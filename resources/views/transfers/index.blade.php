@extends('layouts.app')

@section('title', 'Mutasi Antar Gudang')
@section('header_title', 'Mutasi Logistik Antar Gudang (KSR ↔ Poliklinik)')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false, 
    cancelModalOpen: false,
    selectedTransfer: {},
    availableItems: {{ json_encode($availableItems->values()) }},
    selectedItem: null,
    updateItem(id) {
        this.selectedItem = this.availableItems.find(i => i.id == id);
    },
    openCancel(transfer) {
        this.selectedTransfer = transfer;
        this.cancelModalOpen = true;
    }
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/></svg>
                <span>Mutasi Antar Gudang (KSR ↔ Poliklinik)</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Sistem transfer FEFO dengan state machine: In Transit → Diterima (Otomatis buat batch tujuan)</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="createModalOpen = true" class="px-5 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Buat Mutasi Baru</span>
            </button>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-xs text-xs font-semibold w-fit">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status', 'all') === 'all' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            Semua Status
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'in_transit']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'in_transit' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            In Transit (Dalam Pengiriman)
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'received']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'received' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            Diterima
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'cancelled']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'cancelled' ? 'bg-rose-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            Dibatalkan
        </a>
    </div>

    <!-- Transfers Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($transfers->isEmpty())
        <div class="py-16 text-center text-slate-400 text-xs">
            Belum ada riwayat mutasi antar gudang sesuai filter.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">No. Mutasi & Tanggal</th>
                        <th class="py-3.5 px-4">Rute Gudang</th>
                        <th class="py-3.5 px-4">Barang & Jumlah</th>
                        <th class="py-3.5 px-4">Alokasi FEFO Asal</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($transfers as $t)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-6 font-sans">
                            <div class="font-bold text-slate-900 font-mono text-xs">{{ $t->transfer_no }}</div>
                            <div class="text-[11px] text-slate-400 mt-0.5">{{ $t->sent_at->format('d M Y, H:i') }}</div>
                            <div class="text-[10px] text-slate-500 mt-0.5">Oleh: {{ $t->requester_name }}</div>
                        </td>

                        <td class="py-4 px-4 font-sans">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-0.5 rounded-md bg-slate-100 font-medium text-[11px] text-slate-700">
                                    {{ $t->source_warehouse === 'ksr' ? 'KSR' : 'Poliklinik' }}
                                </span>
                                <svg class="w-4 h-4 text-rose-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                <span class="px-2 py-0.5 rounded-md bg-rose-50 font-bold text-[11px] text-rose-900">
                                    {{ $t->target_warehouse === 'ksr' ? 'KSR' : 'Poliklinik' }}
                                </span>
                            </div>
                        </td>

                        <td class="py-4 px-4 font-sans">
                            <div class="font-bold text-slate-900">{{ $t->item->name }}</div>
                            <div class="font-mono font-black text-rose-800 text-sm mt-0.5">
                                {{ $t->quantity }} <span class="text-xs font-normal text-slate-500 font-sans">{{ $t->item->unit }}</span>
                            </div>
                        </td>

                        <!-- Allocated Batches -->
                        <td class="py-4 px-4 font-sans">
                            @if(!empty($t->allocations))
                            <div class="space-y-1">
                                @foreach($t->allocations as $alloc)
                                <div class="text-[11px] bg-slate-50 px-2 py-0.5 rounded border border-slate-200/80 font-mono text-slate-700">
                                    {{ $alloc['batch_no'] ?? '-' }}: <strong>{{ $alloc['quantity'] }}</strong>
                                    @if(!empty($alloc['expiry_date'])) <span class="text-slate-400 text-[10px]">(Exp: {{ $alloc['expiry_date'] }})</span> @endif
                                </div>
                                @endforeach
                            </div>
                            @endif
                        </td>

                        <!-- Status Badge -->
                        <td class="py-4 px-4 text-center font-sans">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold border {{ $t->status_badge_class }}">
                                {{ $t->status_label }}
                            </span>
                            @if($t->status === 'received')
                            <p class="text-[10px] text-slate-400 mt-1 font-mono">Diterima: {{ $t->received_at?->format('d/m H:i') }} ({{ $t->receiver_name }})</p>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="py-4 px-6 text-right font-sans">
                            @if($t->status === 'in_transit')
                            <div class="flex items-center justify-end gap-2">
                                <!-- Receive Button (Only Destination or Super Admin) -->
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->warehouse === $t->target_warehouse)
                                <form method="POST" action="{{ route('transfers.receive', $t) }}" onsubmit="return confirm('Konfirmasi penerimaan barang mutasi ini di gudang Anda?');">
                                    @csrf
                                    <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition-colors">
                                        Terima Barang
                                    </button>
                                </form>
                                @endif

                                <!-- Cancel Button (Source or Super Admin) -->
                                @if(auth()->user()->isSuperAdmin() || auth()->user()->warehouse === $t->source_warehouse)
                                <button type="button" @click="openCancel({{ json_encode($t) }})" class="px-3 py-1.5 rounded-xl bg-rose-100 hover:bg-rose-200 text-rose-900 font-bold text-xs transition-colors">
                                    Batalkan
                                </button>
                                @endif
                            </div>
                            @else
                            <span class="text-slate-400 text-xs">-</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $transfers->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL: CREATE TRANSFER -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="createModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Buat Mutasi Antar Gudang</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('transfers.store') }}" class="space-y-4">
                @csrf

                <!-- Select Item from Source Warehouse -->
                <div>
                    <label for="transfer_item" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Pilih Barang di Gudang Asal <span class="text-rose-600">*</span>
                    </label>
                    <select id="transfer_item" name="item_id" required @change="updateItem($event.target.value)" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="">-- Pilih Barang yang Memiliki Stok --</option>
                        @foreach($availableItems as $item)
                        <option value="{{ $item->id }}">
                            [{{ strtoupper($item->category) }}] {{ $item->name }} (Asal: {{ $item->warehouse_label }}) - Stok: {{ $item->current_stock }} {{ $item->unit }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Warehouse -->
                <div>
                    <label for="transfer_target" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Gudang Tujuan Mutasi <span class="text-rose-600">*</span>
                    </label>
                    <select id="transfer_target" name="target_warehouse" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="poliklinik">Gudang Poliklinik INSTIKI</option>
                        <option value="ksr">Gudang Markas KSR PMI</option>
                    </select>
                </div>

                <!-- Quantity -->
                <div>
                    <label for="transfer_qty" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Jumlah Mutasi <span class="text-rose-600">*</span>
                    </label>
                    <input type="number" id="transfer_qty" name="quantity" min="1" required placeholder="Contoh: 10" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                </div>

                <!-- Notes -->
                <div>
                    <label for="transfer_notes" class="block text-xs font-bold text-slate-700 uppercase mb-1">Catatan Keperluan Mutasi</label>
                    <textarea id="transfer_notes" name="notes" rows="2" placeholder="Alasan pemindahan stok..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Kirim Mutasi (In Transit)
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL: CANCEL TRANSFER -->
    <div x-show="cancelModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-md w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="cancelModalOpen = false">
            <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Batalkan Mutasi</h3>
                <button type="button" @click="cancelModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <p class="text-xs text-slate-600 mb-4">
                Membatalkan transfer <strong class="text-slate-900" x-text="selectedTransfer.transfer_no"></strong> akan secara otomatis mengembalikan jumlah stok yang sebelumnya terpotong ke batch asal.
            </p>

            <form :action="'/transfers/' + selectedTransfer.id + '/cancel'" method="POST" class="space-y-4">
                @csrf

                <div>
                    <label for="cancel_reason" class="block text-xs font-bold text-slate-700 uppercase mb-1">Alasan Pembatalan</label>
                    <input type="text" id="cancel_reason" name="reason" required placeholder="Salah input / tidak jadi..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="cancelModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Tutup
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-700 hover:bg-rose-800 text-white font-bold text-xs shadow-md">
                        Konfirmasi Batalkan
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
