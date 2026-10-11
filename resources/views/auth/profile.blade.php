@extends('layouts.app')

@section('title', 'Profil Pengguna')
@section('header_title', 'Pengaturan Akun & Profil')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">

    <!-- Header Card -->
    <div class="bg-gradient-to-r from-[#7f1d2d] to-[#991b1b] rounded-3xl p-6 lg:p-8 text-white shadow-xl flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="flex items-center gap-5">
            <div class="w-20 h-20 rounded-2xl bg-white text-[#7f1d2d] font-black text-3xl flex items-center justify-center shadow-lg border-2 border-rose-200">
                {{ substr($user->name, 0, 1) }}
            </div>
            <div>
                <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 text-rose-200 text-xs font-semibold uppercase mb-2">
                    {{ $user->role_label }} • {{ $user->warehouse_label }}
                </div>
                <h2 class="text-2xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-rose-200 font-mono text-sm">NIM: {{ $user->nim }}</p>
            </div>
        </div>
        <div class="text-right bg-black/20 p-4 rounded-2xl border border-rose-500/20">
            <p class="text-xs text-rose-300">Status Akun:</p>
            <p class="text-sm font-bold text-emerald-300 flex items-center gap-1.5 justify-end mt-0.5">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span>
                Aktif & Terverifikasi
            </p>
        </div>
    </div>

    <!-- Edit Form Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs">
        <h3 class="text-lg font-bold text-slate-900 mb-6 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            <span>Ubah Informasi Akun & Password</span>
        </h3>

        <form method="POST" action="{{ route('profile.update') }}" class="space-y-6">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- NIM / Username -->
                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Username / NIM @if($user->isSuperAdmin()) <span class="text-rose-600 font-normal normal-case">(Khusus Super Admin dapat diganti)</span> @endif
                    </label>
                    @if($user->isSuperAdmin())
                    <input type="text" 
                           id="nim"
                           name="nim"
                           value="{{ old('nim', $user->nim) }}" 
                           required 
                           class="w-full px-4 py-3 bg-slate-50 border {{ $errors->has('nim') ? 'border-rose-500 ring-2 ring-rose-500/20' : 'border-slate-300' }} rounded-2xl text-slate-900 text-sm font-mono focus:ring-2 focus:ring-rose-700 outline-none transition-all">
                    <p class="text-[11px] text-slate-500 mt-1">Username akun Super Admin untuk login ke dalam sistem.</p>
                    @else
                    <input type="text" 
                           value="{{ $user->nim }}" 
                           readonly 
                           class="w-full px-4 py-3 bg-slate-100 border border-slate-200 rounded-2xl text-slate-500 text-sm font-mono cursor-not-allowed">
                    <p class="text-[11px] text-slate-400 mt-1">NIM berfungsi sebagai username permanen.</p>
                    @endif
                </div>

                <!-- Name -->
                <div>
                    <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Nama Lengkap <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $user->name) }}" 
                           required 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-700 focus:border-rose-700 outline-none">
                </div>

                <!-- Email -->
                <div class="md:col-span-2">
                    <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-2">
                        Alamat Email (Opsional)
                    </label>
                    <input type="email" 
                           id="email" 
                           name="email" 
                           value="{{ old('email', $user->email) }}" 
                           class="w-full px-4 py-3 bg-slate-50 border border-slate-300 rounded-2xl text-slate-900 text-sm focus:ring-2 focus:ring-rose-700 focus:border-rose-700 outline-none">
                </div>
            </div>

            <!-- Password Section -->
            <div class="pt-6 border-t border-slate-200">
                <h4 class="text-sm font-bold text-slate-800 mb-1">Ganti Password (Kosongkan jika tidak ingin mengubah)</h4>
                <p class="text-xs text-slate-500 mb-4">Pastikan menggunakan kombinasi password yang kuat.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label for="current_password" class="block text-xs font-bold text-slate-700 mb-1.5">Password Saat Ini</label>
                        <input type="password" 
                               id="current_password" 
                               name="current_password" 
                               placeholder="••••••••" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                    <div>
                        <label for="new_password" class="block text-xs font-bold text-slate-700 mb-1.5">Password Baru</label>
                        <input type="password" 
                               id="new_password" 
                               name="new_password" 
                               placeholder="••••••••" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                    <div>
                        <label for="new_password_confirmation" class="block text-xs font-bold text-slate-700 mb-1.5">Konfirmasi Password Baru</label>
                        <input type="password" 
                               id="new_password_confirmation" 
                               name="new_password_confirmation" 
                               placeholder="••••••••" 
                               class="w-full px-4 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>
            </div>

            <div class="flex justify-end pt-4">
                <button type="submit" class="px-6 py-3 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-sm shadow-md transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>

</div>
@endsection
