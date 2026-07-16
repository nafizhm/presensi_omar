<?php

namespace App\Services;

use App\Models\ShiftSchedule;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;

class AttendanceScheduleService
{
    public function resolve(User $user, Carbon $now): array
    {
        $previousDate = $now->copy()->subDay()->startOfDay();
        $previousShift = $this->shiftForDate($user, $previousDate);
        $previousSchedule = $previousShift?->schedules->firstWhere('day_of_week', $previousDate->isoWeekday());
        if ($previousSchedule?->is_workday) {
            $previousWindow = $this->fromSchedule($previousDate, $previousShift->name, $previousSchedule);
            if ($previousWindow['overnight']
                && $now->greaterThanOrEqualTo($previousWindow['start'])
                && $now->lessThanOrEqualTo($previousWindow['end'])) {
                return $previousWindow;
            }
        }

        $today = $now->copy()->startOfDay();
        $shift = $this->shiftForDate($user, $today);
        if (! $shift) {
            return $this->buildWindow($today, 'Jadwal Default', '08:00', '12:00', '17:00');
        }

        $schedule = $shift->schedules->firstWhere('day_of_week', $today->isoWeekday());
        if (! $schedule?->is_workday) {
            $window = $this->buildWindow($today, $shift->name, '08:00', '12:00', '17:00');
            $window['is_workday'] = false;
            return $window;
        }

        return $this->fromSchedule($today, $shift->name, $schedule);
    }

    private function shiftForDate(User $user, Carbon $date): ?Shift
    {
        $assignment = EmployeeSchedule::with('shift.schedules')
            ->where('user_id', $user->id)
            ->whereDate('schedule_date', $date->toDateString())
            ->first();

        if ($assignment?->shift?->status === 'aktif') {
            return $assignment->shift;
        }

        $user->loadMissing('shift.schedules');

        return $user->shift?->status === 'aktif' ? $user->shift : null;
    }

    private function fromSchedule(Carbon $date, string $shiftName, ShiftSchedule $schedule): array
    {
        return $this->buildWindow(
            $date,
            $shiftName,
            $schedule->check_in_time,
            $schedule->middle_time,
            $schedule->check_out_time
        );
    }

    private function buildWindow(
        Carbon $date,
        string $shiftName,
        string $checkInTime,
        string $middleTime,
        string $checkOutTime
    ): array {
        $start = Carbon::parse($date->toDateString().' '.$checkInTime, $date->timezone);
        $middle = Carbon::parse($date->toDateString().' '.$middleTime, $date->timezone);
        if ($middle->lessThanOrEqualTo($start)) {
            $middle->addDay();
        }

        $end = Carbon::parse($date->toDateString().' '.$checkOutTime, $date->timezone);
        while ($end->lessThanOrEqualTo($middle)) {
            $end->addDay();
        }

        $now = Carbon::now($date->timezone);

        return [
            'shift_name' => $shiftName,
            'attendance_date' => $date->toDateString(),
            'start' => $start,
            'middle' => $middle,
            'end' => $end,
            'mode' => $now->lessThan($middle) ? 'masuk' : 'pulang',
            'overnight' => ! $end->isSameDay($date),
            'is_workday' => true,
        ];
    }
}
