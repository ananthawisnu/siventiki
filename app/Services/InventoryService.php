<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Item;
use App\Models\Movement;
use App\Models\Transfer;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InventoryService
{
    /**
     * Process Inbound Movement (Barang Masuk)
     */
    public function processInbound(
        Item $item,
        int $quantity,
        string $batchNo,
        ?string $expiryDate,
        string $receivedAt,
        ?string $invoiceNo,
        ?string $source,
        User $actor,
        ?string $notes = null
    ): Movement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah barang masuk harus lebih besar dari 0.');
        }

        if ($item->category === 'obat') {
            if (empty($batchNo)) {
                throw new InvalidArgumentException('Nomor Batch wajib diisi untuk kategori Obat.');
            }
            if (empty($expiryDate)) {
                throw new InvalidArgumentException('Tanggal Kedaluwarsa wajib diisi untuk kategori Obat.');
            }
        } else {
            // For BHP, if batchNo is empty, generate an automatic receipt batch tag
            if (empty($batchNo)) {
                $batchNo = 'BHP-'.now()->format('YmdHis');
            }
        }

        return DB::transaction(function () use ($item, $quantity, $batchNo, $expiryDate, $receivedAt, $invoiceNo, $source, $actor, $notes) {
            // 1. Create or add to batch
            $batch = Batch::create([
                'item_id' => $item->id,
                'warehouse' => $item->warehouse,
                'batch_no' => $batchNo,
                'expiry_date' => $expiryDate ? Carbon::parse($expiryDate) : null,
                'quantity' => $quantity,
                'initial_quantity' => $quantity,
                'received_at' => Carbon::parse($receivedAt),
                'invoice_no' => $invoiceNo,
                'source' => $source,
            ]);

            // 2. Calculate new running balance
            $currentStock = $item->batches()->sum('quantity');

            // 3. Create movement log
            return Movement::create([
                'item_id' => $item->id,
                'warehouse' => $item->warehouse,
                'kind' => 'inbound',
                'quantity' => $quantity,
                'reference_no' => $invoiceNo,
                'source_destination' => $source,
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'notes' => $notes,
                'allocations' => [
                    [
                        'batch_id' => $batch->id,
                        'batch_no' => $batch->batch_no,
                        'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                        'quantity' => $quantity,
                    ],
                ],
                'running_balance' => $currentStock,
            ]);
        });
    }

    /**
     * Process Outbound Movement with FEFO Automatic Allocation (First Expired First Out)
     */
    public function processOutboundFEFO(
        Item $item,
        int $quantity,
        ?string $referenceNo,
        ?string $destination,
        User $actor,
        ?string $notes = null
    ): Movement {
        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah barang keluar harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($item, $quantity, $referenceNo, $destination, $actor, $notes) {
            // Lock active batches for update
            $activeBatches = Batch::where('item_id', $item->id)
                ->where('warehouse', $item->warehouse)
                ->where('quantity', '>', 0)
                ->orderByRaw('expiry_date IS NULL, expiry_date ASC, received_at ASC, id ASC')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $activeBatches->sum('quantity');

            if ($totalAvailable < $quantity) {
                throw new Exception("Stok tidak mencukupi! Tersedia: {$totalAvailable} {$item->unit}, Diminta: {$quantity} {$item->unit}.");
            }

            $remainingToDeduct = $quantity;
            $allocations = [];

            foreach ($activeBatches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $deduct = min($batch->quantity, $remainingToDeduct);
                $batch->quantity -= $deduct;
                $batch->save();

                $allocations[] = [
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                    'quantity' => $deduct,
                ];

                $remainingToDeduct -= $deduct;
            }

            $newBalance = $item->batches()->sum('quantity');

            return Movement::create([
                'item_id' => $item->id,
                'warehouse' => $item->warehouse,
                'kind' => 'outbound',
                'quantity' => $quantity,
                'reference_no' => $referenceNo,
                'source_destination' => $destination,
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'notes' => $notes,
                'allocations' => $allocations,
                'running_balance' => $newBalance,
            ]);
        });
    }

    /**
     * Process Stock Opname (Penyesuaian Fisik vs Sistem)
     * $batchAdjustments format: [ batch_id => physical_quantity ]
     */
    public function processOpname(
        Item $item,
        array $batchAdjustments,
        User $actor,
        ?string $notes = null
    ): array {
        return DB::transaction(function () use ($item, $batchAdjustments, $actor, $notes) {
            $movements = [];
            $allocations = [];
            $totalDelta = 0;

            foreach ($batchAdjustments as $batchId => $physicalQty) {
                $batch = Batch::where('id', $batchId)
                    ->where('item_id', $item->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $physicalQty = max(0, (int) $physicalQty);
                $systemQty = $batch->quantity;
                $delta = $physicalQty - $systemQty;

                if ($delta !== 0) {
                    $batch->quantity = $physicalQty;
                    $batch->save();

                    $allocations[] = [
                        'batch_id' => $batch->id,
                        'batch_no' => $batch->batch_no,
                        'system_quantity' => $systemQty,
                        'physical_quantity' => $physicalQty,
                        'delta' => $delta,
                    ];

                    $totalDelta += $delta;
                }
            }

            $newBalance = (int) $item->batches()->sum('quantity');

            $movement = Movement::create([
                'item_id' => $item->id,
                'warehouse' => $item->warehouse,
                'kind' => 'opname',
                'quantity' => $totalDelta,
                'reference_no' => 'OPN-'.now()->format('YmdHis'),
                'source_destination' => 'Stock Opname Fisik',
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'notes' => $notes ?? 'Penyesuaian hasil stock opname fisik',
                'allocations' => $allocations,
                'running_balance' => $newBalance,
            ]);

            return [
                'movement' => $movement,
                'total_delta' => $totalDelta,
                'new_balance' => $newBalance,
            ];
        });
    }

    /**
     * Create Inter-Warehouse Transfer (Mutasi Antar Gudang - Status In Transit)
     */
    public function processTransfer(
        Item $sourceItem,
        string $targetWarehouse,
        int $quantity,
        User $requester,
        ?string $notes = null
    ): Transfer {
        if ($sourceItem->warehouse === $targetWarehouse) {
            throw new InvalidArgumentException('Gudang asal dan gudang tujuan mutasi tidak boleh sama.');
        }

        if ($quantity <= 0) {
            throw new InvalidArgumentException('Jumlah mutasi harus lebih besar dari 0.');
        }

        return DB::transaction(function () use ($sourceItem, $targetWarehouse, $quantity, $requester, $notes) {
            // Deduct batches using FEFO
            $activeBatches = Batch::where('item_id', $sourceItem->id)
                ->where('warehouse', $sourceItem->warehouse)
                ->where('quantity', '>', 0)
                ->orderByRaw('expiry_date IS NULL, expiry_date ASC, received_at ASC, id ASC')
                ->lockForUpdate()
                ->get();

            $totalAvailable = $activeBatches->sum('quantity');

            if ($totalAvailable < $quantity) {
                throw new Exception("Stok di gudang asal ({$sourceItem->warehouse_label}) tidak mencukupi! Tersedia: {$totalAvailable} {$sourceItem->unit}.");
            }

            $remainingToDeduct = $quantity;
            $allocations = [];

            foreach ($activeBatches as $batch) {
                if ($remainingToDeduct <= 0) {
                    break;
                }

                $deduct = min($batch->quantity, $remainingToDeduct);
                $batch->quantity -= $deduct;
                $batch->save();

                $allocations[] = [
                    'batch_id' => $batch->id,
                    'batch_no' => $batch->batch_no,
                    'expiry_date' => $batch->expiry_date?->format('Y-m-d'),
                    'quantity' => $deduct,
                ];

                $remainingToDeduct -= $deduct;
            }

            $transferNo = Transfer::generateTransferNo();

            // Create transfer record
            $transfer = Transfer::create([
                'transfer_no' => $transferNo,
                'source_warehouse' => $sourceItem->warehouse,
                'target_warehouse' => $targetWarehouse,
                'item_id' => $sourceItem->id,
                'quantity' => $quantity,
                'status' => 'in_transit',
                'allocations' => $allocations,
                'requester_id' => $requester->id,
                'requester_name' => $requester->name,
                'notes' => $notes,
                'sent_at' => now(),
            ]);

            // Record movement in source warehouse
            Movement::create([
                'item_id' => $sourceItem->id,
                'warehouse' => $sourceItem->warehouse,
                'kind' => 'outbound',
                'quantity' => $quantity,
                'reference_no' => $transferNo,
                'source_destination' => 'Mutasi ke '.($targetWarehouse === 'poliklinik' ? 'Gudang Inventaris' : 'Gudang Obat'),
                'actor_id' => $requester->id,
                'actor_name' => $requester->name,
                'notes' => "Transfer antar gudang #{$transferNo}: {$notes}",
                'allocations' => $allocations,
                'running_balance' => $sourceItem->batches()->sum('quantity'),
            ]);

            return $transfer;
        });
    }

    /**
     * Receive Transfer at Destination Warehouse
     */
    public function receiveTransfer(Transfer $transfer, User $receiver): void
    {
        if ($transfer->status !== 'in_transit') {
            throw new Exception('Transfer ini tidak dalam status In Transit atau sudah diproses.');
        }

        DB::transaction(function () use ($transfer, $receiver) {
            $sourceItem = $transfer->item;

            // Find or create sibling item in target warehouse
            $targetItem = Item::firstOrCreate(
                [
                    'name' => $sourceItem->name,
                    'category' => $sourceItem->category,
                    'warehouse' => $transfer->target_warehouse,
                ],
                [
                    'code' => ($transfer->target_warehouse === 'poliklinik' ? 'POL-' : 'KSR-').$sourceItem->code,
                    'unit' => $sourceItem->unit,
                    'min_stock' => $sourceItem->min_stock,
                    'description' => $sourceItem->description,
                ]
            );

            // Replicate batches into destination
            $targetAllocations = [];
            foreach ($transfer->allocations as $alloc) {
                $newBatch = Batch::create([
                    'item_id' => $targetItem->id,
                    'warehouse' => $transfer->target_warehouse,
                    'batch_no' => $alloc['batch_no'] ?? 'TRF-'.now()->format('Ymd'),
                    'expiry_date' => ! empty($alloc['expiry_date']) ? Carbon::parse($alloc['expiry_date']) : null,
                    'quantity' => $alloc['quantity'],
                    'initial_quantity' => $alloc['quantity'],
                    'received_at' => now(),
                    'invoice_no' => $transfer->transfer_no,
                    'source' => 'Mutasi dari '.($transfer->source_warehouse === 'poliklinik' ? 'Gudang Inventaris' : 'Gudang Obat'),
                ]);

                $targetAllocations[] = [
                    'batch_id' => $newBatch->id,
                    'batch_no' => $newBatch->batch_no,
                    'expiry_date' => $newBatch->expiry_date?->format('Y-m-d'),
                    'quantity' => $alloc['quantity'],
                ];
            }

            // Update transfer record
            $transfer->update([
                'status' => 'received',
                'receiver_id' => $receiver->id,
                'receiver_name' => $receiver->name,
                'received_at' => now(),
            ]);

            // Record inbound movement in destination warehouse
            Movement::create([
                'item_id' => $targetItem->id,
                'warehouse' => $transfer->target_warehouse,
                'kind' => 'inbound',
                'quantity' => $transfer->quantity,
                'reference_no' => $transfer->transfer_no,
                'source_destination' => 'Mutasi Masuk dari '.($transfer->source_warehouse === 'poliklinik' ? 'Gudang Inventaris' : 'Gudang Obat'),
                'actor_id' => $receiver->id,
                'actor_name' => $receiver->name,
                'notes' => "Penerimaan mutasi #{$transfer->transfer_no}",
                'allocations' => $targetAllocations,
                'running_balance' => $targetItem->batches()->sum('quantity'),
            ]);
        });
    }

    /**
     * Cancel Transfer and restore source batches
     */
    public function cancelTransfer(Transfer $transfer, User $actor, ?string $reason = null): void
    {
        if ($transfer->status !== 'in_transit') {
            throw new Exception('Hanya transfer berstatus In Transit yang dapat dibatalkan.');
        }

        DB::transaction(function () use ($transfer, $actor, $reason) {
            $sourceItem = $transfer->item;

            // Restore batch quantities
            foreach ($transfer->allocations as $alloc) {
                if (! empty($alloc['batch_id'])) {
                    $batch = Batch::find($alloc['batch_id']);
                    if ($batch) {
                        $batch->quantity += $alloc['quantity'];
                        $batch->save();
                    }
                }
            }

            $transfer->update([
                'status' => 'cancelled',
                'notes' => ($transfer->notes ? $transfer->notes.' | ' : '').'Dibatalkan oleh '.$actor->name.($reason ? ': '.$reason : ''),
            ]);

            // Record restoring movement
            Movement::create([
                'item_id' => $sourceItem->id,
                'warehouse' => $sourceItem->warehouse,
                'kind' => 'inbound',
                'quantity' => $transfer->quantity,
                'reference_no' => 'BATAL-'.$transfer->transfer_no,
                'source_destination' => 'Pembatalan Mutasi '.$transfer->transfer_no,
                'actor_id' => $actor->id,
                'actor_name' => $actor->name,
                'notes' => 'Pengembalian stok akibat pembatalan transfer: '.($reason ?? '-'),
                'allocations' => $transfer->allocations,
                'running_balance' => $sourceItem->batches()->sum('quantity'),
            ]);
        });
    }
}
