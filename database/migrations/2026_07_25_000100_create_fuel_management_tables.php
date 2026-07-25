<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS fuel');

        Schema::create('fuel.storages', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->nullable()->index();
            $t->uuid('organization_id')->nullable()->index();
            $t->uuid('company_id')->nullable()->index();
            $t->uuid('branch_id')->nullable()->index();
            $t->uuid('work_id')->nullable()->index();
            $t->uuid('cost_center_id')->nullable()->index();
            $t->string('code', 40);
            $t->string('name', 160);
            $t->string('storage_type', 30)->default('tank');
            $t->uuid('asset_id')->nullable()->index();
            $t->decimal('capacity_liters', 18, 4)->default(0);
            $t->decimal('minimum_level_liters', 18, 4)->default(0);
            $t->string('location', 220)->nullable();
            $t->string('responsible_name', 140)->nullable();
            $t->string('status', 20)->default('active');
            $t->jsonb('settings')->nullable();
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->unique(['tenant_id', 'code']);
        });

        Schema::create('fuel.pumps', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('storage_id')->index();
            $t->string('code', 40);
            $t->string('name', 120);
            $t->string('serial_number', 100)->nullable();
            $t->decimal('current_meter', 18, 3)->default(0);
            $t->string('status', 20)->default('active');
            $t->timestampsTz();
            $t->softDeletesTz();
            $t->unique(['storage_id', 'code']);
        });

        Schema::create('fuel.entries', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->nullable()->index();
            $t->uuid('company_id')->nullable()->index();
            $t->uuid('branch_id')->nullable()->index();
            $t->uuid('work_id')->nullable()->index();
            $t->uuid('cost_center_id')->nullable()->index();
            $t->uuid('storage_id')->index();
            $t->uuid('fuel_id')->index();
            $t->uuid('supplier_id')->nullable()->index();
            $t->string('number', 50);
            $t->string('invoice_number', 60)->nullable();
            $t->date('invoice_date')->nullable();
            $t->timestampTz('received_at');
            $t->decimal('quantity_liters', 18, 4);
            $t->decimal('unit_cost', 18, 6)->default(0);
            $t->decimal('total_cost', 18, 4)->default(0);
            $t->string('status', 30)->default('posted');
            $t->uuid('received_by')->nullable()->index();
            $t->text('notes')->nullable();
            $t->jsonb('metadata')->nullable();
            $t->timestampsTz();
            $t->unique(['tenant_id', 'number']);
        });

        Schema::create('fuel.fuelings', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->nullable()->index();
            $t->uuid('company_id')->nullable()->index();
            $t->uuid('branch_id')->nullable()->index();
            $t->uuid('work_id')->nullable()->index();
            $t->uuid('cost_center_id')->nullable()->index();
            $t->uuid('storage_id')->index();
            $t->uuid('pump_id')->nullable()->index();
            $t->uuid('fuel_id')->index();
            $t->uuid('asset_id')->index();
            $t->string('number', 50);
            $t->timestampTz('fueled_at');
            $t->decimal('quantity_liters', 18, 4);
            $t->decimal('unit_cost', 18, 6)->default(0);
            $t->decimal('total_cost', 18, 4)->default(0);
            $t->string('meter_type', 20)->default('none');
            $t->decimal('meter_reading', 18, 2)->nullable();
            $t->decimal('previous_meter_reading', 18, 2)->nullable();
            $t->decimal('distance_or_hours', 18, 2)->nullable();
            $t->decimal('calculated_consumption', 18, 4)->nullable();
            $t->string('operator_name', 140)->nullable();
            $t->uuid('performed_by')->nullable()->index();
            $t->string('status', 30)->default('posted');
            $t->text('notes')->nullable();
            $t->jsonb('metadata')->nullable();
            $t->timestampsTz();
            $t->unique(['tenant_id', 'number']);
        });

        Schema::create('fuel.stock_balances', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->nullable()->index();
            $t->uuid('storage_id')->index();
            $t->uuid('fuel_id')->index();
            $t->decimal('quantity_liters', 18, 4)->default(0);
            $t->decimal('average_cost', 18, 6)->default(0);
            $t->decimal('total_value', 18, 4)->default(0);
            $t->timestampTz('last_movement_at')->nullable();
            $t->timestampsTz();
            $t->unique(['storage_id', 'fuel_id']);
        });

        Schema::create('fuel.stock_movements', function (Blueprint $t) {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->nullable()->index();
            $t->uuid('storage_id')->index();
            $t->uuid('fuel_id')->index();
            $t->string('number', 50);
            $t->string('movement_type', 20);
            $t->decimal('quantity_liters', 18, 4);
            $t->decimal('unit_cost', 18, 6)->default(0);
            $t->decimal('total_cost', 18, 4)->default(0);
            $t->nullableUuidMorphs('source');
            $t->uuid('performed_by')->nullable()->index();
            $t->timestampTz('occurred_at');
            $t->text('notes')->nullable();
            $t->jsonb('metadata')->nullable();
            $t->timestampsTz();
            $t->unique(['tenant_id', 'number']);
        });
    }

    public function down(): void
    {
        foreach (['stock_movements', 'stock_balances', 'fuelings', 'entries', 'pumps', 'storages'] as $table) {
            Schema::dropIfExists('fuel.' . $table);
        }
    }
};
