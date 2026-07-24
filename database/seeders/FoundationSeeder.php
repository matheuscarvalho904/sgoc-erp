<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class FoundationSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $tenantId = (string) Str::uuid();
            $organizationId = (string) Str::uuid();
            $companyId = (string) Str::uuid();
            $branchId = (string) Str::uuid();

            $tenant = DB::table('core.tenants')
                ->where('slug', config('sgoc.default_tenant_slug', 'sgoc'))
                ->first();

            if ($tenant === null) {
                DB::table('core.tenants')->insert([
                    'id' => $tenantId,
                    'name' => 'SGOC ERP',
                    'slug' => config('sgoc.default_tenant_slug', 'sgoc'),
                    'timezone' => config('sgoc.timezone', 'America/Cuiaba'),
                    'currency' => config('sgoc.currency', 'BRL'),
                    'status' => 'active',
                    'settings' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $tenantId = $tenant->id;
            }

            $organization = DB::table('core.organizations')
                ->where('tenant_id', $tenantId)
                ->where('code', 'ORG-001')
                ->first();

            if ($organization === null) {
                DB::table('core.organizations')->insert([
                    'id' => $organizationId,
                    'tenant_id' => $tenantId,
                    'code' => 'ORG-001',
                    'name' => 'OrganizaÃ§Ã£o Principal',
                    'status' => 'active',
                    'settings' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $organizationId = $organization->id;
            }

            $company = DB::table('core.companies')
                ->where('tenant_id', $tenantId)
                ->where('code', 'EMP-001')
                ->first();

            if ($company === null) {
                DB::table('core.companies')->insert([
                    'id' => $companyId,
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'code' => 'EMP-001',
                    'name' => 'Empresa Principal',
                    'timezone' => config('sgoc.timezone', 'America/Cuiaba'),
                    'currency' => config('sgoc.currency', 'BRL'),
                    'status' => 'active',
                    'settings' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } else {
                $companyId = $company->id;
            }

            $branch = DB::table('core.branches')
                ->where('company_id', $companyId)
                ->where('code', 'FIL-001')
                ->first();

            if ($branch === null) {
                DB::table('core.branches')->insert([
                    'id' => $branchId,
                    'tenant_id' => $tenantId,
                    'organization_id' => $organizationId,
                    'company_id' => $companyId,
                    'code' => 'FIL-001',
                    'name' => 'Matriz',
                    'is_headquarters' => true,
                    'status' => 'active',
                    'settings' => json_encode([], JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $userId = DB::table('users')->orderBy('id')->value('id');

            if ($userId !== null) {
                DB::table('core.user_tenants')->updateOrInsert(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $userId,
                    ],
                    [
                        'is_default' => true,
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });
    }
}