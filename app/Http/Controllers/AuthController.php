<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLogin(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        try {
            $credentials = $request->validate([
                'nim' => ['required', 'string'],
                'password' => ['required', 'string'],
            ], [
                'nim.required' => 'Nomor Induk Mahasiswa (NIM) atau Username wajib diisi.',
                'password.required' => 'Password wajib diisi.',
            ]);

            $user = User::where('nim', $credentials['nim'])->first();

            if (! $user) {
                return back()->withInput($request->only('nim'))
                    ->with('login_error', [
                        'title' => 'Username / NIM Tidak Ditemukan',
                        'message' => 'Username atau NIM tidak ditemukan dalam sistem. Pastikan data yang Anda masukkan sudah benar.',
                    ])
                    ->withErrors([
                        'nim' => 'Username atau NIM tidak ditemukan dalam sistem.',
                    ]);
            }

            if (! Hash::check($credentials['password'], $user->password)) {
                return back()->withInput($request->only('nim'))
                    ->with('login_error', [
                        'title' => 'Password Salah',
                        'message' => 'Kata sandi yang Anda masukkan salah. Silakan periksa kembali dan coba lagi.',
                    ])
                    ->withErrors([
                        'password' => 'Kata sandi yang Anda masukkan salah.',
                    ]);
            }

            if (! $user->is_active) {
                return back()->withInput($request->only('nim'))
                    ->with('login_error', [
                        'title' => 'Akun Dinonaktifkan',
                        'message' => 'Akun Anda sedang dinonaktifkan. Silakan hubungi Super Admin.',
                    ])
                    ->withErrors([
                        'nim' => 'Akun Anda dinonaktifkan. Silakan hubungi Super Admin.',
                    ]);
            }

            Auth::login($user, $request->boolean('remember'));
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('welcome_user', $user->name)
                ->with('success', "Selamat datang kembali, {$user->name}!");
        } catch (ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            report($e);

            return back()->withInput($request->only('nim'))
                ->with('login_error', [
                    'title' => 'Terjadi Kesalahan',
                    'message' => 'Terjadi kendala pada sistem. Silakan coba beberapa saat lagi.',
                ])
                ->withErrors([
                    'nim' => 'Terjadi kesalahan sistem saat memproses login.',
                ]);
        }
    }

    public function logout(Request $request): RedirectResponse
    {
        $userName = Auth::user()?->name ?? 'Rekan KSR';

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')
            ->with('logout_user_name', $userName)
            ->with('info', 'Anda telah berhasil logout.');
    }

    public function profile(): View
    {
        return view('auth.profile', [
            'user' => Auth::user(),
        ]);
    }

    public function updateProfile(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        $rules = [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'current_password' => ['nullable', 'required_with:new_password', 'current_password'],
            'new_password' => ['nullable', 'string', 'min:6', 'confirmed'],
        ];

        // Khusus Super Admin, Username / NIM dapat diubah
        if ($user->isSuperAdmin()) {
            $rules['nim'] = ['required', 'string', 'max:50', Rule::unique('users', 'nim')->ignore($user->id)];
        }

        $validated = $request->validate($rules, [
            'nim.required' => 'Username / NIM wajib diisi.',
            'nim.unique' => 'Username / NIM ini sudah digunakan oleh akun lain.',
            'name.required' => 'Nama lengkap wajib diisi.',
            'current_password.current_password' => 'Password saat ini tidak cocok.',
            'new_password.min' => 'Password baru minimal 6 karakter.',
            'new_password.confirmed' => 'Konfirmasi password baru tidak cocok.',
        ]);

        if ($user->isSuperAdmin() && ! empty($validated['nim'])) {
            $user->nim = $validated['nim'];
        }

        $user->name = $validated['name'];
        $user->email = $validated['email'];

        if (! empty($validated['new_password'])) {
            $user->password = Hash::make($validated['new_password']);
        }

        $user->save();

        return back()->with('success', 'Profil dan kredensial berhasil diperbarui.');
    }
}
