<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE IF NOT EXISTS purchasing.application_types (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    code varchar(20) NOT NULL,
    name varchar(120) NOT NULL,
    description text,
    target_kind varchar(40) NOT NULL DEFAULT 'manual',
    requires_target boolean NOT NULL DEFAULT true,
    allows_allocation boolean NOT NULL DEFAULT true,
    measurement_effect varchar(20) NOT NULL DEFAULT 'none',
    status varchar(20) NOT NULL DEFAULT 'active',
    sort_order integer NOT NULL DEFAULT 0,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT application_types_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT application_types_status_check CHECK (status IN ('active', 'inactive')),
    CONSTRAINT application_types_measurement_check CHECK (measurement_effect IN ('none', 'deduct', 'add', 'deduct_add'))
);

ALTER TABLE purchasing.purchase_request_items
    ADD COLUMN IF NOT EXISTS item_type varchar(20) NOT NULL DEFAULT 'product',
    ADD COLUMN IF NOT EXISTS product_id uuid NULL,
    ADD COLUMN IF NOT EXISTS unit_id uuid NULL,
    ADD COLUMN IF NOT EXISTS service_description varchar(500) NULL,
    ADD COLUMN IF NOT EXISTS application_type_id uuid NULL,
    ADD COLUMN IF NOT EXISTS application_target_id uuid NULL,
    ADD COLUMN IF NOT EXISTS application_label varchar(250) NULL,
    ADD COLUMN IF NOT EXISTS application_data jsonb NOT NULL DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS allocation_percentage numeric(7,4) NOT NULL DEFAULT 100;

DO $$ BEGIN
    ALTER TABLE purchasing.purchase_request_items
        ADD CONSTRAINT purchase_request_items_product_fk
        FOREIGN KEY (product_id) REFERENCES catalog.products(id);
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    ALTER TABLE purchasing.purchase_request_items
        ADD CONSTRAINT purchase_request_items_unit_fk
        FOREIGN KEY (unit_id) REFERENCES catalog.units(id);
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    ALTER TABLE purchasing.purchase_request_items
        ADD CONSTRAINT purchase_request_items_application_type_fk
        FOREIGN KEY (application_type_id) REFERENCES purchasing.application_types(id);
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    ALTER TABLE purchasing.purchase_request_items
        ADD CONSTRAINT purchase_request_items_item_type_check
        CHECK (item_type IN ('product', 'service'));
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

DO $$ BEGIN
    ALTER TABLE purchasing.purchase_request_items
        ADD CONSTRAINT purchase_request_items_allocation_check
        CHECK (allocation_percentage > 0 AND allocation_percentage <= 100);
EXCEPTION WHEN duplicate_object THEN NULL; END $$;

CREATE INDEX IF NOT EXISTS purchase_request_items_product_idx ON purchasing.purchase_request_items(product_id);
CREATE INDEX IF NOT EXISTS purchase_request_items_application_idx ON purchasing.purchase_request_items(application_type_id, application_target_id);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
ALTER TABLE purchasing.purchase_request_items
    DROP CONSTRAINT IF EXISTS purchase_request_items_product_fk,
    DROP CONSTRAINT IF EXISTS purchase_request_items_unit_fk,
    DROP CONSTRAINT IF EXISTS purchase_request_items_application_type_fk,
    DROP CONSTRAINT IF EXISTS purchase_request_items_item_type_check,
    DROP CONSTRAINT IF EXISTS purchase_request_items_allocation_check,
    DROP COLUMN IF EXISTS allocation_percentage,
    DROP COLUMN IF EXISTS application_data,
    DROP COLUMN IF EXISTS application_label,
    DROP COLUMN IF EXISTS application_target_id,
    DROP COLUMN IF EXISTS application_type_id,
    DROP COLUMN IF EXISTS service_description,
    DROP COLUMN IF EXISTS unit_id,
    DROP COLUMN IF EXISTS product_id,
    DROP COLUMN IF EXISTS item_type;
DROP TABLE IF EXISTS purchasing.application_types;
SQL);
    }
};
