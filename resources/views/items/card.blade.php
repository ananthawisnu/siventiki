@extends('layouts.app')

@section('title', 'Kartu Stok - ' . $item->name)
@section('header_title', 'Kartu Stok & Saldo Berjalan')

@section('content')
<div class="space-y-6">

    <!-- Header / Item Summary Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('items.index') }}" class="text-xs text-rose-800 font-bold hover:underline inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Katalog</span>
                </a>
                <span class="text-slate-300">•</span>
                <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $item->category === 'obat' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300' }}">
                    {{ $item->category_label }}
                </span>
                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-medium">
                    {{ $item->warehouse_label }}
                </span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">{{ $item->name }}</h2>
            <p class="text-xs font-mono text-slate-500 mt-1">KODE: {{ $item->code }} @if($item->description) • {{ $item->description }} @endif</p>
        </div>

        <div class="flex items-center gap-4 bg-slate-50 p-4 rounded-2xl border border-slate-100 shrink-0">
            <div class="text-right">
                <p class="text-xs text-slate-500 font-medium">Saldo Stok Saat Ini</p>
                <p class="text-3xl font-black {{ $item->is_low_stock ? 'text-rose-700' : 'text-slate-900' }} font-mono">
                    {{ number_format($item->current_stock) }} <span class="text-sm font-normal text-slate-500">{{ $item->unit }}</span>
                </p>
            </div>
            <button onclick="window.print()" class="px-4 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 text-white font-bold text-xs flex items-center gap-2 shadow-xs transition-colors no-print">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak Kartu Stok</span>
            </button>
        </div>
    </div>

    <!-- Active Batches Overview -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <span class="w-2.5 h-2.5 rounded-full bg-rose-700"></span>
            <span>Daftar Batch Aktif (FEFO Order)</span>
        </h3>

        @if($batches->isEmpty())
        <p class="text-xs text-slate-400 py-4 text-center">Belum ada batch yang tercatat untuk barang ini.</p>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-500 font-bold border-b border-slate-200">
                        <th class="py-2.5 px-4">No. Batch</th>
                        <th class="py-2.5 px-4">Tgl Terima</th>
                        <th class="py-2.5 px-4">Tgl Kedaluwarsa</th>
                        <th class="py-2.5 px-4">Status FEFO</th>
                        <th class="py-2.5 px-4">No. Faktur / Sumber</th>
                        <th class="py-2.5 px-4 text-right">Sisa Stok</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($batches as $b)
                    <tr class="hover:bg-slate-50/50">
                        <td class="py-3 px-4 font-bold text-slate-900">{{ $b->batch_no }}</td>
                        <td class="py-3 px-4 text-slate-600">{{ $b->received_at->format('d/m/Y') }}</td>
                        <td class="py-3 px-4 {{ $b->days_until_expiry < 0 ? 'text-rose-700 font-bold' : ($b->days_until_expiry <= 60 ? 'text-amber-700 font-bold' : 'text-slate-600') }}">
                            {{ $b->expiry_date ? $b->expiry_date->format('d/m/Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold {{ $b->expiry_status === 'expired' ? 'bg-rose-100 text-rose-800' : ($b->expiry_status === 'critical_30' ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800') }}">
                                {{ $b->expiry_status_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-slate-600 font-sans">
                            {{ $b->invoice_no ?? '-' }} ({{ $b->source ?? '-' }})
                        </td>
                        <td class="py-3 px-4 text-right font-bold {{ $b->quantity > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                            {{ $b->quantity }} {{ $item->unit }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

    <!-- Movement History with Running Balance Table -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Buku Histori Mutasi & Saldo Berjalan</span>
        </h3>

        @if($movements->isEmpty())
        <div class="py-12 text-center text-slate-400 text-xs">
            Belum ada catatan mutasi pergerakan untuk barang ini.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[11px]">
                        <th class="py-3 px-4">Tanggal & Waktu</th>
                        <th class="py-3 px-3">Jenis</th>
                        <th class="py-3 px-3 text-right">Masuk</th>
                        <th class="py-3 px-3 text-right">Keluar</th>
                        <th class="py-3 px-3 text-right">Opname</th>
                        <th class="py-3 px-4 text-right font-black bg-rose-50/50">Saldo Berjalan</th>
                        <th class="py-3 px-4">Ref / Tujuan</th>
                        <th class="py-3 px-4">Aktor</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($movements as $m)
                    <tr class="hover:bg-slate-50/70 transition-colors">
                        <td class="py-3.5 px-4 text-slate-700">
                            {{ $m->created_at->format('d/m/Y H:i') }}
                        </td>
                        <td class="py-3.5 px-3 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $m->kind_badge_class }}">
                                {{ $m->kind_label }}
                            </span>
                        </td>
                        
                        <!-- Masuk -->
                        <td class="py-3.5 px-3 text-right font-bold text-emerald-700">
                            {{ $m->kind === 'inbound' ? '+'.$m->quantity : '-' }}
                        </td>

                        <!-- Keluar -->
                        <td class="py-3.5 px-3 text-right font-bold text-rose-700">
                            {{ $m->kind === 'outbound' ? '-'.$m->quantity : '-' }}
                        </td>

                        <!-- Opname Delta -->
                        <td class="py-3.5 px-3 text-right font-bold text-amber-700">
                            {{ $m->kind === 'opname' ? ($m->quantity >= 0 ? '+'.$m->quantity : $m->quantity) : '-' }}
                        </td>

                        <!-- Saldo Berjalan -->
                        <td class="py-3.5 px-4 text-right font-black text-sm text-slate-900 bg-rose-50/30">
                            {{ number_format($m->running_balance) }} <span class="text-[10px] font-normal text-slate-400">{{ $item->unit }}</span>
                        </td>

                        <!-- Ref & Notes -->
                        <td class="py-3.5 px-4 font-sans text-slate-700">
                            <div class="font-medium text-slate-800">{{ $m->reference_no ?? '-' }}</div>
                            <div class="text-[10px] text-slate-400 truncate max-w-[180px]">{{ $m->source_destination ?? $m->notes }}</div>
                        </td>

                        <!-- Actor -->
                        <td class="py-3.5 px-4 font-sans text-slate-500">
                            {{ $m->actor_name }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-4 pt-4 border-t border-slate-100">
            {{ $movements->links() }}
        </div>
        @endif
    </div>

</div>
@endsection
