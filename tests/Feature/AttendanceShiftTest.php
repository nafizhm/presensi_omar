<?php

namespace Tests\Feature;

use App\Models\LocationPoint;
use App\Models\EmployeeSchedule;
use App\Models\Shift;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceShiftTest extends TestCase
{
    use RefreshDatabase;

    public function test_shift_marks_late_checkin_and_early_checkout_using_middle_limit(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createLocation($admin);
        $shift = $this->createShift('Shift Pagi', 1, '08:00', '12:00', '17:00');
        $employee = User::factory()->create(['shift_id' => $shift->id]);

        Carbon::setTestNow(Carbon::parse('2026-07-20 01:30:00 UTC')); // Senin 08:30 WIB
        try {
            $this->actingAs($employee)
                ->get(route('presensi.checkin'))
                ->assertOk()
                ->assertSee('Simpan Absen Masuk')
                ->assertSee('Shift Pagi');

            $this->actingAs($employee)->postJson(route('presensi.store'), [])->assertOk();
            $presensi = $employee->presensis()->firstOrFail();
            $this->assertSame('08:30', $presensi->jam_masuk);
            $this->assertSame('telat', $presensi->status);
            $this->assertSame('Terlambat', $presensi->keterangan);
            $this->assertSame('Shift Pagi', $presensi->shift_name);

            Carbon::setTestNow(Carbon::parse('2026-07-20 08:00:00 UTC')); // Senin 15:00 WIB
            $this->actingAs($employee)
                ->get(route('presensi.checkin'))
                ->assertOk()
                ->assertSee('Simpan Absen Pulang');

            $this->actingAs($employee)->postJson(route('presensi.checkout'), [])->assertOk();
            $presensi->refresh();
            $this->assertSame('15:00', $presensi->jam_pulang);
            $this->assertStringContainsString('Pulang terlalu cepat', $presensi->keterangan);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_overnight_shift_keeps_attendance_on_shift_start_date(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createLocation($admin);
        $shift = $this->createShift('Shift Malam', 1, '22:00', '01:00', '06:00');
        $employee = User::factory()->create(['shift_id' => $shift->id]);

        Carbon::setTestNow(Carbon::parse('2026-07-20 17:30:00 UTC')); // Selasa 00:30 WIB
        try {
            $this->actingAs($employee)
                ->get(route('presensi.checkin'))
                ->assertOk()
                ->assertSee('Simpan Absen Masuk');
            $this->actingAs($employee)->postJson(route('presensi.store'), [])->assertOk();

            $presensi = $employee->presensis()->firstOrFail();
            $this->assertSame('2026-07-20', $presensi->tanggal->toDateString());
            $this->assertSame('00:30', $presensi->jam_masuk);

            Carbon::setTestNow(Carbon::parse('2026-07-20 19:00:00 UTC')); // Selasa 02:00 WIB
            $this->actingAs($employee)
                ->get(route('presensi.checkin'))
                ->assertOk()
                ->assertSee('Simpan Absen Pulang');
            $this->actingAs($employee)->postJson(route('presensi.checkout'), [])->assertOk();

            $presensi->refresh();
            $this->assertSame('02:00', $presensi->jam_pulang);
            $this->assertStringContainsString('Pulang terlalu cepat', $presensi->keterangan);
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_date_specific_shift_overrides_employee_main_shift(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->createLocation($admin);
        $mainShift = $this->createShift('Shift Utama', 1, '08:00', '12:00', '17:00');
        $specialShift = $this->createShift('Shift Khusus', 1, '14:00', '18:00', '22:00');
        $employee = User::factory()->create(['shift_id' => $mainShift->id]);
        EmployeeSchedule::create([
            'user_id' => $employee->id,
            'schedule_date' => '2026-07-20',
            'shift_id' => $specialShift->id,
        ]);

        Carbon::setTestNow(Carbon::parse('2026-07-20 08:00:00 UTC')); // Senin 15:00 WIB
        try {
            $this->actingAs($employee)
                ->get(route('presensi.checkin'))
                ->assertOk()
                ->assertSee('Shift Khusus')
                ->assertSee('Simpan Absen Masuk');

            $this->actingAs($employee)->postJson(route('presensi.store'), [])->assertOk();

            $presensi = $employee->presensis()->firstOrFail();
            $this->assertSame('Shift Khusus', $presensi->shift_name);
            $this->assertSame('15:00', $presensi->jam_masuk);
            $this->assertSame('telat', $presensi->status);
        } finally {
            Carbon::setTestNow();
        }
    }

    private function createLocation(User $admin): void
    {
        LocationPoint::create([
            'name' => 'Kantor WIB',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'radius_meters' => 100,
            'timezone' => 'Asia/Jakarta',
            'marked_by_user_id' => $admin->id,
            'status' => 'aktif',
        ]);
    }

    private function createShift(string $name, int $day, string $start, string $middle, string $end): Shift
    {
        $shift = Shift::create(['name' => $name, 'status' => 'aktif']);
        $shift->schedules()->create([
            'day_of_week' => $day,
            'is_workday' => true,
            'check_in_time' => $start,
            'middle_time' => $middle,
            'check_out_time' => $end,
        ]);

        return $shift;
    }
}
