@extends('layouts.app')

@section('title', 'Dashboard')
@section('header_title', 'Dashboard Inventaris & Logistik Medis')

@section('content')
<div class="space-y-8" x-data="{ 
    expiryTab: 'expired',
    refreshing: false,
    refreshDashboard() {
        this.refreshing = true;
        window.location.reload();
    }
}">

    <!-- Welcome Banner & Refresh Toolbar -->
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 bg-gradient-to-r from-[#7f1d2d] via-[#991b1b] to-[#be123c] rounded-3xl p-6 text-white shadow-lg">
        <div>
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/15 text-rose-100 text-xs font-semibold uppercase mb-2">
                {{ auth()->user()->role_label }} • {{ auth()->user()->warehouse_label }}
            </span>
            <h2 class="text-2xl font-black tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h2>
            <p class="text-xs text-rose-100/90 mt-1">Monitoring stok FEFO, logistik medis, dan status aset tetap secara real-time.</p>
        </div>
        <div class="flex items-center gap-2.5">
            <button @click="refreshDashboard()" 
                    :disabled="refreshing"
                    class="px-4 py-2.5 rounded-2xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-medium text-xs flex items-center gap-2 transition-all cursor-pointer backdrop-blur-xs">
                <svg class="w-4 h-4" :class="refreshing ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                <span x-text="refreshing ? 'Menyegarkan...' : 'Segarkan Data'">Segarkan Data</span>
            </button>
            <a href="{{ route('movements.index') }}" class="px-4 py-2.5 rounded-2xl bg-white text-[#7f1d2d] hover:bg-rose-50 font-bold text-xs shadow-md transition-all flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                <span>Input Mutasi</span>
            </a>
        </div>
    </div>

    <!-- 4 METRIC CARDS -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        
        <!-- Card 1: Total Stok Units -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Unit Stok</span>
                <div class="w-10 h-10 rounded-2xl bg-rose-50 text-[#7f1d2d] flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black text-slate-900 font-mono">{{ number_format($totalStockUnits) }}</h3>
                <span class="text-xs text-slate-500 font-medium">unit</span>
            </div>
            <p class="text-[11px] text-slate-400 mt-2 flex items-center gap-1">
                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                Stok aktif (Obat & BHP)
            </p>
        </div>

        <!-- Card 2: Aset Tetap -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Total Aset Tetap</span>
                <div class="w-10 h-10 rounded-2xl bg-blue-50 text-blue-700 flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black text-slate-900 font-mono">{{ number_format($totalAssets) }}</h3>
                <span class="text-xs text-slate-500 font-medium">item</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">
                {{ $assetStats['tersedia'] }} Siap • {{ $assetStats['digunakan'] }} Pakai • {{ $assetStats['rusak'] }} Rusak
            </p>
        </div>

        <!-- Card 3: Stok Kritis (BHP <= min_stock) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all group {{ $criticalStockCount > 0 ? 'ring-2 ring-rose-500/20' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Stok Kritis (BHP)</span>
                <div class="w-10 h-10 rounded-2xl {{ $criticalStockCount > 0 ? 'bg-rose-100 text-rose-700 animate-pulse' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black {{ $criticalStockCount > 0 ? 'text-rose-700' : 'text-slate-900' }} font-mono">
                    {{ number_format($criticalStockCount) }}
                </h3>
                <span class="text-xs text-slate-500 font-medium">item</span>
            </div>
            <p class="text-[11px] {{ $criticalStockCount > 0 ? 'text-rose-600 font-semibold' : 'text-slate-400' }} mt-2">
                {{ $criticalStockCount > 0 ? 'Perlu Restock Segera!' : 'Semua stok BHP aman' }}
            </p>
        </div>

        <!-- Card 4: Peringatan Kedaluwarsa -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs hover:shadow-md transition-all group {{ $totalExpiringAlertCount > 0 ? 'ring-2 ring-amber-500/20' : '' }}">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-slate-500 uppercase tracking-wider">Akan / Kedaluwarsa</span>
                <div class="w-10 h-10 rounded-2xl {{ $totalExpiringAlertCount > 0 ? 'bg-amber-100 text-amber-700' : 'bg-slate-100 text-slate-600' }} flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="flex items-baseline gap-2">
                <h3 class="text-3xl font-black text-amber-600 font-mono">{{ number_format($totalExpiringAlertCount) }}</h3>
                <span class="text-xs text-slate-500 font-medium">batch</span>
            </div>
            <p class="text-[11px] text-slate-500 mt-2">
                <span class="text-rose-700 font-bold">{{ $expiredCount }} sudah lewat</span> • {{ $totalExpiringAlertCount - $expiredCount }} dalam 90 hari
            </p>
        </div>

    </div>

    <!-- MIDDLE SECTION: Jendela Kedaluwarsa & Reorder Point BHP -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- JENDELA KEDALUWARSA (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col">
            
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-600"></span>
                        <span>Jendela Kedaluwarsa (FEFO Monitoring)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Monitoring batch obat berdasarkan rentang hari kedaluwarsa</p>
                </div>

                <!-- Tabs -->
                <div class="flex items-center gap-1 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                    <button @click="expiryTab = 'expired'" 
                            :class="expiryTab === 'expired' ? 'bg-[#7f1d2d] text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <span>Lewat</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $expiredBatches->count() > 0 ? 'bg-white text-rose-900 font-bold' : 'bg-slate-200 text-slate-700' }}">{{ $expiredBatches->count() }}</span>
                    </button>
                    <button @click="expiryTab = '30'" 
                            :class="expiryTab === '30' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <span>≤30 Hari</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700">{{ $expiring30Batches->count() }}</span>
                    </button>
                    <button @click="expiryTab = '60'" 
                            :class="expiryTab === '60' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <span>≤60 Hari</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700">{{ $expiring60Batches->count() }}</span>
                    </button>
                    <button @click="expiryTab = '90'" 
                            :class="expiryTab === '90' ? 'bg-amber-600 text-white shadow-xs' : 'text-slate-600 hover:text-slate-900'" 
                            class="px-2.5 py-1.5 rounded-lg transition-colors flex items-center gap-1.5">
                        <span>≤90 Hari</span>
                        <span class="px-1.5 py-0.2 rounded-full text-[10px] bg-slate-200 text-slate-700">{{ $expiring90Batches->count() }}</span>
                    </button>
                </div>
            </div>

            <!-- Tab Content: Sudah Lewat -->
            <div x-show="expiryTab === 'expired'" class="flex-1 overflow-x-auto">
                @if($expiredBatches->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-12 h-12 mx-auto mb-2 text-emerald-500/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p class="text-sm font-medium text-slate-600">Tidak ada batch yang sudah kedaluwarsa.</p>
                    <p class="text-xs text-slate-400">Semua batch masih berada dalam batas aman.</p>
                </div>
                @else
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100 pb-2">
                            <th class="py-2.5">Barang</th>
                            <th class="py-2.5">No. Batch</th>
                            <th class="py-2.5">Tgl Kedaluwarsa</th>
                            <th class="py-2.5">Sisa Hari</th>
                            <th class="py-2.5 text-right">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($expiredBatches as $batch)
                        <tr class="hover:bg-rose-50/50 transition-colors">
                            <td class="py-3 font-semibold text-slate-900">
                                <div>{{ $batch->item->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $batch->item->code }} • {{ $batch->item->warehouse_label }}</div>
                            </td>
                            <td class="py-3 font-mono text-slate-700">{{ $batch->batch_no }}</td>
                            <td class="py-3 font-mono text-rose-700 font-bold">{{ $batch->expiry_date->format('d M Y') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full bg-rose-100 text-rose-800 font-bold text-[10px]">
                                    Lewat {{ abs($batch->days_until_expiry) }} hari
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900">
                                {{ $batch->quantity }} {{ $batch->item->unit }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Tab Content: <=30 Hari -->
            <div x-show="expiryTab === '30'" class="flex-1 overflow-x-auto" style="display: none;">
                @if($expiring30Batches->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <p class="text-sm font-medium text-slate-600">Tidak ada batch yang kedaluwarsa dalam 30 hari.</p>
                </div>
                @else
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100 pb-2">
                            <th class="py-2.5">Barang</th>
                            <th class="py-2.5">No. Batch</th>
                            <th class="py-2.5">Tgl Kedaluwarsa</th>
                            <th class="py-2.5">Sisa Hari</th>
                            <th class="py-2.5 text-right">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($expiring30Batches as $batch)
                        <tr class="hover:bg-amber-50/50 transition-colors">
                            <td class="py-3 font-semibold text-slate-900">
                                <div>{{ $batch->item->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $batch->item->code }} • {{ $batch->item->warehouse_label }}</div>
                            </td>
                            <td class="py-3 font-mono text-slate-700">{{ $batch->batch_no }}</td>
                            <td class="py-3 font-mono text-amber-700 font-bold">{{ $batch->expiry_date->format('d M Y') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold text-[10px]">
                                    Sisa {{ $batch->days_until_expiry }} hari
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900">
                                {{ $batch->quantity }} {{ $batch->item->unit }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Tab Content: <=60 Hari -->
            <div x-show="expiryTab === '60'" class="flex-1 overflow-x-auto" style="display: none;">
                @if($expiring60Batches->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <p class="text-sm font-medium text-slate-600">Tidak ada batch yang kedaluwarsa dalam 60 hari.</p>
                </div>
                @else
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100 pb-2">
                            <th class="py-2.5">Barang</th>
                            <th class="py-2.5">No. Batch</th>
                            <th class="py-2.5">Tgl Kedaluwarsa</th>
                            <th class="py-2.5">Sisa Hari</th>
                            <th class="py-2.5 text-right">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($expiring60Batches as $batch)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 font-semibold text-slate-900">
                                <div>{{ $batch->item->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $batch->item->code }}</div>
                            </td>
                            <td class="py-3 font-mono text-slate-700">{{ $batch->batch_no }}</td>
                            <td class="py-3 font-mono text-slate-700">{{ $batch->expiry_date->format('d M Y') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium text-[10px]">
                                    Sisa {{ $batch->days_until_expiry }} hari
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900">
                                {{ $batch->quantity }} {{ $batch->item->unit }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

            <!-- Tab Content: <=90 Hari -->
            <div x-show="expiryTab === '90'" class="flex-1 overflow-x-auto" style="display: none;">
                @if($expiring90Batches->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <p class="text-sm font-medium text-slate-600">Tidak ada batch yang kedaluwarsa dalam 90 hari.</p>
                </div>
                @else
                <table class="w-full text-left text-xs">
                    <thead>
                        <tr class="text-slate-400 font-semibold border-b border-slate-100 pb-2">
                            <th class="py-2.5">Barang</th>
                            <th class="py-2.5">No. Batch</th>
                            <th class="py-2.5">Tgl Kedaluwarsa</th>
                            <th class="py-2.5">Sisa Hari</th>
                            <th class="py-2.5 text-right">Stok</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach($expiring90Batches as $batch)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="py-3 font-semibold text-slate-900">
                                <div>{{ $batch->item->name }}</div>
                                <div class="text-[10px] text-slate-400 font-mono">{{ $batch->item->code }}</div>
                            </td>
                            <td class="py-3 font-mono text-slate-700">{{ $batch->batch_no }}</td>
                            <td class="py-3 font-mono text-slate-700">{{ $batch->expiry_date->format('d M Y') }}</td>
                            <td class="py-3">
                                <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 font-medium text-[10px]">
                                    Sisa {{ $batch->days_until_expiry }} hari
                                </span>
                            </td>
                            <td class="py-3 text-right font-mono font-bold text-slate-900">
                                {{ $batch->quantity }} {{ $batch->item->unit }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @endif
            </div>

        </div>

        <!-- REORDER POINT (BHP) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col">
            <div class="mb-4 pb-4 border-b border-slate-100">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500"></span>
                        <span>Reorder Point BHP</span>
                    </h3>
                    <span class="text-xs px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 font-bold">
                        {{ $criticalStockItems->count() }} Item
                    </span>
                </div>
                <p class="text-xs text-slate-500 mt-0.5">BHP medis yang stoknya di bawah batas minimum</p>
            </div>

            <div class="flex-1 overflow-y-auto max-h-80 space-y-3">
                @if($criticalStockItems->isEmpty())
                <div class="py-12 text-center text-slate-400">
                    <svg class="w-10 h-10 mx-auto mb-2 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    <p class="text-sm font-semibold text-slate-700">Semua Stok BHP Terpenuhi</p>
                    <p class="text-xs text-slate-400">Tidak ada barang yang berada di bawah min. stok.</p>
                </div>
                @else
                @foreach($criticalStockItems as $item)
                <div class="p-3.5 rounded-2xl bg-rose-50/60 border border-rose-200/80 flex items-center justify-between gap-3">
                    <div class="truncate">
                        <p class="text-xs font-bold text-slate-900 truncate">{{ $item->name }}</p>
                        <p class="text-[10px] text-slate-500 font-mono">{{ $item->code }} • {{ $item->warehouse_label }}</p>
                    </div>
                    <div class="text-right shrink-0">
                        <div class="text-xs font-mono font-black text-rose-700">
                            {{ $item->current_stock }} / <span class="text-slate-400">{{ $item->min_stock }}</span> {{ $item->unit }}
                        </div>
                        <span class="text-[9px] px-1.5 py-0.5 rounded bg-rose-200/80 text-rose-900 font-bold uppercase">
                            Batas Kritis
                        </span>
                    </div>
                </div>
                @endforeach
                @endif
            </div>

            <div class="mt-4 pt-3 border-t border-slate-100 text-center">
                <a href="{{ route('movements.index') }}" class="text-xs font-bold text-[#7f1d2d] hover:text-[#991b1b] inline-flex items-center gap-1">
                    <span>Lakukan Pengadaan / Inbound BHP</span>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

    </div>

    <!-- BOTTOM SECTION: 6-Month Trend Chart & 8 Recent Movements -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- GRAFIK TREN 6 BULAN (2 Cols) -->
        <div class="lg:col-span-2 bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-[#7f1d2d]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/></svg>
                        <span>Grafik Tren Konsumsi 6 Bulan (Obat vs BHP)</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">Analisis pengeluaran logistik per bulan untuk prediksi restock</p>
                </div>
                <div class="flex items-center gap-3 text-xs">
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-[#7f1d2d]"></span>
                        <span class="text-slate-600 font-medium">Obat</span>
                    </span>
                    <span class="flex items-center gap-1.5">
                        <span class="w-3 h-3 rounded-md bg-[#059669]"></span>
                        <span class="text-slate-600 font-medium">BHP</span>
                    </span>
                </div>
            </div>

            <div class="flex-1 min-h-[260px] relative">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <!-- 8 AKTIVITAS TERBARU (1 Col) -->
        <div class="bg-white rounded-3xl p-6 border border-slate-200/80 shadow-xs flex flex-col">
            <div class="flex items-center justify-between mb-4 pb-4 border-b border-slate-100">
                <div>
                    <h3 class="text-base font-bold text-slate-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span>Aktivitas Terbaru</span>
                    </h3>
                    <p class="text-xs text-slate-500 mt-0.5">8 pergerakan stok terkini</p>
                </div>
                <a href="{{ route('movements.index') }}" class="text-xs font-bold text-[#7f1d2d] hover:underline">Semua</a>
            </div>

            <div class="flex-1 overflow-y-auto max-h-[300px] space-y-3">
                @if($recentMovements->isEmpty())
                <div class="py-12 text-center text-slate-400 text-xs">
                    Belum ada riwayat pergerakan stok.
                </div>
                @else
                @foreach($recentMovements as $mov)
                <div class="p-3 rounded-2xl bg-slate-50 hover:bg-slate-100/80 transition-colors border border-slate-100">
                    <div class="flex items-center justify-between mb-1">
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border {{ $mov->kind_badge_class }}">
                            {{ $mov->kind_label }}
                        </span>
                        <span class="text-[10px] text-slate-400 font-mono">{{ $mov->created_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-xs font-bold text-slate-900 truncate">{{ $mov->item->name }}</p>
                    <div class="flex items-center justify-between mt-1 text-[11px] text-slate-500">
                        <span class="font-mono font-semibold {{ $mov->kind === 'inbound' ? 'text-emerald-700' : ($mov->kind === 'outbound' ? 'text-rose-700' : 'text-amber-700') }}">
                            {{ $mov->kind === 'inbound' ? '+' : ($mov->kind === 'outbound' ? '-' : ($mov->quantity >= 0 ? '+'.$mov->quantity : $mov->quantity)) }} {{ abs($mov->quantity) }} {{ $mov->item->unit }}
                        </span>
                        <span class="text-slate-400 truncate max-w-[120px]">{{ $mov->actor_name }}</span>
                    </div>
                </div>
                @endforeach
                @endif
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trendChart');
    if (!ctx) return;

    const trendData = @json($trendData);

    new Chart(ctx, {
        type: 'bar',
        data: trendData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    backgroundColor: '#1e293b',
                    padding: 12,
                    cornerRadius: 12,
                    titleFont: { family: 'IBM Plex Sans', size: 12, weight: 'bold' },
                    bodyFont: { family: 'IBM Plex Mono', size: 12 }
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { family: 'IBM Plex Sans', size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: '#f1f5f9' },
                    ticks: { font: { family: 'IBM Plex Mono', size: 11 }, precision: 0 }
                }
            }
        }
    });
});
</script>
@endpush
@endsection
