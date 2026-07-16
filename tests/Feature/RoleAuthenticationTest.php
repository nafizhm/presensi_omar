<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_only_from_admin_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'password' => 'secret-password',
        ]);

        $this->post(route('login'), [
            'employee_code' => $admin->employee_code,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('employee_code');

        $this->post(route('admin.login.store'), [
            'username' => strtoupper($admin->username),
            'password' => 'secret-password',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticatedAs($admin);
    }

    public function test_employee_can_login_only_from_employee_login(): void
    {
        $employee = User::factory()->create([
            'role' => 'karyawan',
            'password' => 'secret-password',
        ]);

        $this->post(route('admin.login.store'), [
            'username' => $employee->username,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('username');

        $this->post(route('login'), [
            'employee_code' => $employee->employee_code,
            'password' => 'secret-password',
        ])->assertRedirect(route('presensi.beranda'));

        $this->assertAuthenticatedAs($employee);
    }

    public function test_inactive_employee_can_not_login(): void
    {
        $employee = User::factory()->create([
            'status' => 'nonaktif',
            'password' => 'secret-password',
        ]);

        $this->post(route('login'), [
            'employee_code' => $employee->employee_code,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('employee_code');

        $this->assertGuest();
    }

    public function test_inactive_admin_can_not_login(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'status' => 'nonaktif',
            'password' => 'secret-password',
        ]);

        $this->post(route('admin.login.store'), [
            'username' => $admin->username,
            'password' => 'secret-password',
        ])->assertSessionHasErrors('username');

        $this->assertGuest();
    }

    public function test_each_role_is_redirected_away_from_the_other_area(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $employee = User::factory()->create(['role' => 'karyawan']);

        $this->actingAs($admin)
            ->get(route('presensi.beranda'))
            ->assertRedirect(route('admin.dashboard'));

        $this->actingAs($employee)
            ->get(route('admin.dashboard'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
