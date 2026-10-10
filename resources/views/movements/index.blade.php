@extends('layouts.app')

@section('title', 'Pergerakan Stok (FEFO)')
@section('header_title', 'Pergerakan Stok Logistik Medis')

@section('content')
<div class="space-y-6" x-data="{ 
    inboundModalOpen: false, 
    outboundModalOpen: false, 
    opnameModalOpen: false,
    selectedOpnameItem: null,
    availableItems: {{ json_encode($availableItems) }},
    inboundSelectedCategory: 'obat',
    outboundSelectedItem: null,
    outboundQuantity: 1,

    updateInboundCategory(itemId) {
        const found = this.availableItems.find(i => i.id == itemId);
        if (found) {
            this.inboundSelectedCategory = found.category;
        }
    },
    updateOutboundItem(itemId) {
        this.outboundSelectedItem = this.availableItems.find(i => i.id == itemId);
    },
    setupOpname(itemId) {
        this.selectedOpnameItem = this.availableItems.find(i => i.id == itemId);
        this.opnameModalOpen = true;
    }
}">

    <!-- Top Action Banner -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16V4m0 0L3 8m4-4l4 4m6 0v12m0 0l4-4m-4 4l-4-4"/></svg>
                <span>Mutasi & Pergerakan Stok Medis</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Inbound batch terverifikasi, Outbound otomatis FEFO (First Expired First Out), & Stock Opname</p>
        </div>

        <div class="flex items-center gap-2.5">
            <!-- Button Inbound -->
            <button @click="inboundModalOpen = true" class="px-4 py-2.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                <span>Barang Masuk</span>
            </button>

            <!-- Button Outbound FEFO -->
            <button @click="outboundModalOpen = true" class="px-4 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                <span>Barang Keluar (FEFO)</span>
            </button>

            <!-- Button Opname -->
            <button @click="opnameModalOpen = true" class="px-4 py-2.5 rounded-2xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                <span>Stock Opname</span>
            </button>
        </div>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        
        <!-- Tabs Filter -->
        <div class="flex items-center gap-1 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-xs text-xs font-semibold">
            <a href="{{ request()->fullUrlWithQuery(['kind' => 'all']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('kind', 'all') === 'all' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Semua Mutasi
            </a>
            <a href="{{ request()->fullUrlWithQuery(['kind' => 'inbound']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('kind') === 'inbound' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Barang Masuk
            </a>
            <a href="{{ request()->fullUrlWithQuery(['kind' => 'outbound']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('kind') === 'outbound' ? 'bg-rose-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Barang Keluar (FEFO)
            </a>
            <a href="{{ request()->fullUrlWithQuery(['kind' => 'opname']) }}" 
               class="px-4 py-2 rounded-xl transition-colors {{ request('kind') === 'opname' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
                Stock Opname
            </a>
        </div>

        <!-- Search Form -->
        <form method="GET" action="{{ route('movements.index') }}" class="flex items-center gap-2">
            @if(request('warehouse'))
            <input type="hidden" name="warehouse" value="{{ request('warehouse') }}">
            @endif
            @if(request('kind'))
            <input type="hidden" name="kind" value="{{ request('kind') }}">
            @endif
            <div class="relative w-full sm:w-64">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Cari barang / faktur / PIC..." 
                       class="w-full pl-9 pr-4 py-2 bg-white border border-slate-300 rounded-2xl text-xs focus:ring-2 focus:ring-rose-700 outline-none shadow-xs">
                <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            </div>
            <button type="submit" class="px-3.5 py-2 bg-slate-800 hover:bg-slate-900 text-white rounded-2xl text-xs font-bold transition-colors">
                Cari
            </button>
        </form>

    </div>

    <!-- Movement Log Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($movements->isEmpty())
        <div class="py-16 text-center text-slate-400 text-xs">
            Belum ada pergerakan stok yang tercatat sesuai filter.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Waktu & Jenis</th>
                        <th class="py-3.5 px-4">Barang & Gudang</th>
                        <th class="py-3.5 px-4 text-right">Jumlah</th>
                        <th class="py-3.5 px-4 text-right">Saldo Sesudah</th>
                        <th class="py-3.5 px-4">Alokasi FEFO / Detail Batch</th>
                        <th class="py-3.5 px-4">Faktur / Ref</th>
                        <th class="py-3.5 px-6">Penanggung Jawab</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($movements as $m)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3.5 px-6 font-sans">
                            <div class="text-slate-900 font-semibold text-xs">{{ $m->created_at->format('d M Y, H:i') }}</div>
                            <span class="inline-block mt-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $m->kind_badge_class }}">
                                {{ $m->kind_label }}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 font-sans">
                            <div class="font-bold text-slate-900">{{ $m->item->name }}</div>
                            <div class="text-[10px] text-slate-400 font-mono">{{ $m->item->code }} • {{ $m->item->warehouse_label }}</div>
                        </td>

                        <td class="py-3.5 px-4 text-right">
                            <span class="font-bold text-sm {{ $m->kind === 'inbound' ? 'text-emerald-700' : ($m->kind === 'outbound' ? 'text-rose-700' : 'text-amber-700') }}">
                                {{ $m->kind === 'inbound' ? '+' : ($m->kind === 'outbound' ? '-' : ($m->quantity >= 0 ? '+'.$m->quantity : $m->quantity)) }} {{ abs($m->quantity) }}
                            </span>
                            <span class="text-xs font-normal text-slate-500 font-sans">{{ $m->item->unit }}</span>
                        </td>

                        <td class="py-3.5 px-4 text-right font-black text-slate-900">
                            {{ number_format($m->running_balance) }} <span class="text-[10px] font-normal text-slate-400 font-sans">{{ $m->item->unit }}</span>
                        </td>

                        <!-- FEFO Allocations Audit Trail -->
                        <td class="py-3.5 px-4 font-sans">
                            @if(!empty($m->allocations) && is_array($m->allocations))
                            <div class="space-y-1 max-w-xs">
                                @foreach($m->allocations as $alloc)
                                <div class="text-[11px] bg-slate-50 px-2 py-1 rounded-lg border border-slate-200/80 font-mono flex items-center justify-between gap-2">
                                    <span class="font-bold text-slate-800">{{ $alloc['batch_no'] ?? 'Batch #'.$alloc['batch_id'] }}</span>
                                    <span class="text-slate-500">
                                        @if(!empty($alloc['expiry_date'])) (Exp: {{ $alloc['expiry_date'] }}) @endif
                                        <strong>{{ $alloc['quantity'] ?? ($alloc['delta'] ?? 0) }}</strong>
                                    </span>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <span class="text-slate-400 font-mono text-[11px]">-</span>
                            @endif
                        </td>

                        <td class="py-3.5 px-4 font-sans text-slate-700">
                            <div class="font-medium">{{ $m->reference_no ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[140px]">{{ $m->source_destination ?? $m->notes }}</div>
                        </td>

                        <td class="py-3.5 px-6 font-sans text-slate-600">
                            <p class="font-semibold text-slate-800">{{ $m->actor_name }}</p>
                            @if($m->notes)
                            <p class="text-[10px] text-slate-400 italic line-clamp-1">"{{ $m->notes }}"</p>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL 1: INBOUND (BARANG MASUK) -->
    <div x-show="inboundModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="inboundModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-600"></span>
                    <span>Input Barang Masuk (Inbound)</span>
                </h3>
                <button type="button" @click="inboundModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('movements.inbound') }}" class="space-y-4">
                @csrf

                <!-- Select Item -->
                <div>
                    <label for="inbound_item" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Pilih Barang <span class="text-rose-600">*</span>
                    </label>
                    <select id="inbound_item" name="item_id" required @change="updateInboundCategory($event.target.value)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="">-- Pilih Barang --</option>
                        @foreach($availableItems as $item)
                        <option value="{{ $item->id }}">
                            [{{ strtoupper($item->category) }}] {{ $item->name }} ({{ $item->warehouse_label }}) - Stok: {{ $item->current_stock }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Quantity & Date -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="inbound_qty" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Jumlah Masuk <span class="text-rose-600">*</span>
                        </label>
                        <input type="number" id="inbound_qty" name="quantity" min="1" required placeholder="Contoh: 50" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>

                    <div>
                        <label for="inbound_date" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Tanggal Terima <span class="text-rose-600">*</span>
                        </label>
                        <input type="date" id="inbound_date" name="received_at" value="{{ date('Y-m-d') }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <!-- Batch & Expiry (Mandatory for Obat) -->
                <div class="grid grid-cols-2 gap-4 p-3.5 bg-rose-50/50 rounded-2xl border border-rose-100">
                    <div>
                        <label for="inbound_batch" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Nomor Batch <span x-show="inboundSelectedCategory === 'obat'" class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="inbound_batch" name="batch_no" :required="inboundSelectedCategory === 'obat'" placeholder="PCT-2401" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="inbound_expiry" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Tgl Kedaluwarsa <span x-show="inboundSelectedCategory === 'obat'" class="text-rose-600">*</span>
                        </label>
                        <input type="date" id="inbound_expiry" name="expiry_date" :required="inboundSelectedCategory === 'obat'" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-xl text-xs focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>
                    <p class="col-span-2 text-[10px] text-slate-500">
                        <span x-show="inboundSelectedCategory === 'obat'" class="text-rose-700 font-bold">* Kategori Obat wajib mengisi Batch No & Tanggal Kedaluwarsa untuk algoritma FEFO.</span>
                        <span x-show="inboundSelectedCategory === 'bhp'">* BHP Medis: Batch & Expiry opsional.</span>
                        <span x-show="inboundSelectedCategory === 'bhp_non_medis'">* BHP Non Medis: Batch & Expiry tidak diperlukan (habis pakai umum).</span>
                    </p>
                </div>

                <!-- Invoice & Source -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="inbound_invoice" class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Faktur / Bukti</label>
                        <input type="text" id="inbound_invoice" name="invoice_no" placeholder="INV/2026/001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>

                    <div>
                        <label for="inbound_source" class="block text-xs font-bold text-slate-700 uppercase mb-1">Sumber Dana / Pemasok</label>
                        <input type="text" id="inbound_source" name="source" placeholder="Kas KSR / Donasi PMI..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="inbound_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Tambahan</label>
                    <input type="text" id="inbound_notes" name="notes" placeholder="Keterangan kondisi/pengiriman..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="inboundModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-md">
                        Simpan Barang Masuk
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 2: OUTBOUND FEFO (BARANG KELUAR) -->
    <div x-show="outboundModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="outboundModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-rose-700"></span>
                    <span>Pengeluaran Stok — Alokasi Otomatis FEFO</span>
                </h3>
                <button type="button" @click="outboundModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('movements.outbound') }}" class="space-y-4">
                @csrf

                <!-- Select Item -->
                <div>
                    <label for="outbound_item" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Pilih Barang Keluar <span class="text-rose-600">*</span>
                    </label>
                    <select id="outbound_item" name="item_id" required @change="updateOutboundItem($event.target.value)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="">-- Pilih Barang --</option>
                        @foreach($availableItems as $item)
                        @if($item->current_stock > 0)
                        <option value="{{ $item->id }}">
                            {{ $item->name }} ({{ $item->warehouse_label }}) - Tersedia: {{ $item->current_stock }} {{ $item->unit }}
                        </option>
                        @endif
                        @endforeach
                    </select>
                </div>

                <!-- Live FEFO Hint -->
                <div x-show="outboundSelectedItem" class="p-3.5 bg-rose-50/70 rounded-2xl border border-rose-200/80 text-xs">
                    <p class="font-bold text-[#7f1d2d]">Algoritma Alokasi Otomatis FEFO Aktif:</p>
                    <p class="text-slate-600 mt-0.5">Sistem akan otomatis memotong batch dengan tanggal kedaluwarsa paling awal terlebih dahulu.</p>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="outbound_qty" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jumlah Pengeluaran <span class="text-rose-600">*</span>
                    </label>
                    <input type="number" id="outbound_qty" name="quantity" min="1" x-model="outboundQuantity" required placeholder="Contoh: 10" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                </div>

                <!-- Reference & Destination -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="outbound_ref" class="block text-xs font-bold text-slate-700 uppercase mb-1">No. Bukti / Surat Jalan</label>
                        <input type="text" id="outbound_ref" name="reference_no" placeholder="OUT-2026-001" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                    </div>

                    <div>
                        <label for="outbound_dest" class="block text-xs font-bold text-slate-700 uppercase mb-1">Tujuan / Keperluan</label>
                        <input type="text" id="outbound_dest" name="destination" placeholder="Posko Medis Wisuda / Pelayanan..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <!-- Notes -->
                <div>
                    <label for="outbound_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan</label>
                    <textarea id="outbound_notes" name="notes" rows="2" placeholder="Keterangan tambahan pengeluaran..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="outboundModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Keluarkan Stok (FEFO)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL 3: STOCK OPNAME (PENYESUAIAN FISIK) -->
    <div x-show="opnameModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="opnameModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                    <span>Penyesuaian Fisik (Stock Opname)</span>
                </h3>
                <button type="button" @click="opnameModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('movements.opname') }}" class="space-y-4">
                @csrf

                <!-- Select Item for Opname -->
                <div>
                    <label for="opname_item_select" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Pilih Barang Opname <span class="text-rose-600">*</span>
                    </label>
                    <select id="opname_item_select" name="item_id" required @change="setupOpname($event.target.value)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none">
                        <option value="">-- Pilih Barang --</option>
                        @foreach($availableItems as $item)
                        <option value="{{ $item->id }}" :selected="selectedOpnameItem && selectedOpnameItem.id == {{ $item->id }}">
                            {{ $item->name }} ({{ $item->warehouse_label }}) - Sistem: {{ $item->current_stock }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Dynamic Active Batches List to Adjust -->
                <template x-if="selectedOpnameItem">
                    <div class="space-y-3">
                        <label class="block text-xs font-bold text-slate-700 uppercase">
                            Hitung Fisik per Batch Aktif:
                        </label>
                        
                        <div class="max-h-56 overflow-y-auto space-y-2 pr-1">
                            <template x-for="batch in selectedOpnameItem.active_batches" :key="batch.id">
                                <div class="p-3 rounded-xl bg-slate-50 border border-slate-200 flex items-center justify-between gap-3">
                                    <div>
                                        <p class="text-xs font-bold text-slate-900" x-text="batch.batch_no"></p>
                                        <p class="text-[10px] text-slate-500 font-mono">
                                            Sistem: <span class="font-bold text-slate-700" x-text="batch.quantity"></span> <span x-text="selectedOpnameItem.unit"></span>
                                            <span x-show="batch.expiry_date"> • Exp: <span x-text="batch.expiry_date"></span></span>
                                        </p>
                                    </div>
                                    <div class="w-28 text-right">
                                        <label class="text-[10px] text-slate-500 block mb-0.5">Fisik:</label>
                                        <input type="number" 
                                               :name="'batches[' + batch.id + ']'" 
                                               :value="batch.quantity" 
                                               min="0" 
                                               required 
                                               class="w-full px-2.5 py-1.5 bg-white border border-slate-300 rounded-lg text-xs font-mono font-bold text-slate-900 text-right focus:ring-2 focus:ring-amber-500 outline-none">
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Notes -->
                <div>
                    <label for="opname_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Hasil Opname</label>
                    <textarea id="opname_notes" name="notes" rows="2" placeholder="Keterangan selisih fisik..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-amber-500 outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="opnameModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-amber-600 hover:bg-amber-700 text-white font-bold text-xs shadow-md">
                        Simpan Penyesuaian Opname
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
