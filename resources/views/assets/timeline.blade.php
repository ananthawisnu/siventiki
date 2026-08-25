@extends('layouts.app')

@section('title', 'Timeline Riwayat - ' . $asset->name)
@section('header_title', 'Timeline & Riwayat Aktivitas Aset')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    <!-- Back Button & Asset Header Card -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
        <div>
            <div class="flex items-center gap-2 mb-2">
                <a href="{{ route('assets.index') }}" class="text-xs text-rose-800 font-bold hover:underline inline-flex items-center gap-1">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    <span>Kembali ke Daftar Aset</span>
                </a>
                <span class="text-slate-300">•</span>
                <span class="px-2 py-0.5 rounded-md bg-slate-100 text-slate-700 text-[10px] font-medium">
                    {{ $asset->warehouse_label }}
                </span>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">{{ $asset->name }}</h2>
            <p class="text-xs font-mono text-slate-500 mt-1">KODE: {{ $asset->code }} • Lokasi: {{ $asset->location ?? '-' }}</p>
        </div>

        <div class="text-right shrink-0">
            <span class="px-3.5 py-1.5 rounded-full text-xs font-bold border {{ $asset->status_badge_class }}">
                Status Terkini: {{ $asset->status_label }}
            </span>
        </div>
    </div>

    <!-- VERTICAL TIMELINE CONTAINER -->
    <div class="bg-white rounded-3xl p-6 lg:p-8 border border-slate-200/80 shadow-xs">
        <h3 class="text-base font-bold text-slate-900 mb-8 flex items-center gap-2">
            <svg class="w-5 h-5 text-rose-800" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <span>Kronologi Aktivitas & Jejak Audit (Audit Trail)</span>
        </h3>

        @if($events->isEmpty())
        <div class="py-12 text-center text-slate-400 text-xs">
            Belum ada riwayat aktivitas yang tercatat.
        </div>
        @else
        <div class="relative pl-6 border-l-2 border-slate-200 space-y-8 ml-3">
            @foreach($events as $event)
            <div class="relative group">
                <!-- Timeline Dot Indicator -->
                <div class="absolute -left-[31px] top-1 w-5 h-5 rounded-full border-4 border-white shadow-xs
                    {{ $event['action'] === 'borrow' ? 'bg-amber-500' : 
                      ($event['action'] === 'return' ? 'bg-emerald-500' : 
                      ($event['action'] === 'damage' ? 'bg-rose-600' : 
                      ($event['action'] === 'repair' ? 'bg-teal-500' : 
                      ($event['action'] === 'cross_loan' ? 'bg-purple-600' : 'bg-blue-500')))) }}">
                </div>

                <div class="bg-slate-50 hover:bg-slate-100/70 p-4 rounded-2xl border border-slate-200/80 transition-colors">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-1 mb-2">
                        <span class="px-2.5 py-0.5 rounded-full text-xs font-bold border inline-block w-fit {{ $event['badge'] }}">
                            {{ $event['label'] }}
                        </span>
                        <span class="text-[11px] font-mono text-slate-500">
                            {{ $event['date']->format('d M Y, H:i') }} ({{ $event['date']->diffForHumans() }})
                        </span>
                    </div>

                    @if($event['borrower'])
                    <div class="text-xs text-slate-700 mt-1">
                        <span class="text-slate-400">Peminjam/PIC:</span> <strong class="text-slate-900">{{ $event['borrower'] }}</strong>
                        @if($event['estimate'])
                        <span class="text-slate-400 ml-2">• Est. Kembali:</span> <span class="font-mono font-semibold text-rose-700">{{ $event['estimate'] }}</span>
                        @endif
                    </div>
                    @endif

                    @if($event['purpose'])
                    <p class="text-xs text-slate-700 mt-1.5 bg-white p-2.5 rounded-xl border border-slate-100">
                        <span class="text-slate-400 block text-[10px] uppercase font-bold">Keperluan / Catatan:</span>
                        {{ $event['purpose'] }}
                    </p>
                    @endif

                    @if($event['damage_notes'])
                    <p class="text-xs text-rose-900 mt-1.5 bg-rose-50 p-2.5 rounded-xl border border-rose-200 font-medium">
                        <span class="text-rose-600 block text-[10px] uppercase font-bold">Catatan Kerusakan / Status:</span>
                        {{ $event['damage_notes'] }}
                    </p>
                    @endif

                    <div class="mt-2 text-[10px] text-slate-400 flex items-center justify-between">
                        <span>Aktor / Petugas: <strong>{{ $event['actor'] }}</strong></span>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>

</div>
@endsection
