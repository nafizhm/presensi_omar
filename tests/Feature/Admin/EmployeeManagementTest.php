<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\Shift;
use App\Models\Department;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_update_and_delete_employee(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $shift = Shift::create(['name' => 'Shift Pagi', 'status' => 'aktif']);
        $department = Department::create(['name' => 'Operasional', 'description' => 'Tim lapangan']);

        $this->actingAs($admin)->post(route('admin.karyawan.store'), [
            'employee_code' => 'kry100',
            'name' => 'Budi Karyawan',
            'gender' => 'laki-laki',
            'phone' => '08123456789',
            'address' => 'Jakarta',
            'email' => 'budi@example.com',
            'password' => 'password123',
            'shift_id' => $shift->id,
            'department_id' => $department->id,
            'status' => 'aktif',
            'can_manage_location_points' => '1',
        ])->assertSessionHas('success');

        $employee = User::where('employee_code', 'KRY100')->firstOrFail();

        $this->actingAs($admin)->put(route('admin.karyawan.update', $employee), [
            'employee_code' => 'KRY100',
            'name' => 'Budi Diperbarui',
            'gender' => 'laki-laki',
            'phone' => '08987654321',
            'address' => 'Bandung',
            'email' => 'budi@example.com',
            'password' => '',
            'shift_id' => $shift->id,
            'department_id' => $department->id,
            'status' => 'nonaktif',
            'can_manage_location_points' => '1',
        ])->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'name' => 'Budi Diperbarui',
            'status' => 'nonaktif',
            'shift_id' => $shift->id,
            'department_id' => $department->id,
            'can_manage_location_points' => true,
        ]);

        $this->actingAs($admin)
            ->delete(route('admin.karyawan.destroy', $employee))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('users', ['id' => $employee->id]);
    }

    public function test_employee_can_not_access_employee_management(): void
    {
        $employee = User::factory()->create();

        $this->actingAs($employee)
            ->get(route('admin.karyawan.index'))
            ->assertRedirect(route('presensi.beranda'));
    }
}
