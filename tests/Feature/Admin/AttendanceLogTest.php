<?php

namespace Tests\Feature\Admin;

use App\Models\LocationPoint;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_log_defaults_to_current_date_in_active_location_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-15 23:30:00 UTC'));

        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $todayEmployee = User::factory()->create(['name' => 'Karyawan Hari Ini']);
            $yesterdayEmployee = User::factory()->create(['name' => 'Karyawan Kemarin']);

            LocationPoint::create([
                'name' => 'Kantor Papua',
                'latitude' => -2.5,
                'longitude' => 140.7,
                'radius_meters' => 100,
                'timezone' => 'Asia/Jayapura',
                'marked_by_user_id' => $admin->id,
                'status' => 'aktif',
            ]);

            Presensi::create([
                'user_id' => $todayEmployee->id,
                'tanggal' => '2026-07-16',
                'jam_masuk' => '08:00',
                'jam_pulang' => '17:00',
                'lokasi_masuk_lat' => '-2.5000000',
                'lokasi_masuk_lng' => '140.7000000',
                'lokasi_pulang_lat' => '-2.5100000',
                'lokasi_pulang_lng' => '140.7100000',
                'foto_masuk' => 'presensi/masuk/test.jpg',
                'foto_pulang' => 'presensi/pulang/test.jpg',
                'status' => 'tepat_waktu',
            ]);
            Presensi::create([
                'user_id' => $yesterdayEmployee->id,
                'tanggal' => '2026-07-15',
                'jam_masuk' => '09:00',
                'status' => 'telat',
            ]);

            $this->actingAs($admin)
                ->get(route('admin.attendance.logs.index'))
                ->assertOk()
                ->assertSee('Log Presensi')
                ->assertSee('value="2026-07-16"', escape: false)
                ->assertSee('Zona waktu WIT')
                ->assertSee('Karyawan Hari Ini')
                ->assertSee('data-type="Jam Masuk"', escape: false)
                ->assertSee('data-type="Jam Pulang"', escape: false)
                ->assertSee('data-lat="-2.5"', escape: false)
                ->assertSee('presensi/masuk/test.jpg')
                ->assertDontSee('Karyawan Kemarin');
        } finally {
            Carbon::setTestNow();
        }
    }

    public function test_admin_can_filter_attendance_log_by_date_without_paging(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        for ($number = 1; $number <= 30; $number++) {
            $employee = User::factory()->create(['name' => 'Karyawan '.$number]);
            Presensi::create([
                'user_id' => $employee->id,
                'tanggal' => '2026-07-10',
                'jam_masuk' => '08:00',
                'status' => 'tepat_waktu',
            ]);
        }

        $response = $this->actingAs($admin)
            ->get(route('admin.attendance.logs.index', ['tanggal' => '2026-07-10']))
            ->assertOk()
            ->assertSee('value="2026-07-10"', escape: false);

        foreach (range(1, 30) as $number) {
            $response->assertSee('Karyawan '.$number);
        }
    }

    public function test_employee_can_not_access_attendance_log(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.attendance.logs.index'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
