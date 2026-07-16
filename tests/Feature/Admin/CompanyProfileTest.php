<?php

namespace Tests\Feature\Admin;

use App\Models\CompanySetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class CompanyProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_company_profile_and_logo(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);
        CompanySetting::create([
            'company_name' => 'Perusahaan Lama',
            'address' => 'Alamat Lama',
            'phone' => '021000',
        ]);

        $this->actingAs($admin)->put(route('admin.settings.profile.update'), [
            'company_name' => 'PT Presensi Indonesia',
            'address' => 'Jl. Merdeka No. 1',
            'phone' => '021-123456',
            'logo' => UploadedFile::fake()->image('logo.png', 200, 200),
        ])->assertSessionHas('success');

        $profile = CompanySetting::firstOrFail();
        $this->assertSame('PT Presensi Indonesia', $profile->company_name);
        Storage::disk('public')->assertExists($profile->logo);
    }

    public function test_company_name_and_logo_are_displayed_on_employee_login(): void
    {
        CompanySetting::create([
            'company_name' => 'PT Login Dinamis',
            'address' => 'Jakarta',
            'phone' => '021000',
            'logo' => 'perusahaan/logo.png',
        ]);

        $this->get(route('login'))
            ->assertOk()
            ->assertSee('PT Login Dinamis')
            ->assertSee('storage/perusahaan/logo.png', escape: false)
            ->assertDontSee('Masuk sebagai Admin')
            ->assertDontSee('akses lainnya');
    }

    public function test_employee_can_not_access_company_profile_settings(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.settings.profile.edit'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
