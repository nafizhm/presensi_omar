<?php

namespace Tests\Feature;

use App\Models\LocationPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;
use Tests\TestCase;

class EmployeeProfileManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_employee_can_only_update_allowed_profile_fields(): void
    {
        $employee = User::factory()->create([
            'role' => 'karyawan',
            'name' => 'Nama Tetap',
            'phone' => '0811',
        ]);

        $this->actingAs($employee)->patch(route('presensi.profil.update'), [
            'phone' => '08223344',
            'email' => 'profil-baru@example.com',
            'password' => 'password-baru',
            'password_confirmation' => 'password-baru',
            'name' => 'Nama Tidak Boleh Berubah',
        ])->assertSessionHas('success');

        $employee->refresh();
        $this->assertSame('Nama Tetap', $employee->name);
        $this->assertSame('08223344', $employee->phone);
        $this->assertSame('profil-baru@example.com', $employee->email);
        $this->assertTrue(Hash::check('password-baru', $employee->password));
    }

    public function test_location_menu_and_creation_require_employee_permission(): void
    {
        $employee = User::factory()->create(['role' => 'karyawan', 'can_manage_location_points' => false]);

        $this->actingAs($employee)->get(route('presensi.profil'))
            ->assertOk()->assertDontSee('Pengaturan Titik Lokasi');

        $payload = [
            'name' => 'Cabang Baru', 'latitude' => -6.2, 'longitude' => 106.8,
            'radius_meters' => 100, 'timezone' => 'Asia/Jakarta',
        ];
        $this->actingAs($employee)->post(route('presensi.profil.locations.store'), $payload)->assertForbidden();

        $employee->update(['can_manage_location_points' => true]);
        $this->actingAs($employee)->get(route('presensi.profil'))
            ->assertOk()->assertSee('Pengaturan Titik Lokasi');
        $this->actingAs($employee)->post(route('presensi.profil.locations.store'), $payload)
            ->assertSessionHas('success');

        $location = LocationPoint::firstOrFail();
        $this->assertSame($employee->id, $location->marked_by_user_id);
        $this->assertSame('aktif', $location->status);
    }

    public function test_employee_can_update_profile_photo_from_camera_upload(): void
    {
        Storage::fake('public');
        $employee = User::factory()->create(['role' => 'karyawan']);

        $this->actingAs($employee)->post(route('presensi.profil.photo.update'), [
            'photo' => UploadedFile::fake()->image('kamera.jpg'),
        ])->assertSessionHas('success');

        $employee->refresh();
        $this->assertNotNull($employee->photo);
        Storage::disk('public')->assertExists($employee->photo);

        $this->actingAs($employee)->get(route('presensi.profil'))
            ->assertOk()
            ->assertSee('Kode Karyawan:')
            ->assertSee('capture="user"', escape: false)
            ->assertSee($employee->photo);
    }

    public function test_employee_pages_use_indonesian_day_names_and_history_filters(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 08:00:00', 'Asia/Jakarta'));
        try {
            $employee = User::factory()->create(['role' => 'karyawan']);

            $this->actingAs($employee)->get(route('presensi.beranda'))
                ->assertOk()->assertSee('Senin, 20 Juli 2026');

            $this->actingAs($employee)->get(route('presensi.riwayat'))
                ->assertOk()
                ->assertSee('data-history-filter="tepat_waktu"', escape: false)
                ->assertSee('data-history-filter="telat"', escape: false)
                ->assertSee('data-history-filter="izin"', escape: false);
        } finally {
            Carbon::setTestNow();
        }
    }
}
