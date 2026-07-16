<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Presensi;
use App\Models\User;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = today();

        return view('admin.dashboard', [
            'totalKaryawan' => User::where('role', 'karyawan')->count(),
            'hadirHariIni' => Presensi::whereDate('tanggal', $today)
                ->whereNotNull('jam_masuk')
                ->count(),
            'terlambatHariIni' => Presensi::whereDate('tanggal', $today)
                ->where('status', 'telat')
                ->count(),
            'presensiTerbaru' => Presensi::with('user')
                ->whereDate('tanggal', $today)
                ->latest('jam_masuk')
                ->take(10)
                ->get(),
        ]);
    }
}
