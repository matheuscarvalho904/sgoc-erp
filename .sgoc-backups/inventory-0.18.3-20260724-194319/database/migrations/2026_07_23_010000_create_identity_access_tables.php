<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::unprepared(<<<'SQL'
CREATE TABLE access_control.roles (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid REFERENCES core.tenants(id) ON DELETE CASCADE,
    code varchar(80) NOT NULL,
    name varchar(150) NOT NULL,
    description text,
    is_system boolean NOT NULL DEFAULT false,
    is_super_admin boolean NOT NULL DEFAULT false,
    status varchar(30) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT roles_status_check CHECK (status IN ('active', 'inactive'))
);

CREATE UNIQUE INDEX roles_tenant_code_unique
    ON access_control.roles (COALESCE(tenant_id, '00000000-0000-0000-0000-000000000000'::uuid), code)
    WHERE deleted_at IS NULL;

CREATE INDEX roles_tenant_idx ON access_control.roles (tenant_id);

CREATE TABLE access_control.permissions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    code varchar(150) NOT NULL,
    name varchar(180) NOT NULL,
    module varchar(100) NOT NULL,
    action varchar(80) NOT NULL,
    description text,
    is_system boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT permissions_code_unique UNIQUE (code)
);

CREATE INDEX permissions_module_action_idx
    ON access_control.permissions (module, action);

CREATE TABLE access_control.role_permissions (
    role_id uuid NOT NULL REFERENCES access_control.roles(id) ON DELETE CASCADE,
    permission_id uuid NOT NULL REFERENCES access_control.permissions(id) ON DELETE CASCADE,
    granted boolean NOT NULL DEFAULT true,
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    PRIMARY KEY (role_id, permission_id)
);

CREATE TABLE access_control.user_roles (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id) ON DELETE CASCADE,
    user_id bigint NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    role_id uuid NOT NULL REFERENCES access_control.roles(id) ON DELETE CASCADE,
    starts_at timestamptz,
    ends_at timestamptz,
    status varchar(30) NOT NULL DEFAULT 'active',
    created_by bigint REFERENCES public.users(id),
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    CONSTRAINT user_roles_unique UNIQUE (tenant_id, user_id, role_id),
    CONSTRAINT user_roles_status_check CHECK (status IN ('active', 'inactive', 'expired')),
    CONSTRAINT user_roles_dates_check CHECK (
        ends_at IS NULL OR starts_at IS NULL OR ends_at >= starts_at
    )
);

CREATE INDEX user_roles_user_tenant_idx
    ON access_control.user_roles (user_id, tenant_id, status);

CREATE TABLE access_control.user_scopes (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id) ON DELETE CASCADE,
    user_id bigint NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    organization_id uuid REFERENCES core.organizations(id) ON DELETE CASCADE,
    company_id uuid REFERENCES core.companies(id) ON DELETE CASCADE,
    branch_id uuid REFERENCES core.branches(id) ON DELETE CASCADE,
    work_id uuid REFERENCES core.works(id) ON DELETE CASCADE,
    scope_type varchar(30) NOT NULL,
    status varchar(30) NOT NULL DEFAULT 'active',
    created_by bigint REFERENCES public.users(id),
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    CONSTRAINT user_scopes_status_check CHECK (status IN ('active', 'inactive')),
    CONSTRAINT user_scopes_type_check CHECK (
        scope_type IN ('tenant', 'organization', 'company', 'branch', 'work')
    )
);

CREATE INDEX user_scopes_user_tenant_idx
    ON access_control.user_scopes (user_id, tenant_id, status);

CREATE UNIQUE INDEX user_scopes_unique
    ON access_control.user_scopes (
        tenant_id,
        user_id,
        scope_type,
        COALESCE(organization_id, '00000000-0000-0000-0000-000000000000'::uuid),
        COALESCE(company_id, '00000000-0000-0000-0000-000000000000'::uuid),
        COALESCE(branch_id, '00000000-0000-0000-0000-000000000000'::uuid),
        COALESCE(work_id, '00000000-0000-0000-0000-000000000000'::uuid)
    );

CREATE TABLE access_control.segregation_rules (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid REFERENCES core.tenants(id) ON DELETE CASCADE,
    code varchar(100) NOT NULL,
    name varchar(180) NOT NULL,
    description text,
    permission_a_id uuid NOT NULL REFERENCES access_control.permissions(id),
    permission_b_id uuid NOT NULL REFERENCES access_control.permissions(id),
    severity varchar(30) NOT NULL DEFAULT 'blocking',
    status varchar(30) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    deleted_at timestamptz,
    CONSTRAINT segregation_rules_severity_check CHECK (
        severity IN ('warning', 'blocking')
    ),
    CONSTRAINT segregation_rules_status_check CHECK (
        status IN ('active', 'inactive')
    ),
    CONSTRAINT segregation_rules_distinct_permissions_check CHECK (
        permission_a_id <> permission_b_id
    )
);

CREATE UNIQUE INDEX segregation_rules_tenant_code_unique
    ON access_control.segregation_rules (
        COALESCE(tenant_id, '00000000-0000-0000-0000-000000000000'::uuid),
        code
    )
    WHERE deleted_at IS NULL;

CREATE TABLE access_control.access_exceptions (
    id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
    tenant_id uuid NOT NULL REFERENCES core.tenants(id) ON DELETE CASCADE,
    user_id bigint NOT NULL REFERENCES public.users(id) ON DELETE CASCADE,
    permission_id uuid NOT NULL REFERENCES access_control.permissions(id) ON DELETE CASCADE,
    effect varchar(20) NOT NULL,
    reason text NOT NULL,
    starts_at timestamptz,
    ends_at timestamptz,
    approved_by bigint REFERENCES public.users(id),
    status varchar(30) NOT NULL DEFAULT 'active',
    created_at timestamptz NOT NULL DEFAULT now(),
    updated_at timestamptz NOT NULL DEFAULT now(),
    CONSTRAINT access_exceptions_effect_check CHECK (
        effect IN ('allow', 'deny')
    ),
    CONSTRAINT access_exceptions_status_check CHECK (
        status IN ('active', 'inactive', 'expired', 'revoked')
    ),
    CONSTRAINT access_exceptions_dates_check CHECK (
        ends_at IS NULL OR starts_at IS NULL OR ends_at >= starts_at
    )
);

CREATE INDEX access_exceptions_user_tenant_idx
    ON access_control.access_exceptions (user_id, tenant_id, status);
SQL);
    }

    public function down(): void
    {
        DB::unprepared(<<<'SQL'
DROP TABLE IF EXISTS access_control.access_exceptions;
DROP TABLE IF EXISTS access_control.segregation_rules;
DROP TABLE IF EXISTS access_control.user_scopes;
DROP TABLE IF EXISTS access_control.user_roles;
DROP TABLE IF EXISTS access_control.role_permissions;
DROP TABLE IF EXISTS access_control.permissions;
DROP TABLE IF EXISTS access_control.roles;
SQL);
    }
};