<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE purchasing.approval_rules (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    company_id uuid REFERENCES core.companies(id),
    name varchar(120) NOT NULL,
    min_amount numeric(19,4) NOT NULL DEFAULT 0,
    max_amount numeric(19,4),
    approver_role_id uuid REFERENCES access_control.roles(id),
    approver_user_id bigint REFERENCES users(id),
    approval_order integer NOT NULL DEFAULT 1,
    status varchar(20) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT approval_rules_amount_check CHECK (min_amount >= 0 AND (max_amount IS NULL OR max_amount >= min_amount)),
    CONSTRAINT approval_rules_status_check CHECK (status IN ('active','inactive')),
    CONSTRAINT approval_rules_approver_check CHECK (approver_role_id IS NOT NULL OR approver_user_id IS NOT NULL)
);
CREATE INDEX approval_rules_lookup_idx ON purchasing.approval_rules (tenant_id, company_id, status, min_amount, max_amount);

CREATE TABLE purchasing.purchase_approvals (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    purchase_request_id uuid NOT NULL REFERENCES purchasing.purchase_requests(id) ON DELETE CASCADE,
    approval_rule_id uuid REFERENCES purchasing.approval_rules(id),
    approval_order integer NOT NULL DEFAULT 1,
    approver_user_id bigint REFERENCES users(id),
    status varchar(20) NOT NULL DEFAULT 'pending',
    requested_at timestamptz NOT NULL DEFAULT now(),
    decided_at timestamptz,
    decision_by bigint REFERENCES users(id),
    comments text,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT purchase_approvals_status_check CHECK (status IN ('pending','approved','rejected','skipped','cancelled')),
    CONSTRAINT purchase_approvals_request_order_unique UNIQUE (purchase_request_id, approval_order)
);
CREATE INDEX purchase_approvals_pending_idx ON purchasing.purchase_approvals (tenant_id, status, approver_user_id);

CREATE TABLE purchasing.quotation_requests (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    purchase_request_id uuid NOT NULL REFERENCES purchasing.purchase_requests(id),
    number varchar(40) NOT NULL,
    issued_at date NOT NULL DEFAULT CURRENT_DATE,
    response_deadline date,
    status varchar(25) NOT NULL DEFAULT 'draft',
    notes text,
    created_by bigint REFERENCES users(id),
    closed_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT quotation_requests_tenant_number_unique UNIQUE (tenant_id, number),
    CONSTRAINT quotation_requests_status_check CHECK (status IN ('draft','sent','partially_answered','answered','under_analysis','closed','cancelled')),
    CONSTRAINT quotation_requests_deadline_check CHECK (response_deadline IS NULL OR response_deadline >= issued_at)
);

CREATE TABLE purchasing.quotation_suppliers (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    quotation_request_id uuid NOT NULL REFERENCES purchasing.quotation_requests(id) ON DELETE CASCADE,
    supplier_id uuid NOT NULL REFERENCES purchasing.suppliers(id),
    status varchar(25) NOT NULL DEFAULT 'invited',
    proposal_number varchar(80),
    proposal_date date,
    validity_date date,
    delivery_days integer,
    freight_amount numeric(19,4) NOT NULL DEFAULT 0,
    discount_amount numeric(19,4) NOT NULL DEFAULT 0,
    other_amount numeric(19,4) NOT NULL DEFAULT 0,
    payment_terms varchar(180),
    notes text,
    attachment_path varchar(500),
    subtotal numeric(19,4) NOT NULL DEFAULT 0,
    total_amount numeric(19,4) NOT NULL DEFAULT 0,
    is_winner boolean NOT NULL DEFAULT false,
    responded_at timestamptz,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT quotation_suppliers_unique UNIQUE (quotation_request_id, supplier_id),
    CONSTRAINT quotation_suppliers_status_check CHECK (status IN ('invited','viewed','answered','declined','disqualified','winner')),
    CONSTRAINT quotation_suppliers_values_check CHECK (freight_amount >= 0 AND discount_amount >= 0 AND other_amount >= 0 AND subtotal >= 0 AND total_amount >= 0),
    CONSTRAINT quotation_suppliers_delivery_check CHECK (delivery_days IS NULL OR delivery_days >= 0)
);

CREATE TABLE purchasing.quotation_items (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    quotation_supplier_id uuid NOT NULL REFERENCES purchasing.quotation_suppliers(id) ON DELETE CASCADE,
    purchase_request_item_id uuid NOT NULL REFERENCES purchasing.purchase_request_items(id),
    quantity numeric(19,4) NOT NULL,
    unit_price numeric(19,4) NOT NULL DEFAULT 0,
    discount_amount numeric(19,4) NOT NULL DEFAULT 0,
    tax_amount numeric(19,4) NOT NULL DEFAULT 0,
    total_amount numeric(19,4) GENERATED ALWAYS AS ((quantity * unit_price) - discount_amount + tax_amount) STORED,
    brand varchar(120),
    notes text,
    selected boolean NOT NULL DEFAULT false,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT quotation_items_unique UNIQUE (quotation_supplier_id, purchase_request_item_id),
    CONSTRAINT quotation_items_quantity_check CHECK (quantity > 0),
    CONSTRAINT quotation_items_values_check CHECK (unit_price >= 0 AND discount_amount >= 0 AND tax_amount >= 0)
);
CREATE INDEX quotation_items_comparison_idx ON purchasing.quotation_items (purchase_request_item_id, total_amount);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS purchasing.quotation_items;
DROP TABLE IF EXISTS purchasing.quotation_suppliers;
DROP TABLE IF EXISTS purchasing.quotation_requests;
DROP TABLE IF EXISTS purchasing.purchase_approvals;
DROP TABLE IF EXISTS purchasing.approval_rules;
SQL);
    }
};
