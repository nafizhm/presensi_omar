<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string $role): Response
    {
        if (! $request->user()) {
            return redirect()->route($role === 'admin' ? 'admin.login' : 'login');
        }

        if ($request->user()->role !== $role) {
            return redirect()->route(
                $request->user()->role === 'admin'
                    ? 'admin.dashboard'
                    : 'presensi.beranda'
            );
        }

        if ($role === 'karyawan' && $request->user()->status !== 'aktif') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->withErrors([
                'employee_code' => 'Akun karyawan Anda sedang nonaktif.',
            ]);
        }

        return $next($request);
    }
}
