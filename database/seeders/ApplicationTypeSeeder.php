<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class ApplicationTypeSeeder extends Seeder
{
    public function run(): void
    {
        $tenants = DB::table('core.tenants')->pluck('id');

        $types = [
            ['CA', 'Centro de Aplicação', 'cost_application', true, true, 'none'],
            ['E', 'Equipamento / Ativo', 'asset', true, true, 'none'],
            ['S', 'Subempreiteiro', 'subcontractor', true, true, 'none'],
            ['M', 'Aplicação manual', 'manual', false, true, 'none'],
            ['ES', 'Equipamento/Subempreiteiro — deduzir na medição', 'asset_subcontractor', true, true, 'deduct'],
            ['SA', 'Equipamento/Subempreiteiro — adicionar na medição', 'asset_subcontractor', true, true, 'add'],
            ['AD', 'Equipamento/Subempreiteiro — deduzir e adicionar', 'asset_subcontractor', true, true, 'deduct_add'],
            ['OBRA', 'Obra', 'work', true, true, 'none'],
            ['CC', 'Centro de Custo', 'cost_center', true, true, 'none'],
            ['DEP', 'Departamento', 'department', true, true, 'none'],
            ['ALM', 'Almoxarifado / Estoque', 'warehouse', true, true, 'none'],
            ['OS', 'Ordem de Serviço', 'service_order', true, true, 'none'],
            ['CTR', 'Contrato', 'contract', true, true, 'none'],
            ['MED', 'Medição', 'measurement', true, true, 'none'],
            ['PROD', 'Produção', 'production', true, true, 'none'],
            ['SERV', 'Serviço / Composição orçamentária', 'budget_service', true, true, 'none'],
            ['FRENTE', 'Frente de serviço / Trecho', 'work_front', true, true, 'none'],
            ['LAB', 'Laboratório / Controle tecnológico', 'laboratory', true, true, 'none'],
            ['PAT', 'Patrimônio', 'patrimony', true, true, 'none'],
            ['OUTRO', 'Outro', 'manual', false, true, 'none'],
        ];

        foreach ($tenants as $tenantId) {
            foreach ($types as $index => [$code, $name, $targetKind, $requiresTarget, $allowsAllocation, $effect]) {
                DB::table('purchasing.application_types')->updateOrInsert(
                    ['tenant_id' => $tenantId, 'code' => $code],
                    [
                        'id' => (string) Str::uuid(),
                        'name' => $name,
                        'target_kind' => $targetKind,
                        'requires_target' => $requiresTarget,
                        'allows_allocation' => $allowsAllocation,
                        'measurement_effect' => $effect,
                        'status' => 'active',
                        'sort_order' => ($index + 1) * 10,
                        'updated_at' => now(),
                        'created_at' => now(),
                    ]
                );
            }
        }
    }
}
