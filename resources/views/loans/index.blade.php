@extends('layouts.app')

@section('title', 'Peminjaman Lintas Divisi')
@section('header_title', 'Peminjaman Aset Tetap Lintas Divisi & UKM')

@section('content')
<div class="space-y-6" x-data="{ 
    createModalOpen: false,
    availableAssets: {{ json_encode($availableAssets) }}
}">

    <!-- Top Action Bar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-white p-6 rounded-3xl border border-slate-200/80 shadow-xs">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                <span>Peminjaman Aset Lintas Divisi & Unit Kampus</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Pencatatan peminjaman peralatan medis/operasional ke Poliklinik, BEM, Ormawa, dan Panitia Kegiatan</p>
        </div>

        <div class="flex items-center gap-3">
            <button @click="createModalOpen = true" class="px-5 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Pinjamkan Aset</span>
            </button>
        </div>
    </div>

    <!-- Status Tabs -->
    <div class="flex items-center gap-1.5 bg-white p-1.5 rounded-2xl border border-slate-200/80 shadow-xs text-xs font-semibold w-fit">
        <a href="{{ request()->fullUrlWithQuery(['status' => 'all']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status', 'all') === 'all' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            Semua Peminjaman
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'dipinjam']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'dipinjam' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            Sedang Dipinjam
        </a>
        <a href="{{ request()->fullUrlWithQuery(['status' => 'selesai']) }}" 
           class="px-4 py-2 rounded-xl transition-colors {{ request('status') === 'selesai' ? 'bg-emerald-700 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900' }}">
            Selesai / Dikembalikan
        </a>
    </div>

    <!-- Loans Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        @if($loans->isEmpty())
        <div class="py-16 text-center text-slate-400 text-xs">
            Belum ada data peminjaman lintas divisi sesuai filter.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-500 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3.5 px-6">Aset & Kode</th>
                        <th class="py-3.5 px-4">Divisi & PIC Peminjam</th>
                        <th class="py-3.5 px-4">Tgl Pinjam & Est. Kembali</th>
                        <th class="py-3.5 px-4">Keperluan</th>
                        <th class="py-3.5 px-4 text-center">Status</th>
                        <th class="py-3.5 px-6 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($loans as $l)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-4 px-6 font-sans">
                            <div class="font-bold text-slate-900">{{ $l->asset->name }}</div>
                            <div class="text-[11px] font-mono text-rose-800 font-semibold">{{ $l->asset->code }}</div>
                            <span class="inline-block mt-0.5 px-2 py-0.2 rounded bg-slate-100 text-[10px] text-slate-600">
                                Asal: {{ $l->source_warehouse === 'ksr' ? 'Gudang Obat' : 'Gudang Inventaris' }}
                            </span>
                        </td>

                        <td class="py-4 px-4 font-sans">
                            <div class="font-bold text-slate-900">{{ $l->target_division }}</div>
                            <div class="text-[11px] text-slate-500">PIC: {{ $l->borrower_name }}</div>
                        </td>

                        <td class="py-4 px-4 text-slate-700">
                            <div>Pinjam: {{ $l->created_at->format('d/m/Y') }}</div>
                            <div class="text-rose-700 font-bold">Est: {{ $l->return_estimate?->format('d/m/Y') }}</div>
                            @if($l->returned_at)
                            <div class="text-[10px] text-emerald-700">Kembali: {{ $l->returned_at->format('d/m/Y H:i') }}</div>
                            @endif
                        </td>

                        <td class="py-4 px-4 font-sans text-slate-600 max-w-xs">
                            <p class="line-clamp-2">{{ $l->purpose }}</p>
                            <p class="text-[10px] text-slate-400 mt-1 font-mono">Petugas: {{ $l->actor_name }}</p>
                        </td>

                        <td class="py-4 px-4 text-center font-sans">
                            <span class="px-3 py-1 rounded-full text-[11px] font-bold border {{ $l->status_badge_class }}">
                                {{ $l->status_label }}
                            </span>
                        </td>

                        <td class="py-4 px-6 text-right font-sans">
                            @if($l->status === 'dipinjam')
                            <form method="POST" action="{{ route('loans.return', $l) }}" data-swal-confirm="Konfirmasi pengembalian aset '{{ $l->asset->name }}'? Status aset akan kembali menjadi Tersedia." data-swal-title="Pengembalian Aset" data-swal-icon="question" data-swal-ok="Ya, Kembalikan">
                                @csrf
                                <button type="submit" class="px-3 py-1.5 rounded-xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-xs transition-colors">
                                    Kembalikan
                                </button>
                            </form>
                            @else
                            <span class="text-emerald-700 font-bold text-xs flex items-center justify-end gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Selesai</span>
                            </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50">
            {{ $loans->links() }}
        </div>
        @endif
    </div>

    <!-- MODAL: PINJAMKAN ASET -->
    <div x-show="createModalOpen" 
         class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-xs" 
         x-transition 
         style="display: none;">
        <div class="bg-white rounded-3xl max-w-lg w-full p-6 lg:p-8 shadow-2xl border border-slate-100 relative" @click.outside="createModalOpen = false">
            <div class="flex items-center justify-between mb-5 pb-3 border-b border-slate-100">
                <h3 class="text-base font-bold text-slate-900">Form Peminjaman Aset Lintas Divisi</h3>
                <button type="button" @click="createModalOpen = false" class="text-slate-400 hover:text-slate-700">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('loans.store') }}" class="space-y-4">
                @csrf

                <!-- Select Available Asset -->
                <div>
                    <label for="loan_asset" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Pilih Aset Tersedia <span class="text-rose-600">*</span>
                    </label>
                    <select id="loan_asset" name="asset_id" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                        <option value="">-- Pilih Aset yang Siap Dipinjam --</option>
                        @foreach($availableAssets as $asset)
                        <option value="{{ $asset->id }}">
                            {{ $asset->name }} ({{ $asset->code }}) - {{ $asset->warehouse_label }}
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Target Division & PIC -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="loan_division" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Divisi / Unit Peminjam <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="loan_division" name="target_division" required placeholder="BEM / Panitia Wisuda..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>

                    <div>
                        <label for="loan_pic" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                            Nama PIC Peminjam <span class="text-rose-600">*</span>
                        </label>
                        <input type="text" id="loan_pic" name="borrower_name" required placeholder="Nama lengkap PIC..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none">
                    </div>
                </div>

                <!-- Return Estimate -->
                <div>
                    <label for="loan_estimate" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Estimasi Tanggal Pengembalian <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" id="loan_estimate" name="return_estimate" value="{{ date('Y-m-d', strtotime('+3 days')) }}" required class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none font-mono">
                </div>

                <!-- Purpose -->
                <div>
                    <label for="loan_purpose" class="block text-xs font-bold text-slate-700 uppercase mb-1">
                        Keperluan Peminjaman <span class="text-rose-600">*</span>
                    </label>
                    <textarea id="loan_purpose" name="purpose" rows="2" required placeholder="Jelaskan acara / peruntukan peminjaman..." class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-sm focus:ring-2 focus:ring-rose-700 outline-none"></textarea>
                </div>

                <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-2.5">
                    <button type="button" @click="createModalOpen = false" class="px-4 py-2.5 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 text-xs font-semibold">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md">
                        Catat Peminjaman
                    </button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection
