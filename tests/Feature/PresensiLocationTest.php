<?php

namespace Tests\Feature;

use App\Models\LocationPoint;
use App\Models\Presensi;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PresensiLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_checkin_page_displays_map_and_active_location(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create();
        LocationPoint::create([
            'name' => 'Kantor Testing',
            'latitude' => -6.2,
            'longitude' => 106.8,
            'radius_meters' => 150,
            'timezone' => 'Asia/Jakarta',
            'marked_by_user_id' => $admin->id,
            'status' => 'aktif',
        ]);

        $this->actingAs($employee)
            ->get(route('presensi.checkin'))
            ->assertOk()
            ->assertSee('googleMapFrame')
            ->assertSee('maps.google.com', escape: false)
            ->assertDontSee('leaflet', escape: false)
            ->assertSee('Kantor Testing')
            ->assertSee('css/presensi.css', escape: false)
            ->assertDontSee('/build/assets', escape: false)
            ->assertDontSee('x-data=', escape: false)
            ->assertDontSee('resources/js/app.js', escape: false)
            ->assertSee('<button id="submitAttendanceButton" type="submit"', escape: false)
            ->assertDontSee('<button id="submitAttendanceButton" type="submit" disabled', escape: false);
    }

    public function test_checkin_from_any_location_is_always_allowed(): void
    {
        Storage::fake('public');
        $employee = User::factory()->create();

        $this->actingAs($employee)->post(route('presensi.store'), [
            'latitude' => 0,
            'longitude' => 0,
            'accuracy' => 10,
            'foto' => UploadedFile::fake()->image('swafoto.jpg', 480, 640),
        ], ['Accept' => 'application/json'])->assertOk()->assertJson(['success' => true]);

        $this->assertDatabaseHas('presensis', [
            'user_id' => $employee->id,
            'lokasi_masuk_lat' => 0,
            'lokasi_masuk_lng' => 0,
        ]);

        $presensi = $employee->presensis()->first();
        Storage::disk('public')->assertExists($presensi->foto_masuk);
    }

    public function test_checkout_from_any_location_is_always_allowed(): void
    {
        $employee = User::factory()->create();
        $presensi = Presensi::create([
            'user_id' => $employee->id,
            'tanggal' => today()->toDateString(),
            'jam_masuk' => '08:00',
            'status' => 'tepat_waktu',
        ]);

        $this->actingAs($employee)->postJson(route('presensi.checkout'), [
            'latitude' => 0,
            'longitude' => 0,
            'accuracy' => 10,
        ])->assertOk()->assertJson(['success' => true]);

        $this->assertNotNull($presensi->fresh()->jam_pulang);
        $this->assertEquals(0, $presensi->fresh()->lokasi_pulang_lat);
        $this->assertEquals(0, $presensi->fresh()->lokasi_pulang_lng);
    }

    public function test_checkin_without_location_or_photo_is_allowed(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->postJson(route('presensi.store'), [])
            ->assertOk()
            ->assertJson(['success' => true]);

        $presensi = $employee->presensis()->firstOrFail();
        $this->assertTrue($presensi->tanggal->isToday());
        $this->assertNotNull($presensi->jam_masuk);
        $this->assertNull($presensi->lokasi_masuk_lat);
        $this->assertNull($presensi->lokasi_masuk_lng);
        $this->assertNull($presensi->foto_masuk);
    }

    public function test_checkout_without_location_or_photo_is_allowed(): void
    {
        $employee = User::factory()->create();
        $presensi = Presensi::create([
            'user_id' => $employee->id,
            'tanggal' => today()->toDateString(),
            'jam_masuk' => '08:00',
            'status' => 'tepat_waktu',
        ]);

        $this->actingAs($employee)
            ->postJson(route('presensi.checkout'), [])
            ->assertOk()
            ->assertJson(['success' => true]);

        $presensi->refresh();
        $this->assertNotNull($presensi->jam_pulang);
        $this->assertNull($presensi->lokasi_pulang_lat);
        $this->assertNull($presensi->lokasi_pulang_lng);
    }

    public function test_attendance_date_and_time_follow_active_location_timezone(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-15 23:30:00 UTC'));

        try {
            $admin = User::factory()->create(['role' => 'admin']);
            $employee = User::factory()->create();
            LocationPoint::create([
                'name' => 'Kantor Papua',
                'latitude' => -2.5,
                'longitude' => 140.7,
                'radius_meters' => 100,
                'timezone' => 'Asia/Jayapura',
                'marked_by_user_id' => $admin->id,
                'status' => 'aktif',
            ]);

            $this->actingAs($employee)
                ->postJson(route('presensi.store'), [])
                ->assertOk();

            $presensi = $employee->presensis()->firstOrFail();
            $this->assertSame('2026-07-16', $presensi->tanggal->toDateString());
            $this->assertSame('08:30', $presensi->jam_masuk);
            $this->assertSame('telat', $presensi->status);

            $this->actingAs($employee)
                ->get(route('presensi.checkin'))
                ->assertOk()
                ->assertSee('Zona waktu WIT')
                ->assertSee('Asia\\/Jayapura', escape: false);
        } finally {
            Carbon::setTestNow();
        }
    }
}
