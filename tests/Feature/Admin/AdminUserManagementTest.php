<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_another_admin(): void
    {
        $currentAdmin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($currentAdmin)->post(route('admin.settings.users.store'), [
            'username' => 'Admin.Baru',
            'name' => 'Admin Baru',
            'email' => 'adminbaru@example.com',
            'password' => 'password123',
            'status' => 'aktif',
        ])->assertSessionHas('success');

        $adminUser = User::where('username', 'admin.baru')->firstOrFail();

        $this->actingAs($currentAdmin)->put(route('admin.settings.users.update', $adminUser), [
            'username' => 'admin.baru',
            'name' => 'Admin Diperbarui',
            'email' => 'adminbaru@example.com',
            'password' => '',
            'status' => 'nonaktif',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $adminUser->id,
            'name' => 'Admin Diperbarui',
            'role' => 'admin',
            'status' => 'nonaktif',
        ]);

        $this->actingAs($currentAdmin)
            ->delete(route('admin.settings.users.destroy', $adminUser))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $adminUser->id]);
    }

    public function test_admin_can_not_disable_or_delete_own_account(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->put(route('admin.settings.users.update', $admin), [
            'username' => $admin->username,
            'name' => $admin->name,
            'email' => $admin->email,
            'password' => '',
            'status' => 'nonaktif',
        ])->assertSessionHasErrors('status');

        $this->assertSame('aktif', $admin->fresh()->status);

        $this->actingAs($admin)
            ->delete(route('admin.settings.users.destroy', $admin))
            ->assertSessionHasErrors('delete');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_employee_can_not_access_admin_user_management(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.settings.users.index'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
