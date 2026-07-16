<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shifts', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('status', 20)->default('aktif')->index();
            $table->timestamps();
        });

        Schema::create('shift_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shift_id')->constrained()->cascadeOnDelete();
            $table->unsignedTinyInteger('day_of_week');
            $table->boolean('is_workday')->default(true);
            $table->time('check_in_time')->nullable();
            $table->time('middle_time')->nullable();
            $table->time('check_out_time')->nullable();
            $table->timestamps();

            $table->unique(['shift_id', 'day_of_week']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('shift_id')->nullable()->after('role')->constrained()->nullOnDelete();
        });

        Schema::table('presensis', function (Blueprint $table) {
            $table->string('shift_name')->nullable()->after('status');
            $table->string('keterangan')->nullable()->after('shift_name');
        });
    }

    public function down(): void
    {
        Schema::table('presensis', function (Blueprint $table) {
            $table->dropColumn(['shift_name', 'keterangan']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shift_id');
        });

        Schema::dropIfExists('shift_schedules');
        Schema::dropIfExists('shifts');
    }
};
