<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::where('role', 'admin')->latest()->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->normalizeInput($request);
        $validated = $request->validate($this->rules());
        $validated['role'] = 'admin';
        $validated['email_verified_at'] = now();

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('admin', 'public');
        }

        User::create($validated);

        return back()->with('success', 'Pengguna admin berhasil ditambahkan.');
    }

    public function update(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureAdmin($adminUser);
        $this->normalizeInput($request);
        $validated = $request->validate($this->rules($adminUser));

        if ($adminUser->is($request->user()) && $validated['status'] !== 'aktif') {
            return back()->withErrors([
                'status' => 'Anda tidak dapat menonaktifkan akun yang sedang digunakan.',
            ])->withInput();
        }

        if (blank($validated['password'] ?? null)) {
            unset($validated['password']);
        }

        if ($request->hasFile('photo')) {
            if ($adminUser->photo) {
                Storage::disk('public')->delete($adminUser->photo);
            }

            $validated['photo'] = $request->file('photo')->store('admin', 'public');
        }

        $adminUser->update($validated);

        return back()->with('success', 'Pengguna admin berhasil diperbarui.');
    }

    public function destroy(Request $request, User $adminUser): RedirectResponse
    {
        $this->ensureAdmin($adminUser);

        if ($adminUser->is($request->user())) {
            return back()->withErrors([
                'delete' => 'Anda tidak dapat menghapus akun yang sedang digunakan.',
            ]);
        }

        if ($adminUser->photo) {
            Storage::disk('public')->delete($adminUser->photo);
        }

        $adminUser->delete();

        return back()->with('success', 'Pengguna admin berhasil dihapus.');
    }

    private function rules(?User $adminUser = null): array
    {
        return [
            'username' => [
                'required', 'string', 'min:3', 'max:50', 'regex:/^[a-z0-9._-]+$/',
                Rule::unique('users', 'username')->ignore($adminUser),
            ],
            'name' => ['required', 'string', 'max:255'],
            'email' => [
                'required', 'email', 'max:255',
                Rule::unique('users', 'email')->ignore($adminUser),
            ],
            'password' => [$adminUser ? 'nullable' : 'required', 'string', 'min:8'],
            'status' => ['required', Rule::in(['aktif', 'nonaktif'])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    private function normalizeInput(Request $request): void
    {
        $request->merge([
            'username' => strtolower(trim((string) $request->username)),
            'email' => strtolower(trim((string) $request->email)),
        ]);
    }

    private function ensureAdmin(User $adminUser): void
    {
        abort_unless($adminUser->role === 'admin', 404);
    }
}
