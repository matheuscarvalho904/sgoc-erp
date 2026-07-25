<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory.stock_movements', function (Blueprint $table): void {
            if (! Schema::hasColumn('inventory.stock_movements', 'status')) $table->string('status', 20)->default('draft')->index();
            if (! Schema::hasColumn('inventory.stock_movements', 'posted_at')) $table->timestampTz('posted_at')->nullable()->index();
            if (! Schema::hasColumn('inventory.stock_movements', 'reversed_at')) $table->timestampTz('reversed_at')->nullable();
            if (! Schema::hasColumn('inventory.stock_movements', 'reversal_of_id')) $table->uuid('reversal_of_id')->nullable()->index();
            if (! Schema::hasColumn('inventory.stock_movements', 'transfer_group_id')) $table->uuid('transfer_group_id')->nullable()->index();
            if (! Schema::hasColumn('inventory.stock_movements', 'balance_before')) $table->decimal('balance_before', 18, 4)->nullable();
            if (! Schema::hasColumn('inventory.stock_movements', 'balance_after')) $table->decimal('balance_after', 18, 4)->nullable();
        });

        DB::statement("UPDATE inventory.stock_movements SET status = 'posted', posted_at = COALESCE(posted_at, created_at), balance_before = COALESCE(balance_before, 0), balance_after = COALESCE(balance_after, 0) WHERE status = 'draft'");
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_movements_product_date_idx ON inventory.stock_movements (product_id, occurred_at DESC)');
        DB::statement('CREATE INDEX IF NOT EXISTS inventory_balances_lookup_idx ON inventory.stock_balances (warehouse_id, product_id, location_id, lot_number)');
    }

    public function down(): void
    {
        Schema::table('inventory.stock_movements', function (Blueprint $table): void {
            foreach (['balance_after','balance_before','transfer_group_id','reversal_of_id','reversed_at','posted_at','status'] as $column) {
                if (Schema::hasColumn('inventory.stock_movements', $column)) $table->dropColumn($column);
            }
        });
    }
};
