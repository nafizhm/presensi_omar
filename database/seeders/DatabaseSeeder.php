<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\CompanySetting;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        CompanySetting::firstOrCreate(
            ['id' => 1],
            [
                'company_name' => 'PT Nusantara Karya',
                'address' => 'Jl. Jenderal Sudirman No. 10, Jakarta Pusat',
                'phone' => '021-555-0199',
                'logo' => 'perusahaan/logo-default.svg',
            ],
        );

        User::updateOrCreate(
            ['email' => 'admin@presensi.test'],
            [
                'name' => 'Admin Presensi',
                'username' => 'admin',
                'password' => 'Presensi123!',
                'role' => 'admin',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ],
        );

        User::updateOrCreate(
            ['email' => 'karyawan@presensi.test'],
            [
                'name' => 'Karyawan Presensi',
                'employee_code' => 'KRY001',
                'password' => 'Karyawan123!',
                'role' => 'karyawan',
                'gender' => 'laki-laki',
                'phone' => '081234567890',
                'address' => 'Jakarta',
                'status' => 'aktif',
                'email_verified_at' => now(),
            ],
        );
    }
}
