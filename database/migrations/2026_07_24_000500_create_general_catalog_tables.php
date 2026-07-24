<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
return new class extends Migration {
 public function up(): void { DB::unprepared(<<<'SQL'
CREATE SCHEMA IF NOT EXISTS catalog;
CREATE SCHEMA IF NOT EXISTS commercial;

CREATE TABLE catalog.units (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
 code varchar(20) NOT NULL, name varchar(100) NOT NULL, symbol varchar(20) NOT NULL,
 decimal_places smallint NOT NULL DEFAULT 2, status varchar(20) NOT NULL DEFAULT 'active',
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), CHECK (decimal_places BETWEEN 0 AND 6), CHECK (status IN ('active','inactive'))
);
CREATE TABLE catalog.brands (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
 code varchar(30) NOT NULL, name varchar(120) NOT NULL, manufacturer_name varchar(180), website varchar(180),
 status varchar(20) NOT NULL DEFAULT 'active', created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), UNIQUE (tenant_id, name), CHECK (status IN ('active','inactive'))
);
CREATE TABLE commercial.payment_methods (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
 code varchar(30) NOT NULL, name varchar(100) NOT NULL, type varchar(30) NOT NULL DEFAULT 'other', requires_bank_data boolean NOT NULL DEFAULT false,
 status varchar(20) NOT NULL DEFAULT 'active', created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), CHECK (type IN ('cash','pix','bank_transfer','boleto','credit_card','debit_card','check','other')), CHECK (status IN ('active','inactive'))
);
CREATE TABLE commercial.payment_terms (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
 code varchar(30) NOT NULL, name varchar(120) NOT NULL, installments smallint NOT NULL DEFAULT 1,
 first_due_days integer NOT NULL DEFAULT 0, interval_days integer NOT NULL DEFAULT 0, is_cash boolean NOT NULL DEFAULT false,
 status varchar(20) NOT NULL DEFAULT 'active', created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), CHECK (installments > 0), CHECK (first_due_days >= 0), CHECK (interval_days >= 0), CHECK (status IN ('active','inactive'))
);
CREATE TABLE catalog.product_categories (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id), parent_id uuid REFERENCES catalog.product_categories(id),
 code varchar(30) NOT NULL, name varchar(120) NOT NULL, description text, status varchar(20) NOT NULL DEFAULT 'active',
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), CHECK (status IN ('active','inactive'))
);
CREATE TABLE catalog.products (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id),
 category_id uuid REFERENCES catalog.product_categories(id), unit_id uuid NOT NULL REFERENCES catalog.units(id), brand_id uuid REFERENCES catalog.brands(id),
 code varchar(40) NOT NULL, name varchar(180) NOT NULL, description text, product_type varchar(30) NOT NULL DEFAULT 'material',
 barcode varchar(60), ncm varchar(10), cest varchar(10), sku varchar(60), track_stock boolean NOT NULL DEFAULT true,
 minimum_stock numeric(19,4) NOT NULL DEFAULT 0, maximum_stock numeric(19,4), average_cost numeric(19,4) NOT NULL DEFAULT 0,
 last_purchase_price numeric(19,4) NOT NULL DEFAULT 0, status varchar(20) NOT NULL DEFAULT 'active', settings jsonb NOT NULL DEFAULT '{}'::jsonb,
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), CHECK (product_type IN ('material','service','fuel','part','epi','asset','input')), CHECK (status IN ('active','inactive'))
);
CREATE INDEX products_name_idx ON catalog.products (tenant_id, name);

CREATE TABLE commercial.customers (
 id uuid PRIMARY KEY DEFAULT gen_random_uuid(), tenant_id uuid NOT NULL REFERENCES core.tenants(id), organization_id uuid REFERENCES core.organizations(id),
 code varchar(30) NOT NULL, person_type varchar(10) NOT NULL DEFAULT 'company', document varchar(20), legal_name varchar(200), trade_name varchar(200) NOT NULL,
 state_registration varchar(40), municipal_registration varchar(40), email varchar(180), phone varchar(30), zip_code varchar(12), street varchar(180), number varchar(30),
 complement varchar(120), district varchar(120), city varchar(120), state char(2), payment_term_id uuid REFERENCES commercial.payment_terms(id),
 credit_limit numeric(19,4) NOT NULL DEFAULT 0, notes text, status varchar(20) NOT NULL DEFAULT 'active', settings jsonb NOT NULL DEFAULT '{}'::jsonb,
 created_at timestamptz NOT NULL DEFAULT now(), updated_at timestamptz NOT NULL DEFAULT now(), deleted_at timestamptz,
 UNIQUE (tenant_id, code), CHECK (person_type IN ('company','individual')), CHECK (status IN ('active','inactive','blocked'))
);
CREATE UNIQUE INDEX customers_document_unique ON commercial.customers (tenant_id, document) WHERE document IS NOT NULL AND deleted_at IS NULL;
SQL); }
 public function down(): void { DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS commercial.customers;
DROP TABLE IF EXISTS catalog.products;
DROP TABLE IF EXISTS catalog.product_categories;
DROP TABLE IF EXISTS commercial.payment_terms;
DROP TABLE IF EXISTS commercial.payment_methods;
DROP TABLE IF EXISTS catalog.brands;
DROP TABLE IF EXISTS catalog.units;
SQL); }
};
