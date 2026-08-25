<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Transfer;
use App\Services\InventoryService;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TransferController extends Controller
{
    public function index(Request $request): View
    {
        $user = Auth::user();
        $selectedWarehouse = $user->isSuperAdmin() ? $request->query('warehouse', 'all') : $user->warehouse;
        $status = $request->query('status', 'all');

        $query = Transfer::with(['item', 'requester', 'receiver'])
            ->when($selectedWarehouse !== 'all', function ($q) use ($selectedWarehouse) {
                $q->where(function ($sub) use ($selectedWarehouse) {
                    $sub->where('source_warehouse', $selectedWarehouse)
                        ->orWhere('target_warehouse', $selectedWarehouse);
                });
            })
            ->when($status !== 'all' && in_array($status, ['in_transit', 'received', 'cancelled']), fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc');

        $transfers = $query->paginate(15)->withQueryString();

        // Items available in current warehouse for initiating transfer
        $availableItems = Item::when($selectedWarehouse !== 'all', fn ($q) => $q->where('warehouse', $selectedWarehouse))
            ->with(['batches'])
            ->get()
            ->filter(fn ($item) => $item->current_stock > 0);

        return view('transfers.index', [
            'transfers' => $transfers,
            'selectedWarehouse' => $selectedWarehouse,
            'selectedStatus' => $status,
            'availableItems' => $availableItems,
        ]);
    }

    public function store(Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'target_warehouse' => ['required', 'in:ksr,poliklinik'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ], [
            'item_id.required' => 'Barang yang akan dimutasi wajib dipilih.',
            'target_warehouse.required' => 'Gudang tujuan mutasi wajib dipilih.',
            'quantity.required' => 'Jumlah mutasi wajib diisi.',
        ]);

        $item = Item::findOrFail($validated['item_id']);

        if (! $user->canAccessWarehouse($item->warehouse)) {
            abort(403, 'Akses ditolak untuk gudang asal barang ini.');
        }

        try {
            $transfer = $inventoryService->processTransfer(
                sourceItem: $item,
                targetWarehouse: $validated['target_warehouse'],
                quantity: (int) $validated['quantity'],
                requester: $user,
                notes: $validated['notes'] ?? null
            );

            return back()->with('success', "Mutasi antar gudang #{$transfer->transfer_no} berhasil dibuat (Status: In Transit).");
        } catch (Exception $e) {
            return back()->withInput()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function receive(Transfer $transfer, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();

        // Only target warehouse admin or Super Admin can receive
        if (! $user->isSuperAdmin() && $user->warehouse !== $transfer->target_warehouse) {
            abort(403, 'Hanya admin gudang tujuan atau Super Admin yang dapat menerima transfer ini.');
        }

        try {
            $inventoryService->receiveTransfer($transfer, $user);

            return back()->with('success', "Mutasi #{$transfer->transfer_no} berhasil diterima di {$transfer->target_warehouse}. Stok telah ditambahkan otomatis.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }

    public function cancel(Transfer $transfer, Request $request, InventoryService $inventoryService): RedirectResponse
    {
        $user = Auth::user();

        if (! $user->isSuperAdmin() && $user->warehouse !== $transfer->source_warehouse) {
            abort(403, 'Hanya admin gudang asal atau Super Admin yang dapat membatalkan transfer ini.');
        }

        $reason = $request->input('reason', 'Dibatalkan oleh admin');

        try {
            $inventoryService->cancelTransfer($transfer, $user, $reason);

            return back()->with('success', "Mutasi #{$transfer->transfer_no} telah dibatalkan dan stok telah dikembalikan ke gudang asal.");
        } catch (Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()]);
        }
    }
}
