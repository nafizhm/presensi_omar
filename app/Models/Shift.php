<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Shift extends Model
{
    protected $fillable = ['name', 'status'];

    public function schedules(): HasMany
    {
        return $this->hasMany(ShiftSchedule::class)->orderBy('day_of_week');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
