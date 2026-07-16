<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Shift;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(): View
    {
        return view('admin.karyawan.index', [
            'employees' => User::with('shift')->where('role', 'karyawan')->latest()->get(),
            'shifts' => Shift::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'employee_code' => strtoupper(trim((string) $request->employee_code)),
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $validated = $request->validate($this->rules());
        $validated['role'] = 'karyawan';
        $validated['email_verified_at'] = now();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('karyawan', 'public');
        }

        User::create($validated);

        return back()->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function update(Request $request, User $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        $request->merge([
            'employee_code' => strtoupper(trim((string) $request->employee_code)),
            'email' => strtolower(trim((string) $request->email)),
        ]);

        $validated = $request->validate($this->rules($employee));

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }

            $validated['photo'] = $request->file('photo')->store('karyawan', 'public');
        }

        $employee->update($validated);

        return back()->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(User $employee): RedirectResponse
    {
        $this->ensureEmployee($employee);

        if ($employee->photo) {
            Storage::disk('public')->delete($employee->photo);
        }

        $employee->delete();

        return back()->with('success', 'Karyawan berhasil dihapus.');
    }

    private function rules(?User $employee = null): array
    {
        return [
            'employee_code' => [
                'required', 'string', 'max:50',
                Rule::unique('users', 'employee_code')->ignore($employee),
            ],
            'name' => ['required', 'string', 'max:255'],
            'gender' => ['required', Rule::in(['laki-laki', 'perempuan'])],
            'phone' => ['required', 'string', 'max:30'],
            'address' => ['required', 'string', 'max:1000'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($employee),
            ],
            'password' => [$employee ? 'nullable' : 'required', 'string', 'min:8'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function ensureEmployee(User $employee): void
    {
        abort_unless($employee->role === 'karyawan', 404);
    }
}
