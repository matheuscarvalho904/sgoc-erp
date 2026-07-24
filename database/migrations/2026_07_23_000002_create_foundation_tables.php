<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE core.tenants (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    name varchar(150) NOT NULL,
    slug varchar(100) NOT NULL,
    legal_name varchar(200),
    document varchar(20),
    email varchar(180),
    phone varchar(30),
    timezone varchar(80) NOT NULL DEFAULT 'America/Cuiaba',
    currency char(3) NOT NULL DEFAULT 'BRL',
    status varchar(30) NOT NULL DEFAULT 'active',
    settings jsonb NOT NULL DEFAULT '{}'::jsonb,
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT tenants_slug_unique UNIQUE (slug),
    CONSTRAINT tenants_status_check CHECK (status IN ('active', 'inactive', 'suspended'))
);

CREATE UNIQUE INDEX tenants_document_unique
    ON core.tenants (document)
    WHERE document IS NOT NULL AND deleted_at IS NULL;

CREATE TABLE core.organizations (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    code varchar(30) NOT NULL,
    name varchar(150) NOT NULL,
    legal_name varchar(200),
    document varchar(20),
    status varchar(30) NOT NULL DEFAULT 'active',
    settings jsonb NOT NULL DEFAULT '{}'::jsonb,
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT organizations_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT organizations_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE INDEX organizations_tenant_idx ON core.organizations (tenant_id);

CREATE TABLE core.companies (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid NOT NULL REFERENCES core.organizations(id),
    code varchar(30) NOT NULL,
    name varchar(150) NOT NULL,
    legal_name varchar(200),
    document varchar(20),
    state_registration varchar(40),
    municipal_registration varchar(40),
    email varchar(180),
    phone varchar(30),
    timezone varchar(80) NOT NULL DEFAULT 'America/Cuiaba',
    currency char(3) NOT NULL DEFAULT 'BRL',
    status varchar(30) NOT NULL DEFAULT 'active',
    settings jsonb NOT NULL DEFAULT '{}'::jsonb,
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT companies_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT companies_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE UNIQUE INDEX companies_document_unique
    ON core.companies (tenant_id, document)
    WHERE document IS NOT NULL AND deleted_at IS NULL;

CREATE INDEX companies_organization_idx ON core.companies (organization_id);

CREATE TABLE core.branches (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid NOT NULL REFERENCES core.organizations(id),
    company_id uuid NOT NULL REFERENCES core.companies(id),
    code varchar(30) NOT NULL,
    name varchar(150) NOT NULL,
    document varchar(20),
    email varchar(180),
    phone varchar(30),
    zip_code varchar(12),
    street varchar(180),
    number varchar(30),
    complement varchar(120),
    district varchar(120),
    city varchar(120),
    state char(2),
    is_headquarters boolean NOT NULL DEFAULT false,
    status varchar(30) NOT NULL DEFAULT 'active',
    settings jsonb NOT NULL DEFAULT '{}'::jsonb,
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT branches_company_code_unique UNIQUE (company_id, code),
    CONSTRAINT branches_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE UNIQUE INDEX branches_document_unique
    ON core.branches (tenant_id, document)
    WHERE document IS NOT NULL AND deleted_at IS NULL;

CREATE UNIQUE INDEX branches_headquarters_unique
    ON core.branches (company_id)
    WHERE is_headquarters = true AND deleted_at IS NULL;

CREATE INDEX branches_company_idx ON core.branches (company_id);

CREATE TABLE core.works (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid NOT NULL REFERENCES core.organizations(id),
    company_id uuid NOT NULL REFERENCES core.companies(id),
    branch_id uuid NOT NULL REFERENCES core.branches(id),
    code varchar(40) NOT NULL,
    name varchar(200) NOT NULL,
    description text,
    client_name varchar(200),
    contract_number varchar(80),
    start_date date,
    expected_end_date date,
    actual_end_date date,
    budget_amount numeric(19,4),
    zip_code varchar(12),
    street varchar(180),
    number varchar(30),
    complement varchar(120),
    district varchar(120),
    city varchar(120),
    state char(2),
    latitude numeric(11,8),
    longitude numeric(11,8),
    status varchar(30) NOT NULL DEFAULT 'planning',
    settings jsonb NOT NULL DEFAULT '{}'::jsonb,
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT works_company_code_unique UNIQUE (company_id, code),
    CONSTRAINT works_status_check CHECK (
        status IN ('planning', 'mobilizing', 'in_progress', 'paused', 'completed', 'cancelled')
    ),
    CONSTRAINT works_dates_check CHECK (
        expected_end_date IS NULL OR start_date IS NULL OR expected_end_date >= start_date
    )
);

CREATE INDEX works_tenant_status_idx ON core.works (tenant_id, status);
CREATE INDEX works_branch_idx ON core.works (branch_id);

CREATE TABLE core.departments (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid REFERENCES core.organizations(id),
    company_id uuid REFERENCES core.companies(id),
    branch_id uuid REFERENCES core.branches(id),
    parent_id uuid REFERENCES core.departments(id),
    code varchar(30) NOT NULL,
    name varchar(150) NOT NULL,
    description text,
    status varchar(30) NOT NULL DEFAULT 'active',
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT departments_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT departments_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE INDEX departments_tenant_idx ON core.departments (tenant_id);

CREATE TABLE core.cost_centers (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id),
    organization_id uuid REFERENCES core.organizations(id),
    company_id uuid REFERENCES core.companies(id),
    branch_id uuid REFERENCES core.branches(id),
    work_id uuid REFERENCES core.works(id),
    parent_id uuid REFERENCES core.cost_centers(id),
    code varchar(40) NOT NULL,
    name varchar(160) NOT NULL,
    description text,
    type varchar(30) NOT NULL DEFAULT 'administrative',
    status varchar(30) NOT NULL DEFAULT 'active',
    lock_version integer NOT NULL DEFAULT 0,
    created_by bigint,
    updated_by bigint,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT cost_centers_tenant_code_unique UNIQUE (tenant_id, code),
    CONSTRAINT cost_centers_type_check CHECK (
        type IN ('administrative', 'operational', 'work', 'production', 'maintenance')
    ),
    CONSTRAINT cost_centers_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE INDEX cost_centers_tenant_type_idx ON core.cost_centers (tenant_id, type);
CREATE INDEX cost_centers_work_idx ON core.cost_centers (work_id);

CREATE TABLE core.user_tenants (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id) ON DELETE CASCADE,
    user_id bigint NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    is_default boolean NOT NULL DEFAULT false,
    status varchar(30) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    CONSTRAINT user_tenants_unique UNIQUE (tenant_id, user_id),
    CONSTRAINT user_tenants_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE UNIQUE INDEX user_tenants_default_unique
    ON core.user_tenants (user_id)
    WHERE is_default = true;

CREATE INDEX user_tenants_tenant_idx ON core.user_tenants (tenant_id);
CREATE INDEX user_tenants_user_idx ON core.user_tenants (user_id);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS core.user_tenants;
DROP TABLE IF EXISTS core.cost_centers;
DROP TABLE IF EXISTS core.departments;
DROP TABLE IF EXISTS core.works;
DROP TABLE IF EXISTS core.branches;
DROP TABLE IF EXISTS core.companies;
DROP TABLE IF EXISTS core.organizations;
DROP TABLE IF EXISTS core.tenants;
SQL);
    }
};