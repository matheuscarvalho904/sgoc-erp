<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement(<<<'SQL'
CREATE UNIQUE INDEX IF NOT EXISTS quotation_suppliers_single_winner_idx
ON purchasing.quotation_suppliers (quotation_request_id)
WHERE is_winner = true AND deleted_at IS NULL
SQL);

        DB::statement(<<<'SQL'
CREATE INDEX IF NOT EXISTS quotation_items_supplier_total_idx
ON purchasing.quotation_items (quotation_supplier_id, total_amount)
WHERE deleted_at IS NULL
SQL);
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS purchasing.quotation_items_supplier_total_idx');
        DB::statement('DROP INDEX IF EXISTS purchasing.quotation_suppliers_single_winner_idx');
    }
};
