<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ShiftController extends Controller
{
    public function index(): View
    {
        $shifts = Shift::with('schedules')->withCount('employees')->latest()->get();
        $shiftData = $shifts->mapWithKeys(function (Shift $shift) {
            return [$shift->id => [
                'name' => $shift->name,
                'status' => $shift->status,
                'schedules' => $shift->schedules->mapWithKeys(function ($schedule) {
                    return [$schedule->day_of_week => [
                        'is_workday' => $schedule->is_workday,
                        'check_in_time' => substr($schedule->check_in_time ?? '', 0, 5),
                        'middle_time' => substr($schedule->middle_time ?? '', 0, 5),
                        'check_out_time' => substr($schedule->check_out_time ?? '', 0, 5),
                    ]];
                }),
            ]];
        });

        return view('admin.shifts.index', [
            'shifts' => $shifts,
            'shiftData' => $shiftData,
            'days' => $this->days(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validated($request);

        DB::transaction(function () use ($validated) {
            $shift = Shift::create([
                'name' => $validated['name'],
                'status' => $validated['status'],
            ]);
            $this->saveSchedules($shift, $validated['schedules']);
        });

        return back()->with('success', 'Shift dan jadwal berhasil ditambahkan.');
    }

    public function update(Request $request, Shift $shift): RedirectResponse
    {
        $validated = $this->validated($request, $shift);

        DB::transaction(function () use ($validated, $shift) {
            $shift->update([
                'name' => $validated['name'],
                'status' => $validated['status'],
            ]);
            $this->saveSchedules($shift, $validated['schedules']);
        });

        return back()->with('success', 'Shift dan jadwal berhasil diperbarui.');
    }

    public function destroy(Shift $shift): RedirectResponse
    {
        $shift->delete();

        return back()->with('success', 'Shift berhasil dihapus. Karyawan terkait menjadi tanpa shift.');
    }

    private function validated(Request $request, ?Shift $shift = null): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', Rule::unique('shifts')->ignore($shift)],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'schedules' => ['required', 'array', 'size:7'],
            'schedules.*.day_of_week' => ['required', 'integer', 'between:1,7', 'distinct'],
            'schedules.*.is_workday' => ['nullable', 'boolean'],
            'schedules.*.check_in_time' => ['nullable', 'date_format:H:i'],
            'schedules.*.middle_time' => ['nullable', 'date_format:H:i'],
            'schedules.*.check_out_time' => ['nullable', 'date_format:H:i'],
        ]);

        $errors = [];
        foreach ($validated['schedules'] as $index => &$schedule) {
            $schedule['is_workday'] = (bool) ($schedule['is_workday'] ?? false);
            if (! $schedule['is_workday']) {
                $schedule['check_in_time'] = null;
                $schedule['middle_time'] = null;
                $schedule['check_out_time'] = null;
                continue;
            }

            foreach (['check_in_time', 'middle_time', 'check_out_time'] as $field) {
                if (blank($schedule[$field] ?? null)) {
                    $errors["schedules.$index.$field"][] = 'Jam wajib diisi untuk hari kerja.';
                }
            }

            if (filled($schedule['check_in_time'] ?? null)
                && filled($schedule['middle_time'] ?? null)
                && filled($schedule['check_out_time'] ?? null)
                && count(array_unique([
                    $schedule['check_in_time'], $schedule['middle_time'], $schedule['check_out_time'],
                ])) < 3) {
                $errors["schedules.$index.middle_time"][] = 'Jam masuk, batas tengah, dan jam pulang tidak boleh sama.';
            }
        }
        unset($schedule);

        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        return $validated;
    }

    private function saveSchedules(Shift $shift, array $schedules): void
    {
        foreach ($schedules as $schedule) {
            $shift->schedules()->updateOrCreate(
                ['day_of_week' => $schedule['day_of_week']],
                [
                    'is_workday' => $schedule['is_workday'],
                    'check_in_time' => $schedule['check_in_time'],
                    'middle_time' => $schedule['middle_time'],
                    'check_out_time' => $schedule['check_out_time'],
                ]
            );
        }
    }

    private function days(): array
    {
        return [1 => 'Senin', 2 => 'Selasa', 3 => 'Rabu', 4 => 'Kamis', 5 => 'Jumat', 6 => 'Sabtu', 7 => 'Minggu'];
    }
}
