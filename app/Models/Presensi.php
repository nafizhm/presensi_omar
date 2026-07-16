<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Presensi extends Model
{
    protected $fillable = [
        'user_id',
        'tanggal',
        'jam_masuk',
        'jam_pulang',
        'lokasi_masuk_lat',
        'lokasi_masuk_lng',
        'lokasi_pulang_lat',
        'lokasi_pulang_lng',
        'foto_masuk',
        'foto_pulang',
        'status', // tepat_waktu | telat | izin | absen
        'shift_name',
        'keterangan',
        'keterangan_lokasi',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
