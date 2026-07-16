<?php

namespace Tests\Feature\Admin;

use App\Models\LocationPoint;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocationPointManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_location_point(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.locations.store'), [
            'name' => 'Kantor Pusat',
            'latitude' => -6.2000000,
            'longitude' => 106.8166660,
            'radius_meters' => 100,
            'timezone' => 'Asia/Jakarta',
            'status' => 'aktif',
        ])->assertSessionHas('success');

        $location = LocationPoint::firstOrFail();
        $this->assertSame($admin->id, $location->marked_by_user_id);

        $this->actingAs($admin)->put(route('admin.settings.locations.update', $location), [
            'name' => 'Kantor Cabang',
            'latitude' => -6.9147440,
            'longitude' => 107.6098100,
            'radius_meters' => 200,
            'timezone' => 'Asia/Makassar',
            'status' => 'nonaktif',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('location_points', [
            'id' => $location->id,
            'name' => 'Kantor Cabang',
            'radius_meters' => 200,
            'timezone' => 'Asia/Makassar',
            'status' => 'nonaktif',
            'marked_by_user_id' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.settings.locations.destroy', $location))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('location_points', ['id' => $location->id]);
    }

    public function test_location_coordinates_and_radius_are_validated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.locations.store'), [
            'name' => 'Lokasi Tidak Valid',
            'latitude' => 100,
            'longitude' => 200,
            'radius_meters' => 0,
            'timezone' => 'Asia/Jakarta',
            'status' => 'aktif',
        ])->assertSessionHasErrors(['latitude', 'longitude', 'radius_meters']);

        $this->assertDatabaseCount('location_points', 0);
    }

    public function test_only_indonesian_timezones_are_allowed(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.settings.locations.store'), [
            'name' => 'Lokasi Testing',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'radius_meters' => 100,
            'timezone' => 'Europe/London',
            'status' => 'aktif',
        ])->assertSessionHasErrors('timezone');

        $this->assertDatabaseCount('location_points', 0);
    }

    public function test_employee_can_not_access_location_management(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.settings.locations.index'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
