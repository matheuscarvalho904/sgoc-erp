<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE SCHEMA IF NOT EXISTS assets;

ALTER TABLE commercial.customers
    ADD COLUMN IF NOT EXISTS external_data jsonb NOT NULL DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS external_data_synced_at timestamptz;

ALTER TABLE purchasing.suppliers
    ADD COLUMN IF NOT EXISTS external_data jsonb NOT NULL DEFAULT '{}'::jsonb,
    ADD COLUMN IF NOT EXISTS external_data_synced_at timestamptz;

CREATE TABLE IF NOT EXISTS catalog.product_classes (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    code varchar(30) NOT NULL,
    name varchar(120) NOT NULL,
    description text,
    controls_stock boolean NOT NULL DEFAULT true,
    requires_lot boolean NOT NULL DEFAULT false,
    requires_expiration boolean NOT NULL DEFAULT false,
    requires_asset boolean NOT NULL DEFAULT false,
    allows_purchase boolean NOT NULL DEFAULT true,
    allows_sale boolean NOT NULL DEFAULT false,
    allows_os_consumption boolean NOT NULL DEFAULT true,
    allows_fueling boolean NOT NULL DEFAULT false,
    generates_depreciation boolean NOT NULL DEFAULT false,
    controls_serial_number boolean NOT NULL DEFAULT false,
    status varchar(20) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    UNIQUE (tenant_id, code),
    CHECK (status IN ('active', 'inactive'))
);

ALTER TABLE catalog.products ADD COLUMN IF NOT EXISTS product_class_id uuid REFERENCES catalog.product_classes(id);
CREATE INDEX IF NOT EXISTS products_product_class_idx ON catalog.products(product_class_id);

CREATE TABLE IF NOT EXISTS assets.asset_types (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    code varchar(30) NOT NULL, name varchar(120) NOT NULL, description text,
    status varchar(20) NOT NULL DEFAULT 'active', created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
    UNIQUE (tenant_id, code), CHECK (status IN ('active','inactive'))
);

CREATE TABLE IF NOT EXISTS assets.asset_categories (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    asset_type_id uuid REFERENCES assets.asset_types(id), code varchar(30) NOT NULL, name varchar(120) NOT NULL, description text,
    status varchar(20) NOT NULL DEFAULT 'active', created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
    UNIQUE (tenant_id, code), CHECK (status IN ('active','inactive'))
);

CREATE TABLE IF NOT EXISTS assets.asset_prefixes (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    asset_type_id uuid REFERENCES assets.asset_types(id), code varchar(10) NOT NULL, name varchar(120) NOT NULL,
    next_number integer NOT NULL DEFAULT 1, digits smallint NOT NULL DEFAULT 3, status varchar(20) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
    UNIQUE (tenant_id, code), CHECK (next_number > 0), CHECK (digits BETWEEN 2 AND 6), CHECK (status IN ('active','inactive'))
);

CREATE TABLE IF NOT EXISTS assets.fuels (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    code varchar(30) NOT NULL, name varchar(100) NOT NULL, unit varchar(10) NOT NULL DEFAULT 'L',
    status varchar(20) NOT NULL DEFAULT 'active', created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
    UNIQUE (tenant_id, code), CHECK (status IN ('active','inactive'))
);

CREATE TABLE IF NOT EXISTS assets.assets (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id), organization_id uuid REFERENCES core.organizations(id),
    company_id uuid REFERENCES core.companies(id), branch_id uuid REFERENCES core.branches(id), work_id uuid REFERENCES core.works(id),
    cost_center_id uuid REFERENCES core.cost_centers(id), department_id uuid REFERENCES core.departments(id),
    asset_type_id uuid NOT NULL REFERENCES assets.asset_types(id), asset_category_id uuid REFERENCES assets.asset_categories(id),
    asset_prefix_id uuid REFERENCES assets.asset_prefixes(id), brand_id uuid REFERENCES catalog.brands(id), fuel_id uuid REFERENCES assets.fuels(id),
    code varchar(40) NOT NULL, prefix_number integer, name varchar(180) NOT NULL, model varchar(120),
    manufacture_year smallint, model_year smallint, plate varchar(12), renavam varchar(20), chassis varchar(40), serial_number varchar(80), patrimony_number varchar(40),
    ownership_type varchar(20) NOT NULL DEFAULT 'owned', operational_status varchar(30) NOT NULL DEFAULT 'available',
    meter_type varchar(20) NOT NULL DEFAULT 'none', current_odometer numeric(19,2) NOT NULL DEFAULT 0, current_hourmeter numeric(19,2) NOT NULL DEFAULT 0,
    tank_capacity numeric(19,3), expected_consumption numeric(19,4), acquisition_date date, acquisition_value numeric(19,4) NOT NULL DEFAULT 0,
    residual_value numeric(19,4) NOT NULL DEFAULT 0, useful_life_months integer, warranty_until date, responsible_name varchar(180), location varchar(200),
    notes text, settings jsonb NOT NULL DEFAULT '{}'::jsonb, status varchar(20) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
    UNIQUE (tenant_id, code),
    CHECK (ownership_type IN ('owned','rented','leased','third_party')),
    CHECK (operational_status IN ('operating','maintenance','stopped','rented','available','disposed','sold')),
    CHECK (meter_type IN ('none','odometer','hourmeter','both')),
    CHECK (status IN ('active','inactive'))
);
CREATE INDEX IF NOT EXISTS assets_status_idx ON assets.assets(tenant_id, operational_status);
CREATE INDEX IF NOT EXISTS assets_work_idx ON assets.assets(work_id);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS assets.assets;
DROP TABLE IF EXISTS assets.fuels;
DROP TABLE IF EXISTS assets.asset_prefixes;
DROP TABLE IF EXISTS assets.asset_categories;
DROP TABLE IF EXISTS assets.asset_types;
ALTER TABLE catalog.products DROP COLUMN IF EXISTS product_class_id;
DROP TABLE IF EXISTS catalog.product_classes;
ALTER TABLE purchasing.suppliers DROP COLUMN IF EXISTS external_data_synced_at, DROP COLUMN IF EXISTS external_data;
ALTER TABLE commercial.customers DROP COLUMN IF EXISTS external_data_synced_at, DROP COLUMN IF EXISTS external_data;
SQL);
    }
};
