@extends('layouts.app')

@section('title', 'Manajemen Pengguna')
@section('header_title', 'Manajemen Pengguna & Hak Akses (RBAC)')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false, 
    editModalOpen: false, 
    selectedUser: {},
    openEdit(user) {
        this.selectedUser = user;
        this.editModalOpen = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                <span>Manajemen Akun & Role Pengguna</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Username berbasis NIM mahasiswa dengan 3 Role: Super Admin, Admin KSR, & Admin Poliklinik</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="createModalOpen = true" class="px-5 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Pengguna Baru</span>
            </button>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Nama & Email</th>
                        <th class="py-3.5 px-4">NIM (Username)</th>
                        <th class="py-3.5 px-4">Role Akses</th>
                        <th class="py-3.5 px-4">Lingkup Gudang</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($users as $u)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-6 font-sans">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#7f1d2d] text-white font-bold flex items-center justify-center text-xs">
                                    {{ substr($u->name, 0, 1) }}
                                </div>
                                <div>
                                    <p class="font-bold text-slate-900">{{ $u->name }}</p>
                                    <p class="text-[11px] text-slate-400 font-mono">{{ $u->email ?? '-' }}</p>
                                </div>
                            </div>
                        </td>

                        <td class="py-4 px-4 text-slate-900 font-bold text-sm">
                            {{ $u->nim }}
                        </td>

                        <td class="py-4 px-4 font-sans">
                            <span class="px-2.5 py-1 rounded-full text-xs font-bold border {{ $u->isSuperAdmin() ? 'bg-rose-100 text-rose-900 border-rose-300' : ($u->isAdminKsr() ? 'bg-amber-100 text-amber-900 border-amber-300' : 'bg-blue-100 text-blue-900 border-blue-300') }}">
                                {{ $u->role_label }}
                            </span>
                        </td>

                        <td class="py-4 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium text-[11px]">
                                {{ $u->warehouse_label }}
                            </span>
                        </td>

                        <td class="py-4 px-4 text-center font-sans">
                            <span class="px-2.5 py-0.5 rounded-full text-[11px] font-bold {{ $u->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-200 text-slate-600' }}">
                                {{ $u->is_active ? 'Aktif' : 'Non-Aktif' }}
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right font-sans">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Edit -->
                                <button type="button" 
                                        @click="openEdit({{ json_encode($u) }})" 
                                        class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100"
                                        title="Ubah Pengguna">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                @if($u->id !== auth()->id())
                                <!-- Toggle Active -->
                                <form method="POST" action="{{ route('users.toggle', $u) }}">
                                    @csrf
                                    <button type="submit" 
                                            class="px-2 py-1 rounded-lg text-xs font-semibold {{ $u->is_active ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50' }}"
                                            title="Toggle Status">
                                        {{ $u->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                                    </button>
                                </form>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('users.destroy', $u) }}" onsubmit="return confirm('Hapus akun pengguna ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-xl text-rose-600 hover:text-rose-900 hover:bg-rose-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <!-- MODAL: ADD USER -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="createModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Tambah Akun Pengguna Baru</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('users.store') }}" class="space-y-4">
                @csrf

                <!-- NIM (Username) -->
                <div>
                    <label for="create_user_nim" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Nomor Induk Mahasiswa (NIM) <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="create_user_nim" name="nim" required placeholder="Contoh: 2401020005" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm font-mono focus:ring-2 focus:ring-rose-700 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">NIM digunakan sebagai username login.</p>
                </div>

                <!-- Name -->
                <div>
                    <label for="create_user_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Nama Lengkap <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="create_user_name" name="name" required placeholder="Nama anggota / petugas..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <!-- Role -->
                <div>
                    <label for="create_user_role" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Role Akses <span class="text-rose-600">*</span>
                    </label>
                    <select id="create_user_role" name="role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="admin_ksr">Admin KSR (Hanya data Gudang KSR)</option>
                        <option value="admin_poliklinik">Admin Poliklinik (Hanya data Poliklinik)</option>
                        <option value="super_admin">Super Admin (Akses Penuh Semua Gudang)</option>
                    </select>
                </div>

                <!-- Email & Password -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create_user_email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                        <input type="email" id="create_user_email" name="email" placeholder="user@instiki.ac.id" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="create_user_pass" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Password <span class="text-rose-600">*</span>
                        </label>
                        <input type="password" id="create_user_pass" name="password" required placeholder="Min. 6 karakter" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Buat Akun
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: EDIT USER -->
    <div x-show="editModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="editModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Ubah Data Pengguna</h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/users/' + selectedUser.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">NIM (Username)</label>
                    <input type="text" :value="selectedUser.nim" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-500">
                </div>

                <div>
                    <label for="edit_user_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Lengkap</label>
                    <input type="text" id="edit_user_name" name="name" x-model="selectedUser.name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div>
                    <label for="edit_user_role" class="block text-xs font-bold text-slate-700 uppercase mb-1">Role Akses</label>
                    <select id="edit_user_role" name="role" x-model="selectedUser.role" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="admin_ksr">Admin KSR</option>
                        <option value="admin_poliklinik">Admin Poliklinik</option>
                        <option value="super_admin">Super Admin</option>
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_user_email" class="block text-xs font-bold text-slate-700 uppercase mb-1">Email</label>
                        <input type="email" id="edit_user_email" name="email" x-model="selectedUser.email" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="edit_user_pass" class="block text-xs font-bold text-slate-700 uppercase mb-1">Reset Password (Opsional)</label>
                        <input type="password" id="edit_user_pass" name="password" placeholder="Kosongkan jika tetap" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="editModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
