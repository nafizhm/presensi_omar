<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('presensis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('tanggal');
            $table->time('jam_masuk')->nullable();
            $table->time('jam_pulang')->nullable();
            $table->decimal('lokasi_masuk_lat', 10, 7)->nullable();
            $table->decimal('lokasi_masuk_lng', 10, 7)->nullable();
            $table->decimal('lokasi_pulang_lat', 10, 7)->nullable();
            $table->decimal('lokasi_pulang_lng', 10, 7)->nullable();
            $table->string('foto_masuk')->nullable();
            $table->string('foto_pulang')->nullable();
            $table->enum('status', ['tepat_waktu', 'telat', 'izin', 'absen'])->default('absen');
            $table->string('keterangan_lokasi')->nullable();
            $table->timestamps();

            $table->unique(['user_id', 'tanggal']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('presensis');
    }
};
