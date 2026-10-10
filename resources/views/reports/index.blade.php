@extends('layouts.app')

@section('title', 'Laporan & LPJ')
@section('header_title', 'Laporan Pertanggungjawaban (LPJ) & Rekapitulasi')

@section('content')
<div class="space-y-6">

    <!-- Top Header Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <h2 class="text-xl font-bold text-slate-900 flex items-center gap-2">
                <svg class="w-6 h-6 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <span>Pusat Laporan & Ekspor Dokumen</span>
            </h2>
            <p class="text-xs text-slate-500 mt-1">Pratinjau data laporan sebelum dicetak dalam format PDF landscape A4 atau diekspor ke Excel/CSV</p>
        </div>

        <div class="flex items-center gap-2.5 shrink-0">
            <!-- Print PDF Button -->
            <a href="{{ route('reports.print', request()->all()) }}" 
               target="_blank" 
               class="px-4 py-2.5 rounded-2xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak PDF (Kop Surat)</span>
            </a>

            <!-- Export CSV / Excel Button -->
            <a href="{{ route('reports.export', request()->all()) }}" 
               class="px-4 py-2.5 rounded-2xl bg-emerald-700 hover:bg-emerald-800 text-white font-bold text-xs shadow-md transition-all flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                <span>Ekspor Excel/CSV</span>
            </a>
        </div>
    </div>

    <!-- Filter & Configuration Toolbar -->
    <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs">
        <form method="GET" action="{{ route('reports.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            
            <!-- Report Type -->
            <div>
                <label for="rep_kind" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Jenis Laporan</label>
                <select id="rep_kind" name="kind" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-700 outline-none">
                    <option value="movements" {{ $kind === 'movements' ? 'selected' : '' }}>1. Kartu Stok & Pergerakan (In/Out/Opname)</option>
                    <option value="assets" {{ $kind === 'assets' ? 'selected' : '' }}>2. Inventaris Aset Tetap & Kondisi</option>
                    <option value="opname" {{ $kind === 'opname' ? 'selected' : '' }}>3. Rekapitulasi Stock Opname Fisik</option>
                </select>
            </div>

            <!-- Warehouse (for Super Admin) -->
            <div>
                <label for="rep_warehouse" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Lingkup Gudang</label>
                <select id="rep_warehouse" name="warehouse" {{ !auth()->user()->isSuperAdmin() ? 'disabled' : '' }} class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-medium focus:ring-2 focus:ring-rose-700 outline-none">
                    @if(auth()->user()->isSuperAdmin())
                    <option value="all" {{ $selectedWarehouse === 'all' ? 'selected' : '' }}>Semua Gudang (Gudang Obat + Gudang Inventaris)</option>
                    <option value="ksr" {{ $selectedWarehouse === 'ksr' ? 'selected' : '' }}>Gudang Obat</option>
                    <option value="poliklinik" {{ $selectedWarehouse === 'poliklinik' ? 'selected' : '' }}>Gudang Inventaris</option>
                    @else
                    <option value="{{ auth()->user()->warehouse }}">{{ auth()->user()->warehouse_label }}</option>
                    @endif
                </select>
            </div>

            <!-- Date Range From -->
            <div>
                <label for="rep_date_from" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Dari Tanggal</label>
                <input type="date" id="rep_date_from" name="date_from" value="{{ $dateFrom }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-rose-700 outline-none">
            </div>

            <!-- Date Range To & Submit -->
            <div class="flex items-end gap-2">
                <div class="flex-1">
                    <label for="rep_date_to" class="block text-xs font-bold text-slate-700 uppercase mb-1.5">Sampai Tanggal</label>
                    <input type="date" id="rep_date_to" name="date_to" value="{{ $dateTo }}" class="w-full px-3.5 py-2.5 bg-slate-50 border border-slate-300 rounded-xl text-xs font-mono focus:ring-2 focus:ring-rose-700 outline-none">
                </div>
                <button type="submit" class="px-4 py-2.5 bg-slate-800 hover:bg-slate-900 text-white rounded-xl text-xs font-bold transition-colors">
                    Terapkan
                </button>
            </div>

        </form>
    </div>

    <!-- Summary Box -->
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        @if($kind === 'movements')
        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-200">
            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Total Barang Masuk (Inbound)</span>
            <p class="text-2xl font-black text-emerald-900 font-mono mt-1">+{{ number_format($reportData['summary']['total_inbound']) }} <span class="text-xs font-normal">unit</span></p>
        </div>
        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-200">
            <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Total Barang Keluar (FEFO)</span>
            <p class="text-2xl font-black text-rose-900 font-mono mt-1">-{{ number_format($reportData['summary']['total_outbound']) }} <span class="text-xs font-normal">unit</span></p>
        </div>
        <div class="bg-amber-50 rounded-2xl p-4 border border-amber-200">
            <span class="text-[11px] font-bold text-amber-800 uppercase tracking-wider">Frekuensi Stock Opname</span>
            <p class="text-2xl font-black text-amber-900 font-mono mt-1">{{ number_format($reportData['summary']['total_opname']) }} <span class="text-xs font-normal">kali</span></p>
        </div>
        @elseif($kind === 'assets')
        <div class="bg-blue-50 rounded-2xl p-4 border border-blue-200">
            <span class="text-[11px] font-bold text-blue-800 uppercase tracking-wider">Total Aset Terdata</span>
            <p class="text-2xl font-black text-blue-900 font-mono mt-1">{{ number_format($reportData['summary']['total']) }} <span class="text-xs font-normal">item</span></p>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-200">
            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Aset Tersedia / Siap Pakai</span>
            <p class="text-2xl font-black text-emerald-900 font-mono mt-1">{{ number_format($reportData['summary']['tersedia']) }} <span class="text-xs font-normal">item</span></p>
        </div>
        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-200">
            <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Aset Sedang Digunakan / Rusak</span>
            <p class="text-2xl font-black text-rose-900 font-mono mt-1">{{ number_format($reportData['summary']['digunakan'] + $reportData['summary']['rusak']) }} <span class="text-xs font-normal">item</span></p>
        </div>
        @elseif($kind === 'opname')
        <div class="bg-slate-50 rounded-2xl p-4 border border-slate-200">
            <span class="text-[11px] font-bold text-slate-700 uppercase tracking-wider">Total Penyesuaian Opname</span>
            <p class="text-2xl font-black text-slate-900 font-mono mt-1">{{ number_format($reportData['summary']['total_adjustments']) }} <span class="text-xs font-normal">batch</span></p>
        </div>
        <div class="bg-emerald-50 rounded-2xl p-4 border border-emerald-200">
            <span class="text-[11px] font-bold text-emerald-800 uppercase tracking-wider">Total Selisih Positif (+)</span>
            <p class="text-2xl font-black text-emerald-900 font-mono mt-1">+{{ number_format($reportData['summary']['positive_delta']) }} <span class="text-xs font-normal">unit</span></p>
        </div>
        <div class="bg-rose-50 rounded-2xl p-4 border border-rose-200">
            <span class="text-[11px] font-bold text-rose-800 uppercase tracking-wider">Total Selisih Negatif (-)</span>
            <p class="text-2xl font-black text-rose-900 font-mono mt-1">{{ number_format($reportData['summary']['negative_delta']) }} <span class="text-xs font-normal">unit</span></p>
        </div>
        @endif
    </div>

    <!-- Live Preview Table -->
    <div class="bg-white rounded-3xl border border-slate-200/80 shadow-xs overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
            <h3 class="text-sm font-bold text-slate-900">
                Pratinjau Data: {{ $reportData['title'] }}
            </h3>
            <span class="text-xs text-slate-500 font-mono font-medium">
                {{ $reportData['records']->count() }} Baris Data
            </span>
        </div>

        @if($reportData['records']->isEmpty())
        <div class="py-16 text-center text-slate-400 text-xs">
            Tidak ada data transaksi pada rentang tanggal dan parameter filter yang dipilih.
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs">
                <thead>
                    <tr class="bg-slate-50/80 text-slate-600 font-bold border-b border-slate-200 uppercase tracking-wider text-[10px]">
                        <th class="py-3 px-4">No</th>
                        @if($kind === 'movements')
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Jenis</th>
                        <th class="py-3 px-4">Kode & Nama Barang</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4 text-right">Jumlah</th>
                        <th class="py-3 px-4 text-right">Saldo</th>
                        <th class="py-3 px-4">Ref/Faktur</th>
                        <th class="py-3 px-4">Petugas</th>
                        @elseif($kind === 'assets')
                        <th class="py-3 px-4">Kode Aset</th>
                        <th class="py-3 px-4">Nama Aset</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4">Tahun</th>
                        <th class="py-3 px-4">Sumber Dana</th>
                        <th class="py-3 px-4">Lokasi</th>
                        <th class="py-3 px-4 text-center">Status</th>
                        @elseif($kind === 'opname')
                        <th class="py-3 px-4">Tanggal</th>
                        <th class="py-3 px-4">Barang</th>
                        <th class="py-3 px-4">Gudang</th>
                        <th class="py-3 px-4 text-right">Selisih (Delta)</th>
                        <th class="py-3 px-4 text-right">Saldo Fisik</th>
                        <th class="py-3 px-4">Petugas</th>
                        <th class="py-3 px-4">Catatan</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 font-mono">
                    @foreach($reportData['records'] as $idx => $row)
                    <tr class="hover:bg-slate-50/60 transition-colors">
                        <td class="py-3 px-4 text-slate-400 font-sans">{{ $idx + 1 }}</td>
                        
                        @if($kind === 'movements')
                        <td class="py-3 px-4 font-sans text-slate-700">{{ $row->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 font-sans">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $row->kind_badge_class }}">
                                {{ $row->kind_label }}
                            </span>
                        </td>
                        <td class="py-3 px-4 font-sans font-bold text-slate-900">
                            {{ $row->item->name }}
                            <div class="text-[10px] text-slate-400 font-mono font-normal">{{ $row->item->code }}</div>
                        </td>
                        <td class="py-3 px-4 font-sans text-slate-600">{{ $row->item->warehouse_label }}</td>
                        <td class="py-3 px-4 text-right font-bold {{ $row->kind === 'inbound' ? 'text-emerald-700' : ($row->kind === 'outbound' ? 'text-rose-700' : 'text-amber-700') }}">
                            {{ $row->kind === 'inbound' ? '+' : ($row->kind === 'outbound' ? '-' : '') }}{{ $row->quantity }} {{ $row->item->unit }}
                        </td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">{{ $row->running_balance }} {{ $row->item->unit }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">{{ $row->reference_no ?? '-' }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">{{ $row->actor_name }}</td>
                        
                        @elseif($kind === 'assets')
                        <td class="py-3 px-4 font-bold text-rose-800">{{ $row->code }}</td>
                        <td class="py-3 px-4 font-sans font-bold text-slate-900">{{ $row->name }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">{{ $row->warehouse_label }}</td>
                        <td class="py-3 px-4 text-slate-700">{{ $row->acquisition_year }}</td>
                        <td class="py-3 px-4 font-sans text-slate-700">{{ $row->funding_source }}</td>
                        <td class="py-3 px-4 font-sans text-slate-700">{{ $row->location ?? '-' }}</td>
                        <td class="py-3 px-4 text-center font-sans">
                            <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold border {{ $row->status_badge_class }}">
                                {{ $row->status_label }}
                            </span>
                        </td>

                        @elseif($kind === 'opname')
                        <td class="py-3 px-4 font-sans text-slate-700">{{ $row->created_at->format('d/m/Y H:i') }}</td>
                        <td class="py-3 px-4 font-sans font-bold text-slate-900">{{ $row->item->name }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">{{ $row->item->warehouse_label }}</td>
                        <td class="py-3 px-4 text-right font-bold {{ $row->quantity >= 0 ? 'text-emerald-700' : 'text-rose-700' }}">
                            {{ $row->quantity > 0 ? '+'.$row->quantity : $row->quantity }} {{ $row->item->unit }}
                        </td>
                        <td class="py-3 px-4 text-right font-black text-slate-900">{{ $row->running_balance }} {{ $row->item->unit }}</td>
                        <td class="py-3 px-4 font-sans text-slate-600">{{ $row->actor_name }}</td>
                        <td class="py-3 px-4 font-sans text-slate-500 italic">{{ $row->notes ?? '-' }}</td>
                        @endif

                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif
    </div>

</div>
@endsection
