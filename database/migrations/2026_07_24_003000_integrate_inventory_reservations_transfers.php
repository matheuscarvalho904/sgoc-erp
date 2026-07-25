<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('inventory.reservations', function (Blueprint $t) {
            if (! Schema::hasColumn('inventory.reservations', 'quantity_consumed')) $t->decimal('quantity_consumed', 18, 4)->default(0);
            if (! Schema::hasColumn('inventory.reservations', 'consumption_movement_id')) $t->uuid('consumption_movement_id')->nullable()->index();
        });
        Schema::table('inventory.stock_movements', function (Blueprint $t) {
            if (! Schema::hasColumn('inventory.stock_movements', 'transfer_group_id')) $t->uuid('transfer_group_id')->nullable()->index();
            if (! Schema::hasColumn('inventory.stock_movements', 'paired_movement_id')) $t->uuid('paired_movement_id')->nullable()->index();
        });
        if (Schema::hasTable('maintenance.work_order_materials')) {
            Schema::table('maintenance.work_order_materials', function (Blueprint $t) {
                if (! Schema::hasColumn('maintenance.work_order_materials', 'quantity_shortage')) $t->decimal('quantity_shortage', 14, 4)->default(0);
            });
        }
    }

    public function down(): void
    {
        Schema::table('inventory.reservations', fn (Blueprint $t) => $t->dropColumn(['quantity_consumed','consumption_movement_id']));
        Schema::table('inventory.stock_movements', fn (Blueprint $t) => $t->dropColumn(['transfer_group_id','paired_movement_id']));
        if (Schema::hasTable('maintenance.work_order_materials')) Schema::table('maintenance.work_order_materials', fn (Blueprint $t) => $t->dropColumn('quantity_shortage'));
    }
};
