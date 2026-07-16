<?php

namespace Tests\Feature\Admin;

use App\Models\Shift;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_shift_with_seven_day_schedule(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.attendance.settings.store'), [
            'name' => 'Shift Pagi',
            'status' => 'aktif',
            'schedules' => $this->schedules('08:00', '12:00', '17:00'),
        ])->assertSessionHas('success');

        $shift = Shift::with('schedules')->firstOrFail();
        $this->assertCount(7, $shift->schedules);
        $this->assertSame('12:00', $shift->schedules->first()->middle_time);

        $this->actingAs($admin)->put(route('admin.attendance.settings.update', $shift), [
            'name' => 'Shift Malam',
            'status' => 'aktif',
            'schedules' => $this->schedules('22:00', '01:00', '06:00'),
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('shifts', ['id' => $shift->id, 'name' => 'Shift Malam']);
        $this->assertDatabaseHas('shift_schedules', [
            'shift_id' => $shift->id,
            'day_of_week' => 1,
            'check_in_time' => '22:00',
            'middle_time' => '01:00',
            'check_out_time' => '06:00',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.attendance.settings.index'))
            ->assertOk()
            ->assertSee('Shift Malam')
            ->assertSee('Batas Tengah');

        $this->actingAs($admin)
            ->delete(route('admin.attendance.settings.destroy', $shift))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('shifts', ['id' => $shift->id]);
    }

    public function test_workday_requires_three_different_times(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $schedules = $this->schedules('08:00', '08:00', '17:00');

        $this->actingAs($admin)->post(route('admin.attendance.settings.store'), [
            'name' => 'Shift Invalid',
            'status' => 'aktif',
            'schedules' => $schedules,
        ])->assertSessionHasErrors('schedules.0.middle_time');

        $this->assertDatabaseCount('shifts', 0);
    }

    public function test_employee_can_not_access_shift_settings(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.attendance.settings.index'))
            ->assertRedirect(route('presensi.beranda'));
    }

    private function schedules(string $start, string $middle, string $end): array
    {
        return collect(range(1, 7))->map(fn (int $day) => [
            'day_of_week' => $day,
            'is_workday' => 1,
            'check_in_time' => $start,
            'middle_time' => $middle,
            'check_out_time' => $end,
        ])->all();
    }
}
