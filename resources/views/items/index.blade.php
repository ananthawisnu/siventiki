@extends('layouts.app')

@section('title', 'Katalog Obat & BHP')
@section('header_title', 'Master Data Obat & Bahan Habis Pakai (BHP)')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false, 
    editModalOpen: false, 
    selectedItem: {},
    openEdit(item) {
        this.selectedItem = item;
        this.editModalOpen = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                <span>Katalog Obat & BHP Medis</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Stok dihitung real-time secara dinamis dari akumulasi batch aktif</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="createModalOpen = true" class="px-5 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Tambah Barang Baru</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Category Tabs -->
        <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-xs text-xs font-semibold">
            <a href="{{ request()->fullUrlWithQuery(['category' => 'all']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('category', 'all') === 'all' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua Kategori
            </a>
            <a href="{{ request()->fullUrlWithQuery(['category' => 'obat']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('category') === 'obat' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Obat Medis
            </a>
            <a href="{{ request()->fullUrlWithQuery(['category' => 'bhp']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('category') === 'bhp' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                BHP Medis
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('items.index') }}" class="flex items-center gap-2">
            @if(request('warehouse'))
            <input type="hidden" name="warehouse" value="{{ request('warehouse') }}">
            @endif
            @if(request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <div class="relative w-full sm:w-64">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari kode/nama barang..." 
                       class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-2xl text-xs focus:ring-2 focus:ring-rose-700 outline-none shadow-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl text-xs font-bold transition-colors">
                Cari
            </button>
            @if(request('search'))
            <a href="{{ route('items.index') }}" class="p-2 text-slate-400 hover:text-slate-700 rounded-2xl bg-white border border-slate-200">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </a>
            @endif
        </form>

    </div>

    <!-- Items Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($items->isEmpty())
        <div class="py-16 text-center text-slate-400">
            <svg class="w-12 h-12 mx-auto mb-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
            <p class="text-sm font-semibold text-slate-700">Belum Ada Data Barang</p>
            <p class="text-xs text-slate-400 mt-1">Silakan klik "Tambah Barang Baru" untuk mendaftarkan obat atau BHP.</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200/80 uppercase tracking-wider">
                        <th class="py-3.5 px-6">Kode & Nama Barang</th>
                        <th class="py-3.5 px-4">Kategori</th>
                        <th class="py-3.5 px-4">Gudang</th>
                        <th class="py-3.5 px-4 text-center">Batas Min.</th>
                        <th class="py-3.5 px-4 text-right">Stok Aktif</th>
                        <th class="py-3.5 px-4 text-center">Batch Aktif</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($items as $item)
                    <tr class="hover:bg-slate-50/60 transition-colors {{ $item->is_low_stock ? 'bg-rose-50/40' : '' }}">
                        <!-- Name & Code -->
                        <td class="py-4 px-6">
                            <div class="font-bold text-slate-900 text-sm">{{ $item->name }}</div>
                            <div class="text-[11px] font-mono text-slate-400 mt-0.5">{{ $item->code }}</div>
                            @if($item->description)
                            <p class="text-[11px] text-slate-500 line-clamp-1 mt-0.5">{{ $item->description }}</p>
                            @endif
                        </td>

                        <!-- Category Badge -->
                        <td class="py-4 px-4">
                            @if($item->category === 'obat')
                            <span class="px-2.5 py-1 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px] border border-amber-300 inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-600"></span>
                                Obat Medis
                            </span>
                            @else
                            <span class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold text-[10px] border border-emerald-300 inline-flex items-center gap-1">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                BHP Medis
                            </span>
                            @endif
                        </td>

                        <!-- Warehouse -->
                        <td class="py-4 px-4">
                            <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 font-medium text-[11px]">
                                {{ $item->warehouse_label }}
                            </span>
                        </td>

                        <!-- Min Stock (for BHP) -->
                        <td class="py-4 px-4 text-center font-mono">
                            @if($item->category === 'bhp')
                            <span class="text-slate-600 font-semibold">{{ $item->min_stock }}</span> <span class="text-slate-400 text-[10px]">{{ $item->unit }}</span>
                            @else
                            <span class="text-slate-300">-</span>
                            @endif
                        </td>

                        <!-- Current Stock -->
                        <td class="py-4 px-4 text-right">
                            <div class="font-mono font-black text-sm {{ $item->is_low_stock ? 'text-rose-700' : 'text-slate-900' }}">
                                {{ number_format($item->current_stock) }} <span class="text-xs font-normal text-slate-500">{{ $item->unit }}</span>
                            </div>
                            @if($item->is_low_stock)
                            <span class="inline-block mt-0.5 px-2 py-0.2 rounded-full bg-rose-200 text-rose-900 font-bold text-[9px] uppercase tracking-wide">
                                Di Bawah Batas
                            </span>
                            @endif
                        </td>

                        <!-- Active Batches Count -->
                        <td class="py-4 px-4 text-center">
                            <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-mono text-xs font-semibold">
                                {{ $item->batches->where('quantity', '>', 0)->count() }} batch
                            </span>
                        </td>

                        <!-- Actions -->
                        <td class="py-4 px-6 text-right">
                            <div class="flex items-center justify-end gap-1.5">
                                <!-- Stock Card Button -->
                                <a href="{{ route('items.card', $item) }}" 
                                   class="px-2.5 py-1.5 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs flex items-center gap-1 transition-colors" 
                                   title="Kartu Stok & Histori Saldo">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                                    <span>Kartu Stok</span>
                                </a>

                                <!-- Edit -->
                                <button type="button" 
                                        @click="openEdit({{ json_encode($item) }})" 
                                        class="p-1.5 rounded-xl text-slate-600 hover:text-slate-900 hover:bg-slate-100" 
                                        title="Ubah Data Barang">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>

                                <!-- Delete -->
                                <form method="POST" action="{{ route('items.destroy', $item) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus barang ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="p-1.5 rounded-xl text-rose-600 hover:text-rose-900 hover:bg-rose-50 transition-colors" 
                                            title="Hapus Barang">
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

        <!-- Pagination -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $items->links() }}
        </div>
        @endif
    </div>

    <!-- CREATE ITEM MODAL -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="createModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Pendaftaran Barang Baru</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('items.store') }}" class="space-y-4" x-data="{ cat: 'obat' }">
                @csrf

                <!-- Name -->
                <div>
                    <label for="create_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Nama Barang <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" id="create_name" name="name" required placeholder="Contoh: Paracetamol 500mg Tablet" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <!-- Category & Warehouse -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create_category" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Kategori <span class="text-rose-600">*</span>
                        </label>
                        <select id="create_category" name="category" x-model="cat" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                            <option value="obat">Obat Medis</option>
                            <option value="bhp">BHP Medis</option>
                        </select>
                    </div>

                    <div>
                        <label for="create_warehouse" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Gudang <span class="text-rose-600">*</span>
                        </label>
                        <select id="create_warehouse" name="warehouse" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->warehouse === 'ksr')
                            <option value="ksr">Gudang KSR</option>
                            @endif
                            @if(auth()->user()->isSuperAdmin() || auth()->user()->warehouse === 'poliklinik')
                            <option value="poliklinik">Gudang Poliklinik</option>
                            @endif
                        </select>
                    </div>
                </div>

                <!-- Unit & Min Stock -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="create_unit" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Satuan <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="create_unit" name="unit" required placeholder="tablet, botol, box, pcs..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="create_min_stock" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Batas Min. Stok <span x-show="cat === 'bhp'" class="text-rose-600">*</span>
                        </label>
                        <input type="number" id="create_min_stock" name="min_stock" min="0" value="10" :required="cat === 'bhp'" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>
                </div>

                <!-- Description -->
                <div>
                    <label for="create_description" class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi / Indikasi</label>
                    <textarea id="create_description" name="description" rows="2" placeholder="Keterangan obat/BHP..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Simpan Barang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- EDIT ITEM MODAL -->
    <div x-show="editModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="editModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Ubah Data Barang</h3>
                <button type="button" @click="editModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form :action="'/items/' + selectedItem.id" method="POST" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-bold text-slate-700 uppercase mb-1">Kode Barang</label>
                    <input type="text" :value="selectedItem.code" readonly class="w-full px-3.5 py-2 bg-slate-100 border border-slate-200 rounded-xl text-sm font-mono text-slate-500">
                </div>

                <div>
                    <label for="edit_name" class="block text-xs font-bold text-slate-700 uppercase mb-1">Nama Barang</label>
                    <input type="text" id="edit_name" name="name" x-model="selectedItem.name" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="edit_unit" class="block text-xs font-bold text-slate-700 uppercase mb-1">Satuan</label>
                        <input type="text" id="edit_unit" name="unit" x-model="selectedItem.unit" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="edit_min_stock" class="block text-xs font-bold text-slate-700 uppercase mb-1">Batas Min. Stok</label>
                        <input type="number" id="edit_min_stock" name="min_stock" x-model="selectedItem.min_stock" min="0" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>
                </div>

                <div>
                    <label for="edit_description" class="block text-xs font-bold text-slate-700 uppercase mb-1">Deskripsi / Indikasi</label>
                    <textarea id="edit_description" name="description" x-model="selectedItem.description" rows="2" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
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
