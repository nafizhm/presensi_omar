<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShiftSchedule extends Model
{
    protected $fillable = [
        'day_of_week', 'is_workday', 'check_in_time', 'middle_time', 'check_out_time',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'is_workday' => 'boolean',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }
}
