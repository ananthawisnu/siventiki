<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Master Items (Obat & BHP)
        Schema::create('items', function (Blueprint $table) {
            $table->id();
            $table->string('code', 50)->unique();
            $table->string('name');
            $table->enum('category', ['obat', 'bhp']);
            $table->enum('warehouse', ['ksr', 'poliklinik']);
            $table->string('unit', 50)->default('pcs');
            $table->integer('min_stock')->default(0);
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['warehouse', 'category']);
        });

        // 2. Batches (FEFO source)
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('warehouse', ['ksr', 'poliklinik']);
            $table->string('batch_no', 100);
            $table->date('expiry_date')->nullable();
            $table->integer('quantity')->default(0);
            $table->integer('initial_quantity')->default(0);
            $table->date('received_at');
            $table->string('invoice_no', 100)->nullable();
            $table->string('source')->nullable();
            $table->timestamps();

            $table->index(['item_id', 'warehouse', 'quantity']);
            $table->index(['expiry_date', 'received_at']);
        });

        // 3. Movements (Inbound, Outbound, Opname)
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->enum('warehouse', ['ksr', 'poliklinik']);
            $table->enum('kind', ['inbound', 'outbound', 'opname']);
            $table->integer('quantity'); // Positive for in/out, delta for opname
            $table->string('reference_no', 100)->nullable();
            $table->string('source_destination')->nullable();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->text('notes')->nullable();
            $table->json('allocations')->nullable();
            $table->integer('running_balance')->default(0);
            $table->timestamps();

            $table->index(['item_id', 'warehouse', 'created_at']);
            $table->index(['kind', 'created_at']);
        });

        // 4. Fixed Assets (Aset Tetap)
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('code', 100)->unique();
            $table->string('name');
            $table->enum('warehouse', ['ksr', 'poliklinik']);
            $table->integer('acquisition_year')->default(2024);
            $table->string('funding_source')->default('Kas KSR');
            $table->string('location')->nullable();
            $table->text('description')->nullable();
            $table->enum('status', ['tersedia', 'digunakan', 'rusak'])->default('tersedia');
            $table->timestamps();

            $table->index(['warehouse', 'status']);
        });

        // 5. Asset Logs (Timeline)
        Schema::create('asset_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('action', ['create', 'borrow', 'return', 'damage', 'repair', 'cross_loan']);
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->string('borrower_name')->nullable();
            $table->date('return_estimate')->nullable();
            $table->text('purpose')->nullable();
            $table->text('damage_notes')->nullable();
            $table->timestamps();

            $table->index(['asset_id', 'created_at']);
        });

        // 6. Cross-division Asset Loans
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asset_id')->constrained('assets')->cascadeOnDelete();
            $table->enum('source_warehouse', ['ksr', 'poliklinik']);
            $table->string('target_division');
            $table->string('borrower_name');
            $table->date('return_estimate')->nullable();
            $table->text('purpose');
            $table->enum('status', ['dipinjam', 'selesai'])->default('dipinjam');
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('actor_name');
            $table->timestamp('returned_at')->nullable();
            $table->timestamps();

            $table->index(['source_warehouse', 'status']);
        });

        // 7. Inter-Warehouse Transfers (Mutasi Antar Gudang)
        Schema::create('transfers', function (Blueprint $table) {
            $table->id();
            $table->string('transfer_no', 100)->unique();
            $table->enum('source_warehouse', ['ksr', 'poliklinik']);
            $table->enum('target_warehouse', ['ksr', 'poliklinik']);
            $table->foreignId('item_id')->constrained('items')->cascadeOnDelete();
            $table->integer('quantity');
            $table->enum('status', ['in_transit', 'received', 'cancelled'])->default('in_transit');
            $table->json('allocations')->nullable();
            $table->foreignId('requester_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('requester_name');
            $table->foreignId('receiver_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('receiver_name')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('sent_at')->useCurrent();
            $table->timestamp('received_at')->nullable();
            $table->timestamps();

            $table->index(['source_warehouse', 'target_warehouse', 'status']);
        });

        // 8. Application Settings (Logos, Title, Letterhead)
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->longText('value')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('settings');
        Schema::dropIfExists('transfers');
        Schema::dropIfExists('loans');
        Schema::dropIfExists('asset_logs');
        Schema::dropIfExists('assets');
        Schema::dropIfExists('movements');
        Schema::dropIfExists('batches');
        Schema::dropIfExists('items');
    }
};
