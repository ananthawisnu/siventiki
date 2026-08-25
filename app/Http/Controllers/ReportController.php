<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Movement;
use App\Models\Setting;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $kind = $request->query('kind', 'movements');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->toDateString());

        $reportData = $this->getReportData($kind, $selectedWarehouse, $dateFrom, $dateTo);

        return view('reports.index', [
            'selectedWarehouse' => $selectedWarehouse,
            'kind' => $kind,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'reportData' => $reportData,
        ]);
    }

    public function printView(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $kind = $request->query('kind', 'movements');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->toDateString());

        $reportData = $this->getReportData($kind, $selectedWarehouse, $dateFrom, $dateTo);

        $settings = [
            'org_name' => Setting::get('org_name', 'KORPS SUKARELA PALANG MERAH INDONESIA'),
            'unit_name' => Setting::get('unit_name', 'UNIT INSTITUT BISNIS DAN TEKNOLOGI INDONESIA (INSTIKI)'),
            'org_address' => Setting::get('org_address', 'Jl. Tukad Pakerisan No.97, Panjer, Denpasar Selatan, Bali 80225'),
            'org_contact' => Setting::get('org_contact', 'Telp: (0361) 256995 | Email: ksrpmi@instiki.ac.id'),
            'logo_left' => Setting::get('logo_left'),
            'logo_right' => Setting::get('logo_right'),
            'leader_name' => Setting::get('leader_name', 'I Komang Wisnu Anantha'),
            'leader_nim' => Setting::get('leader_nim', '2401020002'),
        ];

        return view('reports.print', [
            'selectedWarehouse' => $selectedWarehouse,
            'kind' => $kind,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'reportData' => $reportData,
            'settings' => $settings,
            'printedBy' => $user->name,
            'printedAt' => now()->translatedFormat('d F Y H:i:s'),
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $kind = $request->query('kind', 'movements');
        $dateFrom = $request->query('date_from', Carbon::now()->startOfMonth()->toDateString());
        $dateTo = $request->query('date_to', Carbon::now()->toDateString());

        $reportData = $this->getReportData($kind, $selectedWarehouse, $dateFrom, $dateTo);
        $fileName = "Laporan_{$kind}_".now()->format('Ymd_His').'.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($kind, $reportData, $selectedWarehouse, $dateFrom, $dateTo) {
            $handle = fopen('php://output', 'w');
            // Write UTF-8 BOM for Excel compatibility
            fwrite($handle, "\xEF\xBB\xBF");

            // Write Title Headers
            fputcsv($handle, ['KSR PMI UNIT INSTIKI - SISTEM INVENTARIS']);
            fputcsv($handle, ['LAPORAN: '.strtoupper($kind)]);
            fputcsv($handle, ['Gudang: '.ucfirst($selectedWarehouse), 'Periode: '.$dateFrom.' s/d '.$dateTo]);
            fputcsv($handle, ['Diekspor pada: '.now()->format('Y-m-d H:i:s')]);
            fputcsv($handle, []); // Blank line

            if ($kind === 'movements') {
                fputcsv($handle, ['No', 'Tanggal', 'Jenis Mutasi', 'Kode Barang', 'Nama Barang', 'Kategori', 'Gudang', 'Jumlah', 'Satuan', 'Faktur/Ref', 'Sumber/Tujuan', 'Aktor', 'Catatan']);
                foreach ($reportData['records'] as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->created_at->format('Y-m-d H:i'),
                        $row->kind_label,
                        $row->item->code,
                        $row->item->name,
                        $row->item->category_label,
                        $row->item->warehouse_label,
                        $row->quantity,
                        $row->item->unit,
                        $row->reference_no ?? '-',
                        $row->source_destination ?? '-',
                        $row->actor_name,
                        $row->notes ?? '-',
                    ]);
                }
            } elseif ($kind === 'assets') {
                fputcsv($handle, ['No', 'Kode Aset', 'Nama Aset', 'Gudang', 'Tahun Perolehan', 'Sumber Dana', 'Lokasi', 'Status', 'Keterangan']);
                foreach ($reportData['records'] as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->code,
                        $row->name,
                        $row->warehouse_label,
                        $row->acquisition_year,
                        $row->funding_source,
                        $row->location ?? '-',
                        $row->status_label,
                        $row->description ?? '-',
                    ]);
                }
            } elseif ($kind === 'opname') {
                fputcsv($handle, ['No', 'Tanggal Opname', 'Kode Barang', 'Nama Barang', 'Gudang', 'Selisih Delta', 'Saldo Akhir', 'Penanggung Jawab', 'Catatan']);
                foreach ($reportData['records'] as $index => $row) {
                    fputcsv($handle, [
                        $index + 1,
                        $row->created_at->format('Y-m-d H:i'),
                        $row->item->code,
                        $row->item->name,
                        $row->item->warehouse_label,
                        ($row->quantity > 0 ? '+'.$row->quantity : $row->quantity).' '.$row->item->unit,
                        $row->running_balance.' '.$row->item->unit,
                        $row->actor_name,
                        $row->notes ?? '-',
                    ]);
                }
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getReportData(string $kind, string $warehouse, string $dateFrom, string $dateTo): array
    {
        $startDate = Carbon::parse($dateFrom)->startOfDay();
        $endDate = Carbon::parse($dateTo)->endOfDay();

        if ($kind === 'movements') {
            $records = Movement::with('item')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->when($warehouse !== 'all', fn ($q) => $q->where('warehouse', $warehouse))
                ->orderBy('created_at', 'desc')
                ->get();

            $summary = [
                'total_inbound' => $records->where('kind', 'inbound')->sum('quantity'),
                'total_outbound' => $records->where('kind', 'outbound')->sum('quantity'),
                'total_opname' => $records->where('kind', 'opname')->count(),
            ];

            return [
                'title' => 'Laporan Pergerakan Stok & Mutasi',
                'records' => $records,
                'summary' => $summary,
            ];
        }

        if ($kind === 'assets') {
            $records = Asset::when($warehouse !== 'all', fn ($q) => $q->where('warehouse', $warehouse))
                ->orderBy('created_at', 'desc')
                ->get();

            $summary = [
                'total' => $records->count(),
                'tersedia' => $records->where('status', 'tersedia')->count(),
                'digunakan' => $records->where('status', 'digunakan')->count(),
                'rusak' => $records->where('status', 'rusak')->count(),
            ];

            return [
                'title' => 'Laporan Inventaris Aset Tetap',
                'records' => $records,
                'summary' => $summary,
            ];
        }

        // Opname Recap
        $records = Movement::with('item')
            ->where('kind', 'opname')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->when($warehouse !== 'all', fn ($q) => $q->where('warehouse', $warehouse))
            ->orderBy('created_at', 'desc')
            ->get();

        $summary = [
            'total_adjustments' => $records->count(),
            'positive_delta' => $records->where('quantity', '>', 0)->sum('quantity'),
            'negative_delta' => $records->where('quantity', '<', 0)->sum('quantity'),
        ];

        return [
            'title' => 'Laporan Rekapitulasi Stock Opname Fisik',
            'records' => $records,
            'summary' => $summary,
        ];
    }
}
