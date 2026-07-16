<?php

namespace Tests\Feature\Admin;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_manage_departments_and_see_employee_count(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.master-data.departments.store'), [
            'name' => 'Teknologi Informasi',
            'description' => 'Pengembangan dan dukungan sistem',
        ])->assertSessionHas('success');

        $department = Department::firstOrFail();
        User::factory()->create(['role' => 'karyawan', 'department_id' => $department->id]);

        $this->actingAs($admin)->get(route('admin.master-data.departments.index'))
            ->assertOk()
            ->assertSee('Teknologi Informasi')
            ->assertSee('1 orang');

        $this->actingAs($admin)->put(route('admin.master-data.departments.update', $department), [
            'name' => 'IT',
            'description' => 'Teknologi',
        ])->assertSessionHas('success');

        $this->actingAs($admin)->delete(route('admin.master-data.departments.destroy', $department))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('departments', ['id' => $department->id]);
        $this->assertDatabaseHas('users', ['department_id' => null]);
    }
}
