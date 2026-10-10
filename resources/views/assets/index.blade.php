@extends('layouts.app')

@section('title', 'Aset Tetap')
@section('header_title', 'Manajemen Aset Tetap & Peralatan Medis')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false, 
    editModalOpen: false, 
    actionModalOpen: false,
    selectedAsset: {},
    actionType: 'borrow', // 'borrow', 'return', 'damage', 'repair'
    openEdit(asset) {
        this.selectedAsset = asset;
        this.editModalOpen = true;
    },
    openAction(asset, action) {
        this.selectedAsset = asset;
        this.actionType = action;
        this.actionModalOpen = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Inventaris Aset Tetap Medis & Operasional</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Kode otomatis format ASET-{KSR|POL}-{NAMA}-{SEQ} dengan audit trail dan histori timeline</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="createModalOpen = true" class="px-5 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Daftarkan Aset Baru</span>
            </button>
        </div>
    </div>

    <!-- Filter Status Tabs & Search -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Status Filter Tabs -->
        <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-xs text-xs font-semibold">
            <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('status', 'all') === 'all' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua Status
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'tersedia']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'tersedia' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Tersedia
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'digunakan']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'digunakan' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Sedang Digunakan
            </a>
            <a href="{{ request()->fullUrlWithQuery(['status' => 'rusak']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'rusak' ? 'bg-rose-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Rusak
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('assets.index') }}" class="flex items-center gap-2">
            @if(request('warehouse'))
            <input type="hidden" name="warehouse" value="{{ request('warehouse') }}">
            @endif
            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <div class="relative w-full sm:w-64">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari kode/nama aset..." 
                       class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-2xl text-xs focus:ring-2 focus:ring-rose-700 outline-none shadow-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl text-xs font-bold transition-colors">
                Cari
            </button>
        </form>

    </div>

    <!-- Assets Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($assets->isEmpty())
        <div class="py-16 text-center text-slate-400 text-xs">
            Belum ada aset tetap yang terdaftar sesuai filter.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Kode & Nama Aset</th>
                        <th class="py-3.5 px-4">Gudang & Lokasi</th>
                        <th class="py-3.5 px-4">Tahun & Sumber Dana</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi Status & Timeline</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($assets as $asset)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <!-- Code & Name -->
                        <td class="py-4 px-6 font-sans">
                            <div class="font-bold text-slate-900 text-sm">{{ $asset->name }}</div>
                            <div class="text-[11px] font-mono text-rose-800 font-semibold mt-0.5">{{ $asset->code }}</div>
                            @if($asset->description)
                            <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $asset->description }}</p>
                            @endif
                        </td>

                        <!-- Warehouse & Location -->
                        <td class="py-4 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium text-[11px]">
                                {{ $asset->warehouse_label }}
                            </span>
                            <p class="text-[11px] text-slate-500 mt-1 flex items-center gap-1">
                                <svg class="w-3.5 h-3.5 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $asset->location ?? 'Gudang Utama' }}</span>
                            </p>
                        </td>

                        <!-- Acquisition Year & Source -->
                        <td class="py-4 px-4 font-sans text-slate-700">
                            <div class="font-bold text-slate-900">{{ $asset->acquisition_year }}</div>
                            <div class="text-[11px] text-slate-500">{{ $asset->funding_source }}</div>
                        </td>

                        <!-- Status Badge -->
                        <td class="py-4 px-4 text-center font-sans">
                            <span class="px-3 py-1 rounded-full text-xs font-bold border {{ $asset->status_badge_class }}">
                                {{ $asset->status_label }}
                            </span>
                        </td>

                        <!-- Actions (4 Status Actions + Timeline) -->
                        <td class="py-4 px-6 text-right font-sans">
                            <div class="flex items-center justify-end gap-1.5">
                                
                                <!-- Timeline Button -->
                                <a href="{{ route('assets.timeline', $asset) }}" 
                                   class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1 transition-colors"
                                   title="Buka Riwayat Timeline Aset">
                                    <svg class="w-3.5 h-3.5 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    <span>Riwayat</span>
                                </a>

                                <!-- Action: Pinjam (if Tersedia) -->
                                @if($asset->status === 'tersedia')
                                <button type="button" 
                                        @click="openAction({{ json_encode($asset) }}, 'borrow')"
                                        class="px-2.5 py-1.5 rounded-xl bg-amber-50 hover:bg-amber-100 text-amber-900 border border-amber-200 font-bold text-xs transition-colors">
                                    Pinjam
                                </button>
                                <button type="button" 
                                        @click="openAction({{ json_encode($asset) }}, 'damage')"
                                        class="px-2.5 py-1.5 rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-900 border border-rose-200 font-bold text-xs transition-colors">
                                    Rusak
                                </button>
                                @endif

                                <!-- Action: Kembalikan (if Digunakan) -->
                                @if($asset->status === 'digunakan')
                                <button type="button" 
                                        @click="openAction({{ json_encode($asset) }}, 'return')"
                                        class="px-2.5 py-1.5 rounded-xl bg-emerald-50 hover:bg-emerald-100 text-emerald-900 border border-emerald-200 font-bold text-xs transition-colors">
                                    Kembalikan
                                </button>
                                @endif

                                <!-- Action: Perbaiki (if Rusak) -->
                                @if($asset->status === 'rusak')
                                <button type="button" 
                                        @click="openAction({{ json_encode($asset) }}, 'repair')"
                                        class="px-2.5 py-1.5 rounded-xl bg-teal-50 hover:bg-teal-100 text-teal-900 border border-teal-200 font-bold text-xs transition-colors">
                                    Perbaiki
                                </button>
                                @endif

                                <!-- Edit -->
                                <button type="button" 
                                        @click="openEdit({{ json_encode($asset) }})" 
                                        class="p-1.5 rounded-xl text-slate-400 hover:text-slate-700 hover:bg-slate-100">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('assets.destroy', $asset) }}" data-swal-confirm="Apakah Anda yakin ingin menghapus aset '{{ $asset->name }}'?" data-swal-title="Hapus Aset" data-swal-icon="warning" data-swal-ok="Ya, Hapus">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 rounded-xl text-rose-500 hover:text-rose-800 hover:bg-rose-50" title="Hapus Aset">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $assets->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL: REGISTER NEW ASSET -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="createModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Registrasi Aset Tetap Baru</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('assets.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label for="asset_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Nama Aset / Peralatan <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="asset_name" name="name" required placeholder="Contoh: Tensimeter Digital Omron" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    <p class="text-[10px] text-slate-400 mt-1">Kode aset akan digenerate otomatis: ASET-{GUDANG}-{SLUG}-{SEQ}</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="asset_warehouse" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Gudang <span class="text-rose-600">*</span>
                        </label>
                        <select id="asset_warehouse" name="warehouse" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->warehouse === 'ksr')
                            <option value="ksr">Gudang Obat</option>
                            @endif
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->warehouse === 'poliklinik')
                            <option value="poliklinik">Gudang Inventaris</option>
                            @endif
                        </select>
                    </div>

                    <div>
                        <label for="asset_year" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Tahun Perolehan <span class="text-rose-600">*</span>
                        </label>
                        <input type="number" id="asset_year" name="acquisition_year" value="{{ date('Y') }}" min="2000" max="{{ date('Y') + 1 }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="asset_funding" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Sumber Dana <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="asset_funding" name="funding_source" value="Kas KSR INSTIKI" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="asset_location" class="block text-xs font-bold text-slate-700 uppercase mb-1">Lokasi Penempatan</label>
                        <input type="text" id="asset_location" name="location" placeholder="Rak A / Meja Dokter..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <div>
                    <label for="asset_description" class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi / Spesifikasi</label>
                    <textarea id="asset_description" name="description" rows="2" placeholder="Spesifikasi alat..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Daftarkan Aset
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: ASSET ACTIONS (PINJAM, KEMBALIKAN, RUSAK, PERBAIKI) -->
    <div x-show="actionModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="actionModalOpen = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900" x-text="
                    actionType === 'borrow' ? 'Form Peminjaman Aset' :
                    actionType === 'return' ? 'Pengembalian Aset' :
                    actionType === 'damage' ? 'Laporan Kerusakan Aset' : 'Konfirmasi Selesai Perbaikan'
                "></h3>
                <button type="button" @click="actionModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-slate-500 mb-4">
                Aset: <strong class="text-slate-800" x-text="selectedAsset.name"></strong> (<span class="font-mono" x-text="selectedAsset.code"></span>)
            </p>

            <form :action="'/assets/' + selectedAsset.id + '/action'" method="POST" class="space-y-4">
                @csrf
                <input type="hidden" name="action" :value="actionType">

                <!-- Case 1: BORROW -->
                <template x-if="actionType === 'borrow'">
                    <div class="space-y-3">
                        <div>
                            <label for="borrow_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Peminjam / PIC <span class="text-rose-600">*</span></label>
                            <input type="text" id="borrow_name" name="borrower_name" required placeholder="Nama PIC..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label for="borrow_estimate" class="block text-xs font-bold text-slate-700 uppercase mb-1">Estimasi Tgl Kembali <span class="text-rose-600">*</span></label>
                            <input type="date" id="borrow_estimate" name="return_estimate" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        </div>
                        <div>
                            <label for="borrow_purpose" class="block text-xs font-bold text-slate-700 uppercase mb-1">Keperluan Peminjaman <span class="text-rose-600">*</span></label>
                            <textarea id="borrow_purpose" name="purpose" rows="2" required placeholder="Kegiatan / unit pengguna..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                        </div>
                    </div>
                </template>

                <!-- Case 2: RETURN -->
                <template x-if="actionType === 'return'">
                    <div>
                        <label for="return_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Kondisi Saat Kembali</label>
                        <input type="text" id="return_notes" name="notes" placeholder="Kondisi baik & lengkap..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-emerald-600 outline-none">
                    </div>
                </template>

                <!-- Case 3: DAMAGE -->
                <template x-if="actionType === 'damage'">
                    <div>
                        <label for="damage_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Keterangan Kerusakan <span class="text-rose-600">*</span></label>
                        <textarea id="damage_notes" name="damage_notes" rows="3" required placeholder="Jelaskan detail bagian alat yang rusak..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
                    </div>
                </template>

                <!-- Case 4: REPAIR -->
                <template x-if="actionType === 'repair'">
                    <div>
                        <label for="repair_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Hasil Perbaikan</label>
                        <input type="text" id="repair_notes" name="repair_notes" placeholder="Sudah diganti sparepart dan siap pakai..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-teal-600 outline-none">
                    </div>
                </template>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="actionModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl text-white font-bold text-xs shadow-md"
                            :class="
                                actionType === 'borrow' ? 'bg-amber-600 hover:bg-amber-700' :
                                actionType === 'return' ? 'bg-emerald-700 hover:bg-emerald-800' :
                                actionType === 'damage' ? 'bg-rose-700 hover:bg-rose-800' : 'bg-teal-700 hover:bg-teal-800'
                            ">
                        Simpan Aksi
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
