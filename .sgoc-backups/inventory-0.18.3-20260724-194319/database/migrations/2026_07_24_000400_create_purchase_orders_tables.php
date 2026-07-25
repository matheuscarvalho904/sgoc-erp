<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE purchasing.purchase_orders (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid NOT NULL REFERENCES core.organizations(id),
    company_id uuid NOT NULL REFERENCES core.companies(id),
    branch_id uuid NOT NULL REFERENCES core.branches(id),
    work_id uuid REFERENCES core.works(id),
    cost_center_id uuid REFERENCES core.cost_centers(id),
    purchase_request_id uuid NOT NULL REFERENCES purchasing.purchase_requests(id),
    quotation_request_id uuid REFERENCES purchasing.quotation_requests(id),
    quotation_supplier_id uuid REFERENCES purchasing.quotation_suppliers(id),
    supplier_id uuid NOT NULL REFERENCES purchasing.suppliers(id),
    number varchar(40) NOT NULL,
    ordered_at date NOT NULL DEFAULT CURRENT_DATE,
    expected_at date,
    status varchar(30) NOT NULL DEFAULT 'draft',
    payment_terms varchar(180),
    delivery_location varchar(250),
    notes text,
    subtotal numeric(19,4) NOT NULL DEFAULT 0,
    freight_amount numeric(19,4) NOT NULL DEFAULT 0,
    discount_amount numeric(19,4) NOT NULL DEFAULT 0,
    other_amount numeric(19,4) NOT NULL DEFAULT 0,
    total_amount numeric(19,4) NOT NULL DEFAULT 0,
    created_by bigint REFERENCES users(id),
    approved_by bigint REFERENCES users(id),
    approved_at timestamptz,
    issued_at timestamptz,
    cancelled_at timestamptz,
    cancellation_reason text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT purchase_orders_tenant_number_unique UNIQUE (tenant_id, number),
    CONSTRAINT purchase_orders_quotation_supplier_unique UNIQUE (quotation_supplier_id),
    CONSTRAINT purchase_orders_status_check CHECK (status IN ('draft','approved','issued','partially_received','received','cancelled')),
    CONSTRAINT purchase_orders_values_check CHECK (subtotal >= 0 AND freight_amount >= 0 AND discount_amount >= 0 AND other_amount >= 0 AND total_amount >= 0),
    CONSTRAINT purchase_orders_dates_check CHECK (expected_at IS NULL OR expected_at >= ordered_at)
);
CREATE INDEX purchase_orders_status_idx ON purchasing.purchase_orders (tenant_id, status);
CREATE INDEX purchase_orders_supplier_idx ON purchasing.purchase_orders (supplier_id, ordered_at);
CREATE INDEX purchase_orders_work_idx ON purchasing.purchase_orders (work_id, ordered_at);

CREATE TABLE purchasing.purchase_order_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    purchase_order_id uuid NOT NULL REFERENCES purchasing.purchase_orders(id) ON DELETE CASCADE,
    purchase_request_item_id uuid REFERENCES purchasing.purchase_request_items(id),
    quotation_item_id uuid REFERENCES purchasing.quotation_items(id),
    sequence integer NOT NULL,
    description varchar(250) NOT NULL,
    specification text,
    unit varchar(20) NOT NULL DEFAULT 'UN',
    quantity numeric(19,4) NOT NULL,
    unit_price numeric(19,4) NOT NULL DEFAULT 0,
    discount_amount numeric(19,4) NOT NULL DEFAULT 0,
    tax_amount numeric(19,4) NOT NULL DEFAULT 0,
    total_amount numeric(19,4) GENERATED ALWAYS AS ((quantity * unit_price) - discount_amount + tax_amount) STORED,
    quantity_received numeric(19,4) NOT NULL DEFAULT 0,
    status varchar(25) NOT NULL DEFAULT 'pending',
    notes text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT purchase_order_items_sequence_unique UNIQUE (purchase_order_id, sequence),
    CONSTRAINT purchase_order_items_quantity_check CHECK (quantity > 0 AND quantity_received >= 0 AND quantity_received <= quantity),
    CONSTRAINT purchase_order_items_values_check CHECK (unit_price >= 0 AND discount_amount >= 0 AND tax_amount >= 0),
    CONSTRAINT purchase_order_items_status_check CHECK (status IN ('pending','partially_received','received','cancelled'))
);
CREATE INDEX purchase_order_items_order_idx ON purchasing.purchase_order_items (purchase_order_id);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS purchasing.purchase_order_items;
DROP TABLE IF EXISTS purchasing.purchase_orders;
SQL);
    }
};
