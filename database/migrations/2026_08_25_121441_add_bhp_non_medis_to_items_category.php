<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds 'bhp_non_medis' to the items.category ENUM column.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE items MODIFY COLUMN category ENUM('obat', 'bhp', 'bhp_non_medis') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("ALTER TABLE items MODIFY COLUMN category ENUM('obat', 'bhp') NOT NULL");
    }
};
