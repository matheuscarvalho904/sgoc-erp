<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS inventory');

        if (! Schema::hasTable('inventory.warehouses')) {
            Schema::create('inventory.warehouses', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->nullable()->index();
                $t->uuid('organization_id')->nullable()->index();
                $t->uuid('company_id')->nullable()->index();
                $t->uuid('branch_id')->nullable()->index();
                $t->uuid('work_id')->nullable()->index();
                $t->uuid('cost_center_id')->nullable()->index();
                $t->string('code', 40);
                $t->string('name', 160);
                $t->string('type', 40)->default('general');
                $t->string('responsible_name', 140)->nullable();
                $t->string('phone', 30)->nullable();
                $t->string('location', 220)->nullable();
                $t->boolean('allows_negative_stock')->default(false);
                $t->string('status', 20)->default('active');
                $t->jsonb('settings')->nullable();
                $t->timestampsTz();
                $t->softDeletesTz();
                $t->unique(['tenant_id', 'code']);
            });
        }

        if (! Schema::hasTable('inventory.locations')) {
            Schema::create('inventory.locations', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('warehouse_id')->index();
                $t->string('code', 60);
                $t->string('name', 160);
                $t->string('aisle', 40)->nullable();
                $t->string('rack', 40)->nullable();
                $t->string('level', 40)->nullable();
                $t->string('position', 40)->nullable();
                $t->string('status', 20)->default('active');
                $t->timestampsTz();
                $t->softDeletesTz();
                $t->unique(['warehouse_id', 'code']);
            });
        }

        if (! Schema::hasTable('inventory.stock_balances')) {
            Schema::create('inventory.stock_balances', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->nullable()->index();
                $t->uuid('warehouse_id')->index();
                $t->uuid('location_id')->nullable()->index();
                $t->uuid('product_id')->index();
                $t->uuid('unit_id')->nullable()->index();
                $t->string('lot_number', 100)->nullable();
                $t->date('expires_at')->nullable();
                $t->decimal('quantity_on_hand', 18, 4)->default(0);
                $t->decimal('quantity_reserved', 18, 4)->default(0);
                $t->decimal('average_cost', 18, 4)->default(0);
                $t->decimal('minimum_stock', 18, 4)->default(0);
                $t->decimal('maximum_stock', 18, 4)->nullable();
                $t->timestampsTz();
                $t->unique(['warehouse_id', 'location_id', 'product_id', 'lot_number'], 'inventory_balance_unique');
            });
        }

        if (! Schema::hasTable('inventory.stock_movements')) {
            Schema::create('inventory.stock_movements', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->nullable()->index();
                $t->uuid('warehouse_id')->index();
                $t->uuid('location_id')->nullable()->index();
                $t->uuid('product_id')->index();
                $t->uuid('unit_id')->nullable()->index();
                $t->uuid('stock_request_id')->nullable()->index();
                $t->string('number', 50);
                $t->string('movement_type', 30);
                $t->string('reason', 60)->nullable();
                $t->decimal('quantity', 18, 4);
                $t->decimal('unit_cost', 18, 4)->default(0);
                $t->decimal('total_cost', 18, 4)->default(0);
                $t->string('lot_number', 100)->nullable();
                $t->date('expires_at')->nullable();
                $t->nullableUuidMorphs('source');
                $t->uuid('performed_by')->nullable()->index();
                $t->timestampTz('occurred_at');
                $t->text('notes')->nullable();
                $t->jsonb('metadata')->nullable();
                $t->timestampsTz();
                $t->unique(['tenant_id', 'number']);
            });
        }

        if (! Schema::hasTable('inventory.stock_requests')) {
            Schema::create('inventory.stock_requests', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->nullable()->index();
                $t->uuid('organization_id')->nullable()->index();
                $t->uuid('company_id')->nullable()->index();
                $t->uuid('branch_id')->nullable()->index();
                $t->uuid('work_id')->nullable()->index();
                $t->uuid('cost_center_id')->nullable()->index();
                $t->uuid('warehouse_id')->index();
                $t->uuid('requester_id')->nullable()->index();
                $t->string('number', 50);
                $t->string('status', 30)->default('draft');
                $t->string('purpose', 40)->default('consumption');
                $t->nullableUuidMorphs('destination');
                $t->timestampTz('requested_at');
                $t->timestampTz('approved_at')->nullable();
                $t->timestampTz('fulfilled_at')->nullable();
                $t->text('notes')->nullable();
                $t->timestampsTz();
                $t->softDeletesTz();
                $t->unique(['tenant_id', 'number']);
            });
        }

        if (! Schema::hasTable('inventory.stock_request_items')) {
            Schema::create('inventory.stock_request_items', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('stock_request_id')->index();
                $t->uuid('product_id')->index();
                $t->uuid('unit_id')->nullable()->index();
                $t->decimal('quantity_requested', 18, 4);
                $t->decimal('quantity_reserved', 18, 4)->default(0);
                $t->decimal('quantity_fulfilled', 18, 4)->default(0);
                $t->decimal('unit_cost', 18, 4)->default(0);
                $t->string('status', 30)->default('pending');
                $t->uuid('purchase_request_id')->nullable()->index();
                $t->text('notes')->nullable();
                $t->timestampsTz();
                $t->softDeletesTz();
            });
        }

        if (! Schema::hasTable('inventory.reservations')) {
            Schema::create('inventory.reservations', function (Blueprint $t) {
                $t->uuid('id')->primary();
                $t->uuid('tenant_id')->nullable()->index();
                $t->uuid('warehouse_id')->index();
                $t->uuid('stock_balance_id')->nullable()->index();
                $t->uuid('product_id')->index();
                $t->uuid('stock_request_item_id')->nullable()->index();
                $t->nullableUuidMorphs('reservable');
                $t->decimal('quantity', 18, 4);
                $t->string('status', 30)->default('active');
                $t->timestampTz('reserved_at');
                $t->timestampTz('released_at')->nullable();
                $t->timestampTz('consumed_at')->nullable();
                $t->timestampsTz();
            });
        }

        if (Schema::hasTable('maintenance.work_order_materials')) {
            Schema::table('maintenance.work_order_materials', function (Blueprint $t) {
                if (! Schema::hasColumn('maintenance.work_order_materials', 'warehouse_id')) $t->uuid('warehouse_id')->nullable()->index();
                if (! Schema::hasColumn('maintenance.work_order_materials', 'stock_request_id')) $t->uuid('stock_request_id')->nullable()->index();
                if (! Schema::hasColumn('maintenance.work_order_materials', 'reservation_id')) $t->uuid('reservation_id')->nullable()->index();
                if (! Schema::hasColumn('maintenance.work_order_materials', 'quantity_reserved')) $t->decimal('quantity_reserved', 14, 4)->default(0);
            });
        }
    }

    public function down(): void
    {
        foreach (['reservations','stock_request_items','stock_requests','stock_movements','stock_balances','locations','warehouses'] as $table) {
            Schema::dropIfExists('inventory.' . $table);
        }
    }
};
