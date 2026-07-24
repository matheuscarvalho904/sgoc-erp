<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        DB::statement('CREATE SCHEMA IF NOT EXISTS documents');
        DB::statement('CREATE SCHEMA IF NOT EXISTS maintenance');

        if (! Schema::hasTable('documents.documents')) {
            Schema::create('documents.documents', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->uuid('organization_id')->nullable()->index();
                $table->uuid('company_id')->nullable()->index();
                $table->string('document_type', 80)->index();
                $table->string('number', 60);
                $table->string('title', 220);
                $table->uuidMorphs('subject');
                $table->string('status', 40)->default('draft')->index();
                $table->uuid('created_by')->nullable()->index();
                $table->timestampTz('issued_at')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestampsTz();
                $table->softDeletesTz();
                $table->unique(['tenant_id', 'document_type', 'number']);
            });
        }

        if (! Schema::hasTable('documents.attachments')) {
            Schema::create('documents.attachments', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('document_id')->nullable()->index();
                $table->uuidMorphs('attachable');
                $table->string('category', 60)->default('other');
                $table->string('original_name', 255);
                $table->string('disk', 40)->default('public');
                $table->string('path', 500);
                $table->string('mime_type', 120)->nullable();
                $table->unsignedBigInteger('size_bytes')->nullable();
                $table->uuid('uploaded_by')->nullable()->index();
                $table->text('notes')->nullable();
                $table->timestampsTz();
                $table->softDeletesTz();
            });
        }

        if (! Schema::hasTable('maintenance.requests')) {
            Schema::create('maintenance.requests', function (Blueprint $table): void {
                $table->uuid('id')->primary();
                $table->uuid('tenant_id')->nullable()->index();
                $table->uuid('organization_id')->nullable()->index();
                $table->uuid('company_id')->nullable()->index();
                $table->uuid('branch_id')->nullable()->index();
                $table->uuid('work_id')->nullable()->index();
                $table->uuid('cost_center_id')->nullable()->index();
                $table->uuid('asset_id')->index();
                $table->uuid('priority_id')->nullable()->index();
                $table->uuid('requester_id')->nullable()->index();
                $table->uuid('reviewer_id')->nullable()->index();
                $table->uuid('work_order_id')->nullable()->index();
                $table->string('number', 50);
                $table->string('status', 40)->default('new')->index();
                $table->timestampTz('requested_at');
                $table->timestampTz('reviewed_at')->nullable();
                $table->decimal('hourmeter', 14, 2)->nullable();
                $table->decimal('odometer', 14, 2)->nullable();
                $table->text('symptom');
                $table->text('location_details')->nullable();
                $table->text('review_notes')->nullable();
                $table->jsonb('metadata')->nullable();
                $table->timestampsTz();
                $table->softDeletesTz();
                $table->unique(['tenant_id', 'number']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance.requests');
        Schema::dropIfExists('documents.attachments');
        Schema::dropIfExists('documents.documents');
    }
};
