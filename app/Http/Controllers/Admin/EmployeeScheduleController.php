<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\EmployeeSchedule;
use App\Models\LocationPoint;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeScheduleController extends Controller
{
    public function index(): View
    {
        return view('admin.employee-schedules.index', [
            'employees' => User::with('shift')
                ->where('role', 'karyawan')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function edit(Request $request, User $employee): View
    {
        $this->ensureEmployee($employee);
        $validated = $request->validate([
            'month' => ['nullable', 'integer', 'between:1,12'],
            'year' => ['nullable', 'integer', 'between:2000,2100'],
        ]);

        $now = Carbon::now($this->timezone());
        $month = (int) ($validated['month'] ?? $now->month);
        $year = (int) ($validated['year'] ?? $now->year);
        $dates = $this->monthDates($year, $month);
        $start = $dates->first()->toDateString();
        $end = $dates->last()->toDateString();

        $assignments = EmployeeSchedule::where('user_id', $employee->id)
            ->whereBetween('schedule_date', [$start, $end])
            ->get()
            ->mapWithKeys(fn (EmployeeSchedule $schedule) => [
                $schedule->schedule_date->toDateString() => $schedule->shift_id,
            ]);

        return view('admin.employee-schedules.edit', [
            'employee' => $employee->load('shift'),
            'shifts' => Shift::where('status', 'aktif')->orderBy('name')->get(),
            'dates' => $dates,
            'assignments' => $assignments,
            'month' => $month,
            'year' => $year,
        ]);
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);
        $validated = $request->validate([
            'month' => ['required', 'integer', 'between:1,12'],
            'year' => ['required', 'integer', 'between:2000,2100'],
            'assignments' => ['required', 'array'],
            'assignments.*' => [
                'nullable', 'integer',
                Rule::exists('shifts', 'id')->where('status', 'aktif'),
            ],
        ]);

        $dates = $this->monthDates((int) $validated['year'], (int) $validated['month']);

        DB::transaction(function () use ($employee, $validated, $dates) {
            foreach ($dates as $date) {
                $dateString = $date->toDateString();
                $shiftId = $validated['assignments'][$dateString] ?? null;

                if ($shiftId) {
                    $schedule = EmployeeSchedule::where('user_id', $employee->id)
                        ->whereDate('schedule_date', $dateString)
                        ->first() ?? new EmployeeSchedule([
                            'user_id' => $employee->id,
                            'schedule_date' => $dateString,
                        ]);
                    $schedule->shift_id = $shiftId;
                    $schedule->save();
                } else {
                    EmployeeSchedule::where('user_id', $employee->id)
                        ->whereDate('schedule_date', $dateString)
                        ->delete();
                }
            }
        });

        return redirect()->route('admin.attendance.employee-schedules.edit', [
            'employee' => $employee,
            'month' => $validated['month'],
            'year' => $validated['year'],
        ])->with('success', 'Jadwal bulanan '.$employee->name.' berhasil diperbarui.');
    }

    private function monthDates(int $year, int $month)
    {
        $start = Carbon::create($year, $month, 1, 0, 0, 0, $this->timezone());

        return collect(range(1, $start->daysInMonth))
            ->map(fn (int $day) => $start->copy()->day($day));
    }

    private function timezone(): string
    {
        return LocationPoint::where('status', 'aktif')->oldest()->value('timezone')
            ?? 'Asia/Jakarta';
    }

    private function ensureEmployee(User $employee): void
    {
        abort_unless($employee->role === 'karyawan', 404);
    }
}
