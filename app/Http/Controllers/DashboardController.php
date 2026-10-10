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

        // 2. Asset Status Distribution
        $assetStats = [
            'tersedia' => Asset::where('status', 'tersedia')
                ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
                ->count(),
            'digunakan' => Asset::where('status', 'digunakan')
                ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
                ->count(),
            'rusak' => Asset::where('status', 'rusak')
                ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
                ->count(),
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
        $labels = [];
        $obatData = [];
        $bhpData = [];
        $bhpNonMedisData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $monthKey = $month->format('Y-m');
            $labels[] = $month->translatedFormat('M Y');

            // Outbound Obat in this month
            $obatOutbound = Movement::where('kind', 'outbound')
                ->whereHas('item', fn ($q) => $q->where('category', 'obat'))
                ->when($warehouse !== 'all', fn ($q) => $q->where('warehouse', $warehouse))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');

            // Outbound BHP Medis in this month
            $bhpOutbound = Movement::where('kind', 'outbound')
                ->whereHas('item', fn ($q) => $q->where('category', 'bhp'))
                ->when($warehouse !== 'all', fn ($q) => $q->where('warehouse', $warehouse))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');

            // Outbound BHP Non Medis in this month
            $bhpNonMedisOutbound = Movement::where('kind', 'outbound')
                ->whereHas('item', fn ($q) => $q->where('category', 'bhp_non_medis'))
                ->when($warehouse !== 'all', fn ($q) => $q->where('warehouse', $warehouse))
                ->whereYear('created_at', $month->year)
                ->whereMonth('created_at', $month->month)
                ->sum('quantity');

            $obatData[] = (int) $obatOutbound;
            $bhpData[] = (int) $bhpOutbound;
            $bhpNonMedisData[] = (int) $bhpNonMedisOutbound;
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
