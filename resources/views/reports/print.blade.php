<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    <title>{{ $reportData['title'] }} - KSR PMI Unit INSTIKI</title>
    @vite(['resources/css/app.css'])
    <style>
        @page {
            size: A4 landscape;
            margin: 1.2cm 1.5cm;
        }
        body {
            font-family: 'IBM Plex Sans', ui-sans-serif, system-ui, sans-serif;
            color: #0f172a;
            background-color: #ffffff;
            font-size: 10pt;
        }
        .kop-border {
            border-bottom: 3px double #7f1d2d;
        }
    </style>
</head>
<body class="p-4 sm:p-8 max-w-6xl mx-auto">

    <!-- Print Floating Toolbar (Hidden when printing) -->
    <div class="no-print mb-6 p-4 rounded-2xl bg-slate-900 text-white flex items-center justify-between shadow-xl">
        <div class="flex items-center gap-3">
            <span class="w-3 h-3 rounded-full bg-emerald-400 animate-pulse"></span>
            <div>
                <p class="text-xs font-bold">Mode Pratinjau Cetak / PDF Resmi</p>
                <p class="text-[11px] text-slate-300">Gunakan dialog cetak browser (Ctrl + P) untuk simpan sebagai PDF A4 Landscape.</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="window.print()" class="px-5 py-2 rounded-xl bg-[#7f1d2d] hover:bg-[#991b1b] text-white font-bold text-xs shadow-md transition-all cursor-pointer flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                <span>Cetak / Simpan PDF</span>
            </button>
            <button onclick="window.close()" class="px-3 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-300 text-xs font-semibold">
                Tutup
            </button>
        </div>
    </div>

    <!-- OFFICIAL LETTERHEAD (KOP SURAT 3 KOLOM) -->
    <div class="pb-4 mb-6 kop-border">
        <table class="w-full">
            <tr>
                <!-- Left Logo: PMI Red Cross -->
                <td class="w-24 text-left align-middle pr-4">
                    @if(!empty($settings['logo_left']))
                        <img src="{{ $settings['logo_left'] }}" alt="Logo PMI" class="h-20 w-auto object-contain">
                    @else
                        <div class="w-18 h-18 rounded-2xl bg-white border border-rose-200 shadow-xs flex items-center justify-center p-2">
                            <svg viewBox="0 0 24 24" class="w-14 h-14 fill-[#dc2626]">
                                <path d="M9 4h6v5h5v6h-5v5H9v-5H4V9h5V4z"/>
                            </svg>
                        </div>
                    @endif
                </td>

                <!-- Center Title & Institution Info -->
                <td class="text-center align-middle px-4">
                    <h2 class="text-lg font-black tracking-wider text-slate-900 uppercase leading-snug">
                        {{ $settings['org_name'] }}
                    </h2>
                    <h3 class="text-base font-extrabold text-[#7f1d2d] uppercase tracking-wide leading-snug">
                        {{ $settings['unit_name'] }}
                    </h3>
                    <p class="text-xs text-slate-600 mt-1">
                        {{ $settings['org_address'] }}
                    </p>
                    <p class="text-[11px] text-slate-500 font-mono mt-0.5">
                        {{ $settings['org_contact'] }}
                    </p>
                </td>

                <!-- Right Logo: INSTIKI -->
                <td class="w-24 text-right align-middle pl-4">
                    @if(!empty($settings['logo_right']))
                        <img src="{{ $settings['logo_right'] }}" alt="Logo INSTIKI" class="h-20 w-auto object-contain ml-auto">
                    @else
                        <div class="w-18 h-18 rounded-2xl bg-[#7f1d2d] text-white flex flex-col items-center justify-center shadow-xs ml-auto border border-rose-800">
                            <span class="font-black text-sm tracking-widest">INSTIKI</span>
                            <span class="text-[8px] font-mono text-rose-200">CAMPUS</span>
                        </div>
                    @endif
                </td>
            </tr>
        </table>
    </div>

    <!-- REPORT TITLE & METADATA -->
    <div class="mb-6 text-center">
        <h1 class="text-base font-black text-slate-900 uppercase tracking-wide">
            {{ $reportData['title'] }}
        </h1>
        <div class="flex items-center justify-center gap-3 text-xs text-slate-600 mt-1 font-mono">
            <span>Lingkup: <strong class="text-slate-900">{{ ucfirst($selectedWarehouse) }}</strong></span>
            <span>•</span>
            <span>Periode: <strong>{{ date('d/m/Y', strtotime($dateFrom)) }} s/d {{ date('d/m/Y', strtotime($dateTo)) }}</strong></span>
            <span>•</span>
            <span>Dicetak: {{ $printedAt }}</span>
        </div>
    </div>

    <!-- SUMMARY HIGHLIGHTS -->
    <div class="mb-6 p-4 rounded-xl bg-slate-50 border border-slate-200 text-xs">
        <div class="grid grid-cols-3 gap-4 text-center font-mono">
            @if($kind === 'movements')
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Total Inbound (Masuk)</span>
                <span class="text-base font-black text-emerald-700">+{{ number_format($reportData['summary']['total_inbound']) }} unit</span>
            </div>
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Total Outbound (FEFO)</span>
                <span class="text-base font-black text-rose-700">-{{ number_format($reportData['summary']['total_outbound']) }} unit</span>
            </div>
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Frekuensi Stock Opname</span>
                <span class="text-base font-black text-amber-700">{{ number_format($reportData['summary']['total_opname']) }} kali</span>
            </div>
            @elseif($kind === 'assets')
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Total Aset</span>
                <span class="text-base font-black text-slate-900">{{ number_format($reportData['summary']['total']) }} item</span>
            </div>
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Tersedia Siap Pakai</span>
                <span class="text-base font-black text-emerald-700">{{ number_format($reportData['summary']['tersedia']) }} item</span>
            </div>
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Sedang Digunakan / Rusak</span>
                <span class="text-base font-black text-rose-700">{{ number_format($reportData['summary']['digunakan'] + $reportData['summary']['rusak']) }} item</span>
            </div>
            @elseif($kind === 'opname')
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Penyesuaian Batch</span>
                <span class="text-base font-black text-slate-900">{{ number_format($reportData['summary']['total_adjustments']) }} batch</span>
            </div>
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Selisih Positif (+)</span>
                <span class="text-base font-black text-emerald-700">+{{ number_format($reportData['summary']['positive_delta']) }} unit</span>
            </div>
            <div>
                <span class="text-slate-500 font-sans block text-[10px] uppercase font-bold">Selisih Negatif (-)</span>
                <span class="text-base font-black text-rose-700">{{ number_format($reportData['summary']['negative_delta']) }} unit</span>
            </div>
            @endif
        </div>
    </div>

    <!-- MAIN DATA TABLE -->
    <table class="w-full text-left text-xs border border-slate-300 mb-8">
        <thead>
            <tr class="bg-[#7f1d2d] text-white font-bold uppercase tracking-wider text-[10px]">
                <th class="py-2.5 px-3 border border-slate-400/40 text-center w-8">No</th>
                @if($kind === 'movements')
                <th class="py-2.5 px-3 border border-slate-400/40">Tanggal & Waktu</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Jenis Mutasi</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Kode & Nama Barang</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Gudang</th>
                <th class="py-2.5 px-3 border border-slate-400/40 text-right">Jumlah</th>
                <th class="py-2.5 px-3 border border-slate-400/40 text-right">Saldo Akhir</th>
                <th class="py-2.5 px-3 border border-slate-400/40">No. Ref / Sumber</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Petugas PIC</th>
                @elseif($kind === 'assets')
                <th class="py-2.5 px-3 border border-slate-400/40">Kode Aset</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Nama Aset</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Gudang</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Tahun</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Sumber Dana</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Lokasi</th>
                <th class="py-2.5 px-3 border border-slate-400/40 text-center">Status</th>
                @elseif($kind === 'opname')
                <th class="py-2.5 px-3 border border-slate-400/40">Tanggal</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Kode & Nama Barang</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Gudang</th>
                <th class="py-2.5 px-3 border border-slate-400/40 text-right">Selisih Fisik</th>
                <th class="py-2.5 px-3 border border-slate-400/40 text-right">Saldo Fisik</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Petugas</th>
                <th class="py-2.5 px-3 border border-slate-400/40">Catatan</th>
                @endif
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-300 font-mono">
            @forelse($reportData['records'] as $idx => $row)
            <tr class="{{ $idx % 2 === 0 ? 'bg-white' : 'bg-slate-50' }}">
                <td class="py-2 px-3 border border-slate-300 text-center font-sans text-slate-500">{{ $idx + 1 }}</td>

                @if($kind === 'movements')
                <td class="py-2 px-3 border border-slate-300 font-sans text-slate-700">{{ $row->created_at->format('d/m/Y H:i') }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans font-bold">{{ $row->kind_label }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans font-bold text-slate-900">{{ $row->item->name }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->item->warehouse_label }}</td>
                <td class="py-2 px-3 border border-slate-300 text-right font-bold {{ $row->kind === 'inbound' ? 'text-emerald-800' : 'text-rose-800' }}">
                    {{ $row->kind === 'inbound' ? '+' : ($row->kind === 'outbound' ? '-' : '') }}{{ $row->quantity }} {{ $row->item->unit }}
                </td>
                <td class="py-2 px-3 border border-slate-300 text-right font-black">{{ $row->running_balance }} {{ $row->item->unit }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans text-slate-700">{{ $row->reference_no ?? ($row->source_destination ?? '-') }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans text-slate-700">{{ $row->actor_name }}</td>

                @elseif($kind === 'assets')
                <td class="py-2 px-3 border border-slate-300 font-bold text-rose-900">{{ $row->code }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans font-bold text-slate-900">{{ $row->name }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->warehouse_label }}</td>
                <td class="py-2 px-3 border border-slate-300 text-center">{{ $row->acquisition_year }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->funding_source }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->location ?? '-' }}</td>
                <td class="py-2 px-3 border border-slate-300 text-center font-sans font-bold uppercase text-[10px]">
                    {{ $row->status_label }}
                </td>

                @elseif($kind === 'opname')
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->created_at->format('d/m/Y H:i') }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans font-bold">{{ $row->item->name }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->item->warehouse_label }}</td>
                <td class="py-2 px-3 border border-slate-300 text-right font-bold {{ $row->quantity >= 0 ? 'text-emerald-800' : 'text-rose-800' }}">
                    {{ $row->quantity > 0 ? '+'.$row->quantity : $row->quantity }} {{ $row->item->unit }}
                </td>
                <td class="py-2 px-3 border border-slate-300 text-right font-black">{{ $row->running_balance }} {{ $row->item->unit }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans">{{ $row->actor_name }}</td>
                <td class="py-2 px-3 border border-slate-300 font-sans italic text-slate-600">{{ $row->notes ?? '-' }}</td>
                @endif
            </tr>
            @empty
            <tr>
                <td colspan="9" class="py-8 text-center text-slate-400 font-sans">Tidak ada data untuk dicetak.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- SIGNATURE BLOCK (TANDA TANGAN RESMI) -->
    <div class="mt-12 break-inside-avoid">
        <table class="w-full text-center text-xs">
            <tr>
                <td class="w-1/2 align-top">
                    <p class="text-slate-600">Mengetahui,</p>
                    <p class="font-bold text-slate-900 mt-1">Ketua KSR PMI Unit INSTIKI</p>
                    <div class="h-20"></div>
                    <p class="font-bold text-slate-900 underline">{{ $settings['leader_name'] }}</p>
                    <p class="text-[11px] font-mono text-slate-600">NIM: {{ $settings['leader_nim'] }}</p>
                </td>
                <td class="w-1/2 align-top">
                    <p class="text-slate-600">Denpasar, {{ now()->translatedFormat('d F Y') }}</p>
                    <p class="font-bold text-slate-900 mt-1">Penanggung Jawab Logistik / Gudang</p>
                    <div class="h-20"></div>
                    <p class="font-bold text-slate-900 underline">{{ $printedBy }}</p>
                    <p class="text-[11px] font-mono text-slate-600">NIM: {{ auth()->user()->nim }}</p>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
