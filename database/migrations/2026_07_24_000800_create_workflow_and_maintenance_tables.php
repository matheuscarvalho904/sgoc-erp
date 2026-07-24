<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS workflow');
        DB::statement('CREATE SCHEMA IF NOT EXISTS maintenance');

        if (! Schema::hasTable('workflow.definitions')) Schema::create('workflow.definitions', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('code',80); $t->string('name',160); $t->string('entity_type',160); $t->boolean('is_active')->default(true); $t->jsonb('settings')->nullable(); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['tenant_id','code']);
        });
        if (! Schema::hasTable('workflow.steps')) Schema::create('workflow.steps', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('definition_id'); $t->unsignedSmallInteger('sequence'); $t->string('name',160); $t->string('approver_type',40)->default('role'); $t->uuid('approver_id')->nullable(); $t->decimal('minimum_amount',18,4)->nullable(); $t->decimal('maximum_amount',18,4)->nullable(); $t->boolean('is_required')->default(true); $t->jsonb('conditions')->nullable(); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['definition_id','sequence']);
        });
        if (! Schema::hasTable('workflow.instances')) Schema::create('workflow.instances', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('definition_id'); $t->uuid('tenant_id')->nullable()->index(); $t->uuidMorphs('subject'); $t->string('status',40)->default('pending'); $t->unsignedSmallInteger('current_step')->default(1); $t->timestampTz('started_at')->nullable(); $t->timestampTz('completed_at')->nullable(); $t->timestampsTz(); $t->softDeletesTz();
        });
        if (! Schema::hasTable('workflow.decisions')) Schema::create('workflow.decisions', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('instance_id'); $t->uuid('step_id')->nullable(); $t->uuid('actor_id')->nullable(); $t->string('decision',30); $t->text('comments')->nullable(); $t->timestampTz('decided_at'); $t->jsonb('metadata')->nullable(); $t->timestampsTz();
        });

        if (! Schema::hasTable('maintenance.maintenance_types')) Schema::create('maintenance.maintenance_types', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('code',40); $t->string('name',120); $t->boolean('is_preventive')->default(false); $t->boolean('requires_approval')->default(false); $t->string('status',20)->default('active'); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['tenant_id','code']);
        });
        if (! Schema::hasTable('maintenance.priorities')) Schema::create('maintenance.priorities', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('code',40); $t->string('name',100); $t->unsignedSmallInteger('level')->default(3); $t->unsignedInteger('sla_hours')->nullable(); $t->string('color',30)->default('gray'); $t->string('status',20)->default('active'); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['tenant_id','code']);
        });
        if (! Schema::hasTable('maintenance.workshops')) Schema::create('maintenance.workshops', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->uuid('company_id')->nullable()->index(); $t->uuid('branch_id')->nullable()->index(); $t->uuid('supplier_id')->nullable()->index(); $t->string('code',40); $t->string('name',160); $t->string('type',30)->default('internal'); $t->string('phone',30)->nullable(); $t->string('email')->nullable(); $t->string('contact_name',120)->nullable(); $t->text('notes')->nullable(); $t->string('status',20)->default('active'); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['tenant_id','code']);
        });
        if (! Schema::hasTable('maintenance.plans')) Schema::create('maintenance.plans', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->string('code',50); $t->string('name',180); $t->uuid('asset_type_id')->nullable()->index(); $t->uuid('asset_id')->nullable()->index(); $t->uuid('maintenance_type_id')->nullable()->index(); $t->string('trigger_type',30)->default('hourmeter'); $t->decimal('interval_value',14,2); $t->decimal('advance_value',14,2)->default(0); $t->date('next_due_date')->nullable(); $t->decimal('next_due_meter',14,2)->nullable(); $t->boolean('auto_create_work_order')->default(true); $t->text('instructions')->nullable(); $t->string('status',20)->default('active'); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['tenant_id','code']);
        });
        if (! Schema::hasTable('maintenance.work_orders')) Schema::create('maintenance.work_orders', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('tenant_id')->nullable()->index(); $t->uuid('organization_id')->nullable()->index(); $t->uuid('company_id')->nullable()->index(); $t->uuid('branch_id')->nullable()->index(); $t->uuid('work_id')->nullable()->index(); $t->uuid('cost_center_id')->nullable()->index(); $t->uuid('asset_id')->index(); $t->uuid('maintenance_type_id')->nullable()->index(); $t->uuid('priority_id')->nullable()->index(); $t->uuid('workshop_id')->nullable()->index(); $t->uuid('requester_id')->nullable()->index(); $t->uuid('responsible_id')->nullable()->index(); $t->string('number',50); $t->string('status',40)->default('open'); $t->string('source',30)->default('manual'); $t->timestampTz('opened_at'); $t->timestampTz('scheduled_at')->nullable(); $t->timestampTz('started_at')->nullable(); $t->timestampTz('completed_at')->nullable(); $t->decimal('entry_hourmeter',14,2)->nullable(); $t->decimal('exit_hourmeter',14,2)->nullable(); $t->decimal('entry_odometer',14,2)->nullable(); $t->decimal('exit_odometer',14,2)->nullable(); $t->text('symptom'); $t->text('diagnosis')->nullable(); $t->text('cause')->nullable(); $t->text('solution')->nullable(); $t->decimal('estimated_cost',18,4)->default(0); $t->decimal('actual_cost',18,4)->default(0); $t->text('notes')->nullable(); $t->jsonb('metadata')->nullable(); $t->timestampsTz(); $t->softDeletesTz(); $t->unique(['tenant_id','number']);
        });
        if (! Schema::hasTable('maintenance.work_order_services')) Schema::create('maintenance.work_order_services', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('work_order_id')->index(); $t->uuid('technician_id')->nullable()->index(); $t->string('description',220); $t->decimal('estimated_hours',10,2)->default(0); $t->decimal('actual_hours',10,2)->default(0); $t->decimal('hourly_rate',18,4)->default(0); $t->decimal('total_cost',18,4)->default(0); $t->string('status',30)->default('pending'); $t->text('notes')->nullable(); $t->timestampsTz(); $t->softDeletesTz();
        });
        if (! Schema::hasTable('maintenance.work_order_materials')) Schema::create('maintenance.work_order_materials', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('work_order_id')->index(); $t->uuid('product_id')->nullable()->index(); $t->uuid('unit_id')->nullable()->index(); $t->string('description',220); $t->decimal('quantity_requested',14,4)->default(0); $t->decimal('quantity_applied',14,4)->default(0); $t->decimal('unit_cost',18,4)->default(0); $t->decimal('total_cost',18,4)->default(0); $t->uuid('purchase_request_id')->nullable()->index(); $t->string('status',30)->default('requested'); $t->timestampsTz(); $t->softDeletesTz();
        });
        if (! Schema::hasTable('maintenance.work_order_events')) Schema::create('maintenance.work_order_events', function (Blueprint $t) {
            $t->uuid('id')->primary(); $t->uuid('work_order_id')->index(); $t->uuid('user_id')->nullable()->index(); $t->string('event_type',50); $t->string('from_status',40)->nullable(); $t->string('to_status',40)->nullable(); $t->text('description')->nullable(); $t->jsonb('data')->nullable(); $t->timestampTz('occurred_at'); $t->timestampsTz();
        });
    }

    public function down(): void
    {
        foreach (['work_order_events','work_order_materials','work_order_services','work_orders','plans','workshops','priorities','maintenance_types'] as $table) Schema::dropIfExists('maintenance.'.$table);
        foreach (['decisions','instances','steps','definitions'] as $table) Schema::dropIfExists('workflow.'.$table);
    }
};
