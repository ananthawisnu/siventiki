<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Movement;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $kind = $request->query('kind', 'all');
        $search = $request->query('search');

        $query = Movement::with(['item', 'actor'])
            ->when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->when($kind !== 'all' && in_array($kind, ['inbound', 'outbound', 'opname']), fn ($q) => $q->where('kind', $kind))
            ->when($search, function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('reference_no', 'LIKE', "%{$search}%")
                        ->orWhere('source_destination', 'LIKE', "%{$search}%")
                        ->orWhere('actor_name', 'LIKE', "%{$search}%")
                        ->orWhereHas('item', fn ($i) => $i->where('name', 'LIKE', "%{$search}%"));
                });
            })
            ->orderBy('created_at', 'desc');

        $movements = $query->paginate(20)->withQueryString();

        // Items available for Inbound / Outbound selection in this warehouse
        $availableItems = Item::when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->with('activeBatches')
            ->orderBy('name')
            ->get();

        return view('movements.index', [
            'movements' => $movements,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedKind' => $kind,
            'search' => $search,
            'availableItems' => $availableItems,
        ]);
    }

    public function storeInbound(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'received_at' => ['required', 'date'],
            'invoice_no' => ['nullable', 'string', 'max:100'],
            'source' => ['nullable', 'string', 'max:255'],
            'batch_no' => ['nullable', 'string', 'max:100'],
            'expiry_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'item_id.required' => 'Barang wajib dipilih.',
            'quantity.required' => 'Jumlah barang masuk wajib diisi.',
            'quantity.min' => 'Jumlah barang masuk minimal 1.',
            'received_at.required' => 'Tanggal terima wajib diisi.',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang ini.');
        }

        try {
            $inventoryService->processInbound(
                item: $item,
                quantity: (int) $validated['quantity'],
                batchNo: (string) ($validated['batch_no'] ?? ''),
                expiryDate: $validated['expiry_date'] ?? null,
                receivedAt: $validated['received_at'],
                invoiceNo: $validated['invoice_no'] ?? null,
                source: $validated['source'] ?? null,
                actor: $user,
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', "Berhasil mencatat Inbound {$validated['quantity']} {$item->unit} untuk {$item->name}.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function storeOutbound(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'destination' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'item_id.required' => 'Barang wajib dipilih.',
            'quantity.required' => 'Jumlah barang keluar wajib diisi.',
            'quantity.min' => 'Jumlah barang keluar minimal 1.',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang ini.');
        }

        try {
            $movement = $inventoryService->processOutboundFEFO(
                item: $item,
                quantity: (int) $validated['quantity'],
                referenceNo: $validated['reference_no'] ?? null,
                destination: $validated['destination'] ?? null,
                actor: $user,
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', "Berhasil mencatat Outbound FEFO {$validated['quantity']} {$item->unit} untuk {$item->name}. Alokasi batch berhasil dicatat.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function storeOpname(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'batches' => ['required', 'array', 'min:1'],
            'batches.*' => ['required', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'item_id.required' => 'Barang wajib dipilih.',
            'batches.required' => 'Data batch fisik wajib diisi.',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang ini.');
        }

        try {
            $result = $inventoryService->processOpname(
                item: $item,
                batchAdjustments: $validated['batches'],
                actor: $user,
                notes: $validated['notes'] ?? null
            );

            $deltaStr = ($result['total_delta'] >= 0 ? '+'.$result['total_delta'] : $result['total_delta']);

            return back()->with('success', "Stock opname untuk {$item->name} berhasil disimpan. Selisih penyesuaian: {$deltaStr} {$item->unit}.");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
