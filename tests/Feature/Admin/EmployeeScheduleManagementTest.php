<?php

namespace Tests\Feature\Admin;

use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeScheduleManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_sees_all_employees_and_monthly_calendar(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        User::factory()->create(['name' => 'Andi Jadwal', 'employee_code' => 'KRY001']);
        $employee = User::factory()->create(['name' => 'Budi Jadwal', 'employee_code' => 'KRY002']);

        $this->actingAs($admin)
            ->get(route('admin.attendance.employee-schedules.index'))
            ->assertOk()
            ->assertSee('Andi Jadwal')
            ->assertSee('Budi Jadwal')
            ->assertSee('Update Jadwal');

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.employee-schedules.edit', [
                'employee' => $employee,
                'month' => 2,
                'year' => 2028,
            ]))
            ->assertOk()
            ->assertSee('Februari 2028')
            ->assertSee('2028-02-29');

        $this->assertSame(29, substr_count($response->getContent(), 'name="assignments['));
    }

    public function test_admin_can_set_change_and_clear_shift_for_specific_dates(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        $morning = Shift::create(['name' => 'Shift Pagi', 'status' => 'aktif']);
        $afternoon = Shift::create(['name' => 'Shift Siang', 'status' => 'aktif']);

        $this->actingAs($admin)->put(route('admin.attendance.employee-schedules.update', $employee), [
            'month' => 7,
            'year' => 2026,
            'assignments' => [
                '2026-07-01' => $morning->id,
                '2026-07-02' => $afternoon->id,
            ],
        ])->assertRedirect(route('admin.attendance.employee-schedules.edit', [
            'employee' => $employee,
            'month' => 7,
            'year' => 2026,
        ]));

        $this->assertSame($morning->id, EmployeeSchedule::whereDate('schedule_date', '2026-07-01')->firstOrFail()->shift_id);
        $this->assertSame($afternoon->id, EmployeeSchedule::whereDate('schedule_date', '2026-07-02')->firstOrFail()->shift_id);

        $this->actingAs($admin)->put(route('admin.attendance.employee-schedules.update', $employee), [
            'month' => 7,
            'year' => 2026,
            'assignments' => [
                '2026-07-01' => $afternoon->id,
                '2026-07-02' => null,
            ],
        ])->assertSessionHas('success');

        $this->assertSame($afternoon->id, EmployeeSchedule::whereDate('schedule_date', '2026-07-01')->firstOrFail()->shift_id);
        $this->assertFalse(EmployeeSchedule::where('user_id', $employee->id)->whereDate('schedule_date', '2026-07-02')->exists());
    }

    public function test_employee_can_not_access_monthly_schedule_settings(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.attendance.employee-schedules.index'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
