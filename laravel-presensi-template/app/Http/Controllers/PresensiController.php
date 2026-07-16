<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PresensiController extends Controller
{
    // Sesuaikan koordinat & radius kantor kamu di sini, atau pindahkan ke config/tabel "kantor" bila multi-cabang.
    protected float $officeLat = -6.200000;
    protected float $officeLng = 106.816666;
    protected int $radiusMeter = 100;
    protected string $namaKantor = 'Kantor Pusat';

    public function beranda()
    {
        $user = Auth::user();
        $today = Carbon::today();

        $presensiHariIni = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', $today)
            ->first();

        $bulanIni = Presensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $today->month)
            ->whereYear('tanggal', $today->year);

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
        ]);
    }

    public function checkinForm()
    {
        $user = Auth::user();
        $sudahMasuk = Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->whereNotNull('jam_masuk')
            ->exists();

        return view('presensi.checkin', [
            'officeLat' => $this->officeLat,
            'officeLng' => $this->officeLng,
            'radiusMeter' => $this->radiusMeter,
            'namaKantor' => $this->namaKantor,
            'mode' => $sudahMasuk ? 'pulang' : 'masuk',
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'foto' => 'nullable|image|max:4096',
        ]);

        $distance = $this->haversine(
            $request->latitude,
            $request->longitude,
            $this->officeLat,
            $this->officeLng
        );

        if ($distance > $this->radiusMeter) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu berada di luar radius kantor (' . round($distance) . ' m). Presensi ditolak.',
            ], 422);
        }

        $user = Auth::user();
        $now = Carbon::now();
        $jamMasukBatas = Carbon::today()->setTime(8, 0);
        $status = $now->gt($jamMasukBatas) ? 'telat' : 'tepat_waktu';

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('presensi/masuk', 'public');
        }

        Presensi::updateOrCreate(
            ['user_id' => $user->id, 'tanggal' => Carbon::today()->toDateString()],
            [
                'jam_masuk' => $now->format('H:i'),
                'lokasi_masuk_lat' => $request->latitude,
                'lokasi_masuk_lng' => $request->longitude,
                'foto_masuk' => $fotoPath,
                'status' => $status,
                'keterangan_lokasi' => $this->namaKantor . ' · ' . round($distance) . ' m',
            ]
        );

        return response()->json(['success' => true]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
        ]);

        $distance = $this->haversine(
            $request->latitude,
            $request->longitude,
            $this->officeLat,
            $this->officeLng
        );

        if ($distance > $this->radiusMeter) {
            return response()->json([
                'success' => false,
                'message' => 'Kamu berada di luar radius kantor (' . round($distance) . ' m). Presensi pulang ditolak.',
            ], 422);
        }

        $user = Auth::user();
        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('presensi/pulang', 'public');
        }

        Presensi::where('user_id', $user->id)
            ->whereDate('tanggal', Carbon::today())
            ->update([
                'jam_pulang' => Carbon::now()->format('H:i'),
                'lokasi_pulang_lat' => $request->latitude,
                'lokasi_pulang_lng' => $request->longitude,
                'foto_pulang' => $fotoPath,
            ]);

        return response()->json(['success' => true]);
    }

    public function riwayat()
    {
        $riwayat = Presensi::where('user_id', Auth::id())
            ->orderByDesc('tanggal')
            ->take(30)
            ->get();

        return view('presensi.riwayat', ['riwayat' => $riwayat]);
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
        $jam = now()->hour;
        return match(true) {
            $jam < 11 => 'Selamat pagi',
            $jam < 15 => 'Selamat siang',
            $jam < 18 => 'Selamat sore',
            default => 'Selamat malam',
        };
    }
}
