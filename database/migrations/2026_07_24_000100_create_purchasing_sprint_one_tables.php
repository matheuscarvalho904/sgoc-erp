<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE SCHEMA IF NOT EXISTS purchasing;

CREATE TABLE purchasing.purchase_categories (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    code varchar(30) NOT NULL,
    name varchar(120) NOT NULL,
    description text,
    status varchar(20) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT purchase_categories_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT purchase_categories_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE TABLE purchasing.suppliers (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid REFERENCES core.organizations(id),
    code varchar(30) NOT NULL,
    person_type varchar(10) NOT NULL DEFAULT 'company',
    document varchar(20),
    legal_name varchar(200),
    trade_name varchar(200) NOT NULL,
    state_registration varchar(40),
    municipal_registration varchar(40),
    email varchar(180),
    phone varchar(30),
    zip_code varchar(12),
    street varchar(180),
    number varchar(30),
    complement varchar(120),
    district varchar(120),
    city varchar(120),
    state char(2),
    payment_notes text,
    status varchar(20) NOT NULL DEFAULT 'active',
    settings jsonb NOT NULL DEFAULT '{}'::jsonb,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT suppliers_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT suppliers_person_type_check CHECK (person_type IN ('company', 'individual')),
    CONSTRAINT suppliers_status_check CHECK (status IN ('active', 'inactive', 'blocked'))
);

CREATE UNIQUE INDEX suppliers_document_unique
    ON purchasing.suppliers (tenant_id, document)
    WHERE document IS NOT NULL AND deleted_at IS NULL;

CREATE INDEX suppliers_trade_name_idx ON purchasing.suppliers (tenant_id, trade_name);

CREATE TABLE purchasing.purchase_requests (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid NOT NULL REFERENCES core.organizations(id),
    company_id uuid NOT NULL REFERENCES core.companies(id),
    branch_id uuid NOT NULL REFERENCES core.branches(id),
    work_id uuid REFERENCES core.works(id),
    cost_center_id uuid REFERENCES core.cost_centers(id),
    requester_id bigint REFERENCES users(id),
    category_id uuid REFERENCES purchasing.purchase_categories(id),
    number varchar(40) NOT NULL,
    requested_at date NOT NULL DEFAULT CURRENT_DATE,
    needed_at date,
    priority varchar(20) NOT NULL DEFAULT 'normal',
    justification text NOT NULL,
    delivery_location varchar(250),
    notes text,
    status varchar(30) NOT NULL DEFAULT 'draft',
    total_estimated numeric(19,4) NOT NULL DEFAULT 0,
    submitted_at timestamptz,
    approved_at timestamptz,
    approved_by bigint REFERENCES users(id),
    rejected_at timestamptz,
    rejected_by bigint REFERENCES users(id),
    rejection_reason text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT purchase_requests_tenant_number_unique UNIQUE (tenant_id, number),
    CONSTRAINT purchase_requests_priority_check CHECK (priority IN ('low', 'normal', 'high', 'urgent')),
    CONSTRAINT purchase_requests_status_check CHECK (status IN ('draft', 'pending_approval', 'approved', 'rejected', 'quoting', 'ordered', 'partially_received', 'received', 'cancelled')),
    CONSTRAINT purchase_requests_dates_check CHECK (needed_at IS NULL OR needed_at >= requested_at)
);

CREATE INDEX purchase_requests_status_idx ON purchasing.purchase_requests (tenant_id, status);
CREATE INDEX purchase_requests_work_idx ON purchasing.purchase_requests (work_id);

CREATE TABLE purchasing.purchase_request_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    purchase_request_id uuid NOT NULL REFERENCES purchasing.purchase_requests(id) ON DELETE CASCADE,
    sequence integer NOT NULL,
    description varchar(250) NOT NULL,
    specification text,
    unit varchar(20) NOT NULL DEFAULT 'UN',
    quantity numeric(19,4) NOT NULL,
    estimated_unit_price numeric(19,4) NOT NULL DEFAULT 0,
    estimated_total numeric(19,4) GENERATED ALWAYS AS (quantity * estimated_unit_price) STORED,
    notes text,
    status varchar(20) NOT NULL DEFAULT 'pending',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT purchase_request_items_sequence_unique UNIQUE (purchase_request_id, sequence),
    CONSTRAINT purchase_request_items_quantity_check CHECK (quantity > 0),
    CONSTRAINT purchase_request_items_price_check CHECK (estimated_unit_price >= 0),
    CONSTRAINT purchase_request_items_status_check CHECK (status IN ('pending', 'approved', 'rejected', 'cancelled'))
);

CREATE INDEX purchase_request_items_request_idx ON purchasing.purchase_request_items (purchase_request_id);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS purchasing.purchase_request_items;
DROP TABLE IF EXISTS purchasing.purchase_requests;
DROP TABLE IF EXISTS purchasing.suppliers;
DROP TABLE IF EXISTS purchasing.purchase_categories;
SQL);
    }
};
