<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class IdentityAccessSeeder extends Seeder
{
    public function run(): void
    {
        DB::transaction(function (): void {
            $permissions = $this->permissions();

            foreach ($permissions as $permission) {
                DB::table('access_control.permissions')->updateOrInsert(
                    ['code' => $permission['code']],
                    [
                        'id' => DB::table('access_control.permissions')
                            ->where('code', $permission['code'])
                            ->value('id') ?? (string) Str::uuid(),
                        'name' => $permission['name'],
                        'module' => $permission['module'],
                        'action' => $permission['action'],
                        'description' => $permission['description'],
                        'is_system' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }

            $tenantId = DB::table('core.tenants')->orderBy('created_at')->value('id');

            if ($tenantId === null) {
                throw new \RuntimeException('Nenhum tenant encontrado. Execute o FoundationSeeder primeiro.');
            }

            $roles = [
                [
                    'code' => 'super-admin',
                    'name' => 'Superadministrador',
                    'description' => 'Acesso total ao tenant e Ã s configuraÃ§Ãµes corporativas.',
                    'is_system' => true,
                    'is_super_admin' => true,
                ],
                [
                    'code' => 'administrator',
                    'name' => 'Administrador',
                    'description' => 'AdministraÃ§Ã£o funcional do SGOC ERP.',
                    'is_system' => true,
                    'is_super_admin' => false,
                ],
                [
                    'code' => 'work-manager',
                    'name' => 'Gestor de Obras',
                    'description' => 'GestÃ£o operacional de obras.',
                    'is_system' => true,
                    'is_super_admin' => false,
                ],
                [
                    'code' => 'financial-manager',
                    'name' => 'Gestor Financeiro',
                    'description' => 'GestÃ£o financeira e aprovaÃ§Ãµes.',
                    'is_system' => true,
                    'is_super_admin' => false,
                ],
                [
                    'code' => 'buyer',
                    'name' => 'Comprador',
                    'description' => 'SolicitaÃ§Ãµes, cotaÃ§Ãµes e pedidos.',
                    'is_system' => true,
                    'is_super_admin' => false,
                ],
                [
                    'code' => 'viewer',
                    'name' => 'Consulta',
                    'description' => 'Acesso somente para consulta.',
                    'is_system' => true,
                    'is_super_admin' => false,
                ],
            ];

            foreach ($roles as $roleData) {
                $roleId = DB::table('access_control.roles')
                    ->where('tenant_id', $tenantId)
                    ->where('code', $roleData['code'])
                    ->value('id');

                DB::table('access_control.roles')->updateOrInsert(
                    [
                        'tenant_id' => $tenantId,
                        'code' => $roleData['code'],
                    ],
                    [
                        'id' => $roleId ?? (string) Str::uuid(),
                        'name' => $roleData['name'],
                        'description' => $roleData['description'],
                        'is_system' => $roleData['is_system'],
                        'is_super_admin' => $roleData['is_super_admin'],
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }

            $administratorRoleId = DB::table('access_control.roles')
                ->where('tenant_id', $tenantId)
                ->where('code', 'administrator')
                ->value('id');

            $permissionIds = DB::table('access_control.permissions')->pluck('id');

            foreach ($permissionIds as $permissionId) {
                DB::table('access_control.role_permissions')->updateOrInsert(
                    [
                        'role_id' => $administratorRoleId,
                        'permission_id' => $permissionId,
                    ],
                    [
                        'granted' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }

            $firstUserId = DB::table('users')->orderBy('id')->value('id');
            $superAdminRoleId = DB::table('access_control.roles')
                ->where('tenant_id', $tenantId)
                ->where('code', 'super-admin')
                ->value('id');

            if ($firstUserId !== null && $superAdminRoleId !== null) {
                DB::table('access_control.user_roles')->updateOrInsert(
                    [
                        'tenant_id' => $tenantId,
                        'user_id' => $firstUserId,
                        'role_id' => $superAdminRoleId,
                    ],
                    [
                        'id' => DB::table('access_control.user_roles')
                            ->where('tenant_id', $tenantId)
                            ->where('user_id', $firstUserId)
                            ->where('role_id', $superAdminRoleId)
                            ->value('id') ?? (string) Str::uuid(),
                        'status' => 'active',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                );
            }
        });
    }

    private function permissions(): array
    {
        $modules = [
            'dashboard' => 'Painel',
            'tenants' => 'Tenants',
            'organizations' => 'OrganizaÃ§Ãµes',
            'companies' => 'Empresas',
            'branches' => 'Filiais',
            'works' => 'Obras',
            'departments' => 'Departamentos',
            'cost-centers' => 'Centros de custo',
            'users' => 'UsuÃ¡rios',
            'roles' => 'Perfis de acesso',
            'permissions' => 'PermissÃµes',
            'audit' => 'Auditoria',
            'settings' => 'ConfiguraÃ§Ãµes',
        ];

        $actions = [
            'view-any' => 'Listar',
            'view' => 'Visualizar',
            'create' => 'Criar',
            'update' => 'Editar',
            'delete' => 'Excluir',
            'restore' => 'Restaurar',
            'export' => 'Exportar',
            'approve' => 'Aprovar',
        ];

        $permissions = [];

        foreach ($modules as $moduleCode => $moduleName) {
            foreach ($actions as $actionCode => $actionName) {
                $permissions[] = [
                    'code' => $moduleCode.'.'.$actionCode,
                    'name' => $actionName.' '.$moduleName,
                    'module' => $moduleCode,
                    'action' => $actionCode,
                    'description' => $actionName.' registros do mÃ³dulo '.$moduleName.'.',
                ];
            }
        }

        return $permissions;
    }
}