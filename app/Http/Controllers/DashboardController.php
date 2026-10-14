<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use App\Models\Batch;
use App\Models\Item;
use App\Models\Movement;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $this->resolveWarehouse($request, $user);

        // 1. Metric Cards
        $totalStockUnits = Batch::active()
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->sum('quantity');

        $totalAssets = Asset::when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->count();

        // Critical stock: BHP & BHP Non Medis items with current stock <= min_stock
        $bhpItems = Item::whereIn('category', ['bhp', 'bhp_non_medis'])
            ->where('min_stock', '>', 0)
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->with(['batches'])
            ->get();

        $criticalStockItems = $bhpItems->filter(fn ($item) => $item->current_stock <= $item->min_stock);
        $criticalStockCount = $criticalStockItems->count();

        // Expiring Batches
        $today = Carbon::today();
        $expiringBatchesQuery = Batch::active()
            ->whereNotNull('expiry_date')
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->with('item');

        $allExpiringBatches = (clone $expiringBatchesQuery)
            ->where('expiry_date', '<=', $today->copy()->addDays(90))
            ->orderBy('expiry_date', 'asc')
            ->get();

        $expiredBatches = $allExpiringBatches->filter(fn ($b) => $b->days_until_expiry < 0);
        $expiring30Batches = $allExpiringBatches->filter(fn ($b) => $b->days_until_expiry >= 0 && $b->days_until_expiry <= 30);
        $expiring60Batches = $allExpiringBatches->filter(fn ($b) => $b->days_until_expiry > 30 && $b->days_until_expiry <= 60);
        $expiring90Batches = $allExpiringBatches->filter(fn ($b) => $b->days_until_expiry > 60 && $b->days_until_expiry <= 90);

        $totalExpiringAlertCount = $allExpiringBatches->count();
        $expiredCount = $expiredBatches->count();

        // 2. Asset Status Distribution (Optimized into 1 query)
        $assetStatsCounts = Asset::when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->selectRaw('status, count(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $assetStats = [
            'tersedia' => (int) ($assetStatsCounts['tersedia'] ?? 0),
            'digunakan' => (int) ($assetStatsCounts['digunakan'] ?? 0),
            'rusak' => (int) ($assetStatsCounts['rusak'] ?? 0),
        ];

        // 3. Recent 8 Movements
        $recentMovements = Movement::with('item')
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get();

        // 4. 6-Month Monthly Consumption Trend (Obat vs BHP)
        $trendData = $this->get6MonthTrendData($selectedWarehouse);

        return view('dashboard.index', [
            'selectedWarehouse' => $selectedWarehouse,
            'totalStockUnits' => $totalStockUnits,
            'totalAssets' => $totalAssets,
            'criticalStockCount' => $criticalStockCount,
            'criticalStockItems' => $criticalStockItems,
            'totalExpiringAlertCount' => $totalExpiringAlertCount,
            'expiredCount' => $expiredCount,
            'expiredBatches' => $expiredBatches,
            'expiring30Batches' => $expiring30Batches,
            'expiring60Batches' => $expiring60Batches,
            'expiring90Batches' => $expiring90Batches,
            'assetStats' => $assetStats,
            'recentMovements' => $recentMovements,
            'trendData' => $trendData,
        ]);
    }

    public function trendApi(Request $request): JsonResponse
    {
        $user = Auth::user();
        $selectedWarehouse = $this->resolveWarehouse($request, $user);

        return response()->json($this->get6MonthTrendData($selectedWarehouse));
    }

    private function resolveWarehouse(Request $request, $user): string
    {
        if ($user->isSuperAdmin()) {
            return $request->query('warehouse', 'all');
        }

        return $user->warehouse;
    }

    private function get6MonthTrendData(string $warehouse): array
    {
        $startDate = Carbon::now()->subMonths(5)->startOfMonth();
        $endDate = Carbon::now()->endOfMonth();

        $driver = DB::getDriverName();
        $dateExpr = $driver === 'sqlite'
            ? "strftime('%Y-%m', movements.created_at)"
            : "DATE_FORMAT(movements.created_at, '%Y-%m')";

        $rawSummary = Movement::query()
            ->join('items', 'items.id', '=', 'movements.item_id')
            ->where('movements.kind', 'outbound')
            ->whereIn('items.category', ['obat', 'bhp', 'bhp_non_medis'])
            ->when($warehouse !== 'all', fn ($q) => $q->where('movements.warehouse', $warehouse))
            ->where('movements.created_at', '>=', $startDate)
            ->where('movements.created_at', '<=', $endDate)
            ->selectRaw("{$dateExpr} as ym, items.category, SUM(movements.quantity) as total_qty")
            ->groupBy('ym', 'items.category')
            ->get();

        $grouped = [];
        foreach ($rawSummary as $row) {
            $grouped[$row->ym][$row->category] = (int) $row->total_qty;
        }

        $labels = [];
        $obatData = [];
        $bhpData = [];
        $bhpNonMedisData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');

            $obatData[] = $grouped[$monthKey]['obat'] ?? 0;
            $bhpData[] = $grouped[$monthKey]['bhp'] ?? 0;
            $bhpNonMedisData[] = $grouped[$monthKey]['bhp_non_medis'] ?? 0;
        }

        return [
            'labels' => $labels,
            'datasets' => [
                [
                    'label' => 'Obat Medis (Keluar)',
                    'backgroundColor' => '#991b1b', // Maroon
                    'borderColor' => '#7f1d2d',
                    'data' => $obatData,
                ],
                [
                    'label' => 'BHP Medis (Keluar)',
                    'backgroundColor' => '#059669', // Emerald/Sage
                    'borderColor' => '#047857',
                    'data' => $bhpData,
                ],
                [
                    'label' => 'BHP Non Medis (Keluar)',
                    'backgroundColor' => '#4f46e5', // Indigo
                    'borderColor' => '#3730a3',
                    'data' => $bhpNonMedisData,
                ],
            ],
        ];
    }
}
