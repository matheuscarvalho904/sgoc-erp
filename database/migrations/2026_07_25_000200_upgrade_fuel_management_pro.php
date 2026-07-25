<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('fuel.storages', function (Blueprint $t): void {
            $t->uuid('default_fuel_id')->nullable()->index();
            $t->string('manufacturer', 120)->nullable();
            $t->string('serial_number', 100)->nullable();
            $t->decimal('latitude', 10, 7)->nullable();
            $t->decimal('longitude', 10, 7)->nullable();
        });
        Schema::table('fuel.pumps', function (Blueprint $t): void {
            $t->string('manufacturer', 120)->nullable();
            $t->decimal('flow_rate_lpm', 12, 3)->nullable();
        });
        Schema::create('fuel.alerts', function (Blueprint $t): void {
            $t->uuid('id')->primary();
            $t->uuid('tenant_id')->nullable()->index();
            $t->uuid('storage_id')->nullable()->index();
            $t->uuid('fueling_id')->nullable()->index();
            $t->uuid('asset_id')->nullable()->index();
            $t->string('alert_type', 50)->index();
            $t->string('severity', 20)->default('warning')->index();
            $t->string('title', 160);
            $t->text('message');
            $t->string('status', 20)->default('open')->index();
            $t->timestampTz('detected_at');
            $t->timestampTz('resolved_at')->nullable();
            $t->uuid('resolved_by')->nullable()->index();
            $t->jsonb('metadata')->nullable();
            $t->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel.alerts');
        Schema::table('fuel.pumps', fn (Blueprint $t) => $t->dropColumn(['manufacturer','flow_rate_lpm']));
        Schema::table('fuel.storages', fn (Blueprint $t) => $t->dropColumn(['default_fuel_id','manufacturer','serial_number','latitude','longitude']));
    }
};
