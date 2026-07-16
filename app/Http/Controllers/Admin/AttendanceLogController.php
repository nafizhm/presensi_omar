<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationPoint;
use App\Models\Presensi;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AttendanceLogController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'tanggal' => ['nullable', 'date_format:Y-m-d'],
        ]);

        $timezone = LocationPoint::where('status', 'aktif')->oldest()->value('timezone')
            ?? 'Asia/Jakarta';
        $selectedDate = $validated['tanggal'] ?? Carbon::now($timezone)->toDateString();

        return view('admin.attendance-logs.index', [
            'attendanceLogs' => Presensi::with('user')
                ->whereDate('tanggal', $selectedDate)
                ->orderBy('jam_masuk')
                ->get(),
            'selectedDate' => $selectedDate,
            'timezoneLabel' => match ($timezone) {
                'Asia/Makassar' => 'WITA',
                'Asia/Jayapura' => 'WIT',
                default => 'WIB',
            },
        ]);
    }
}
