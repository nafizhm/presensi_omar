<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_code')->nullable()->unique()->after('id');
            $table->string('gender', 20)->nullable()->after('email');
            $table->string('phone', 30)->nullable()->after('gender');
            $table->text('address')->nullable()->after('phone');
            $table->string('status', 20)->default('aktif')->after('role')->index();
            $table->string('photo')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['employee_code']);
            $table->dropIndex(['status']);
            $table->dropColumn([
                'employee_code', 'gender', 'phone', 'address', 'status', 'photo',
            ]);
        });
    }
};
