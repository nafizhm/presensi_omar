<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CompanySetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class CompanyProfileController extends Controller
{
    public function edit(): View
    {
        return view('admin.settings.profile', [
            'profile' => CompanySetting::firstOrCreate([], [
                'company_name' => config('app.name', 'Presensi Mobile'),
                'address' => '-',
                'phone' => '-',
            ]),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'company_name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string', 'max:1000'],
            'phone' => ['required', 'string', 'max:30'],
            'logo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);

        $profile = CompanySetting::firstOrCreate([], [
            'company_name' => $validated['company_name'],
            'address' => $validated['address'],
            'phone' => $validated['phone'],
        ]);

        if ($request->hasFile('logo')) {
            if ($profile->logo) {
                Storage::disk('public')->delete($profile->logo);
            }

            $validated['logo'] = $request->file('logo')->store('perusahaan', 'public');
        }

        $profile->update($validated);

        return back()->with('success', 'Profil perusahaan berhasil disimpan.');
    }
}
