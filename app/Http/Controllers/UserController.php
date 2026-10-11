<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        $users = User::orderBy('role', 'asc')->orderBy('name', 'asc')->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nim' => ['required', 'string', 'max:50', 'unique:users,nim'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'password' => ['required', 'string', 'min:6'],
            'role' => ['required', Rule::in(['super_admin', 'admin_ksr', 'admin_poliklinik'])],
        ], [
            'nim.required' => 'NIM wajib diisi sebagai username.',
            'nim.unique' => 'NIM ini sudah terdaftar di sistem.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        // Auto assign warehouse according to role
        $warehouse = match ($validated['role']) {
            'super_admin' => 'all',
            'admin_ksr' => 'ksr',
            'admin_poliklinik' => 'poliklinik',
            default => 'ksr',
        };

        User::create([
            'nim' => $validated['nim'],
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => $validated['role'],
            'warehouse' => $warehouse,
            'is_active' => true,
        ]);

        return back()->with('success', "Akun pengguna {$validated['name']} (NIM: {$validated['nim']}) berhasil dibuat.");
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'role' => ['required', Rule::in(['super_admin', 'admin_ksr', 'admin_poliklinik'])],
            'password' => ['nullable', 'string', 'min:6'],
        ];

        // Khusus Super Admin, Username / NIM dapat diubah
        if ($user->isSuperAdmin() || $request->input('role') === 'super_admin') {
            $rules['nim'] = ['required', 'string', 'max:50', Rule::unique('users', 'nim')->ignore($user->id)];
        }

        $validated = $request->validate($rules, [
            'nim.required' => 'Username / NIM wajib diisi.',
            'nim.unique' => 'Username / NIM ini sudah digunakan oleh akun lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
        ]);

        $warehouse = match ($validated['role']) {
            'super_admin' => 'all',
            'admin_ksr' => 'ksr',
            'admin_poliklinik' => 'poliklinik',
            default => 'ksr',
        };

        if (isset($validated['nim']) && ($user->isSuperAdmin() || $validated['role'] === 'super_admin')) {
            $user->nim = $validated['nim'];
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];
        $user->role = $validated['role'];
        $user->warehouse = $warehouse;

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', "Data pengguna {$user->name} berhasil diperbarui.");
    }

    public function toggleStatus(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menonaktifkan akun Anda sendiri.']);
        }

        $user->is_active = ! $user->is_active;
        $user->save();

        $statusText = $user->is_active ? 'diaktifkan' : 'dinonaktifkan';

        return back()->with('success', "Akun pengguna {$user->name} berhasil {$statusText}.");
    }

    public function destroy(User $user): RedirectResponse
    {
        if ($user->id === Auth::id()) {
            return back()->withErrors(['error' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        $name = $user->name;
        $user->delete();

        return back()->with('success', "Akun pengguna {$name} berhasil dihapus.");
    }
}
