<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LocationPoint;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class LocationPointController extends Controller
{
    public function index(): View
    {
        return view('admin.locations.index', [
            'locations' => LocationPoint::with('markedBy')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate($this->rules());
        $validated['marked_by_user_id'] = $request->user()->id;

        LocationPoint::create($validated);

        return back()->with('success', 'Titik lokasi berhasil ditambahkan.');
    }

    public function update(Request $request, LocationPoint $location): RedirectResponse
    {
        $location->update($request->validate($this->rules()));

        return back()->with('success', 'Titik lokasi berhasil diperbarui.');
    }

    public function destroy(LocationPoint $location): RedirectResponse
    {
        $location->delete();

        return back()->with('success', 'Titik lokasi berhasil dihapus.');
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meters' => ['required', 'integer', 'min:1', 'max:100000'],
            'timezone' => ['required', Rule::in(['Asia/Jakarta', 'Asia/Makassar', 'Asia/Jayapura'])],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
        ];
    }
}
