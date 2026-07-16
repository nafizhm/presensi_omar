<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\LocationPoint;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use App\Services\AttendanceScheduleService;

class PresensiController extends Controller
{
    public function __construct(private readonly AttendanceScheduleService $scheduleService)
    {
    }

    // Sesuaikan koordinat & radius kantor kamu di sini, atau pindahkan ke config/tabel "kantor" bila multi-cabang.
    protected float $officeLat = -6.200000;
    protected float $officeLng = 106.816666;
    protected int $radiusMeter = 100;
    protected string $namaKantor = 'Kantor Pusat';

    public function beranda()
    {
        $user = Auth::user();
        $office = $this->officeLocation();
        $localNow = Carbon::now($office['timezone']);
        $schedule = $this->scheduleService->resolve($user, $localNow);
        $attendanceDate = $schedule['attendance_date'];

        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $attendanceDate)
            ->first();

        $bulanIni = Presensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $localNow->month)
            ->whereYear('tanggal', $localNow->year);

        $stats = [
            'hadir' => (clone $bulanIni)->whereIn('status', ['tepat_waktu', 'telat'])->count(),
            'telat' => (clone $bulanIni)->where('status', 'telat')->count(),
            'izin' => (clone $bulanIni)->where('status', 'izin')->count(),
        ];

        $riwayatTerakhir = Presensi::where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->take(2)
            ->get();

        return view('presensi.beranda', [
            'user' => $user,
            'inisial' => $this->inisial($user->name ?? ''),
            'sapaan' => $this->sapaanWaktu(),
            'presensiHariIni' => $presensiHariIni,
            'stats' => $stats,
            'riwayatTerakhir' => $riwayatTerakhir,
            'localNow' => $localNow,
        ]);
    }

    public function checkinForm()
    {
        $user = Auth::user();
        $office = $this->officeLocation();
        $now = Carbon::now($office['timezone']);
        $schedule = $this->scheduleService->resolve($user, $now);
        $today = Carbon::parse($schedule['attendance_date'], $office['timezone']);
        $sudahMasuk = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today->toDateString())
            ->whereNotNull('jam_masuk')
            ->exists();

        return view('presensi.checkin', [
            'officeLat' => $office['latitude'],
            'officeLng' => $office['longitude'],
            'radiusMeter' => $office['radius'],
            'namaKantor' => $office['name'],
            'googleMapsApiKey' => config('services.google_maps.embed_key'),
            'timezone' => $office['timezone'],
            'timezoneLabel' => $office['timezone_label'],
            'mode' => $schedule['mode'],
            'schedule' => $schedule,
            'sudahMasuk' => $sudahMasuk,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'foto' => 'nullable|image|max:4096',
        ]);

        $office = $this->officeLocation();
        $hasCoordinates = $request->input('latitude') !== null && $request->input('longitude') !== null;
        $distance = $hasCoordinates
            ? $this->haversine(
                $request->input('latitude'),
                $request->input('longitude'),
                $office['latitude'],
                $office['longitude']
            )
            : null;

        $user = Auth::user();
        $now = Carbon::now($office['timezone']);
        $schedule = $this->scheduleService->resolve($user, $now);
        $status = $now->gt($schedule['start']) ? 'telat' : 'tepat_waktu';
        $attendanceNote = $status === 'telat' ? 'Terlambat' : 'Tepat waktu';
        if (! $schedule['is_workday']) {
            $attendanceNote = 'Masuk di luar jadwal kerja';
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('presensi/masuk', 'public');
        }

        Presensi::updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => $schedule['attendance_date']],
            [
                'jam_masuk' => $now->format('H:i'),
                'lokasi_masuk_lat' => $request->input('latitude'),
                'lokasi_masuk_lng' => $request->input('longitude'),
                'foto_masuk' => $fotoPath,
                'status' => $status,
                'shift_name' => $schedule['shift_name'],
                'keterangan' => $attendanceNote,
                'keterangan_lokasi' => $hasCoordinates
                    ? $office['name'] . ' - ' . round($distance) . ' m'
                    : null,
            ]
        );

        return response()->json(['success' => true]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric',
            'accuracy' => 'nullable|numeric',
            'foto' => 'nullable|image|max:4096',
        ]);

        $user = Auth::user();
        $office = $this->officeLocation();
        $now = Carbon::now($office['timezone']);
        $schedule = $this->scheduleService->resolve($user, $now);
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('presensi/pulang', 'public');
        }

        $presensi = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $schedule['attendance_date'])
            ->first() ?? new Presensi([
                'user_id' => $user->id,
                'tanggal' => $schedule['attendance_date'],
            ]);
        $checkoutNote = $now->lt($schedule['end'])
            ? 'Pulang terlalu cepat'
            : 'Pulang sesuai jadwal';
        if (! $schedule['is_workday']) {
            $checkoutNote = 'Pulang di luar jadwal kerja';
        }
        $existingNote = $presensi->keterangan;

        $presensi->fill([
                'jam_pulang' => $now->format('H:i'),
                'lokasi_pulang_lat' => $request->input('latitude'),
                'lokasi_pulang_lng' => $request->input('longitude'),
                'foto_pulang' => $fotoPath,
                'status' => $presensi->exists ? $presensi->status : 'absen',
                'shift_name' => $presensi->shift_name ?: $schedule['shift_name'],
                'keterangan' => $existingNote ? $existingNote.'; '.$checkoutNote : $checkoutNote,
            ])->save();

        return response()->json(['success' => true]);
    }

    public function riwayat()
    {
        $office = $this->officeLocation();
        $localNow = Carbon::now($office['timezone']);
        $riwayat = Presensi::where('user_id', Auth::id())
            ->orderByDesc('tanggal')
            ->take(30)
            ->get();

        return view('presensi.riwayat', ['riwayat' => $riwayat, 'localNow' => $localNow]);
    }

    public function profil()
    {
        $user = Auth::user();
        return view('presensi.profil', [
            'user' => $user,
            'inisial' => $this->inisial($user->name ?? ''),
            'radiusMeter' => $this->radiusMeter,
        ]);
    }

    /** Jarak antara dua koordinat GPS dalam meter (formula Haversine) */
    protected function haversine($lat1, $lon1, $lat2, $lon2): float
    {
        $R = 6371000; // radius bumi dalam meter
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    protected function inisial(string $nama): string
    {
        $parts = explode(' ', trim($nama));
        $inisial = strtoupper(substr($parts[0] ?? 'U', 0, 1) . substr($parts[1] ?? '', 0, 1));
        return $inisial ?: 'U';
    }

    protected function sapaanWaktu(): string
    {
        $jam = Carbon::now($this->officeLocation()['timezone'])->hour;
        return match(true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }

    private function officeLocation(): array
    {
        $location = LocationPoint::where('status', 'aktif')->oldest()->first();

        return [
            'name' => $location?->name ?? $this->namaKantor,
            'latitude' => (float) ($location?->latitude ?? $this->officeLat),
            'longitude' => (float) ($location?->longitude ?? $this->officeLng),
            'radius' => (int) ($location?->radius_meters ?? $this->radiusMeter),
            'timezone' => $location?->timezone ?? 'Asia/Jakarta',
            'timezone_label' => match ($location?->timezone ?? 'Asia/Jakarta') {
                'Asia/Makassar' => 'WITA',
                'Asia/Jayapura' => 'WIT',
                default => 'WIB',
            },
        ];
    }
}
