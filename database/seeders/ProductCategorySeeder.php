<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\ProductCategory;
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['AGREG', 'Agregados'], ['CIM', 'Cimentos e argamassas'], ['ACO', 'Aços e ferragens'],
            ['HID', 'Materiais hidráulicos'], ['ELE', 'Materiais elétricos'], ['MADE', 'Madeiras e formas'],
            ['FERR', 'Ferramentas'], ['EPI', 'Equipamentos de proteção individual'], ['SINAL', 'Sinalização'],
            ['PEC', 'Peças e manutenção'], ['PNEU', 'Pneus e câmaras'], ['FILTRO', 'Filtros'],
            ['COMB', 'Combustíveis'], ['LUB', 'Lubrificantes e graxas'], ['PRE', 'Pré-moldados'],
            ['ASF', 'Insumos asfálticos'], ['SERV', 'Serviços'], ['EXP', 'Materiais de expediente'],
            ['LIMP', 'Materiais de limpeza'], ['ALIM', 'Alimentação e cozinha'],
        ];

        foreach (Tenant::query()->get() as $tenant) {
            foreach ($records as [$code, $name]) {
                ProductCategory::withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $code],
                    ['name' => $name, 'status' => 'active', 'deleted_at' => null],
                );
            }
        }
    }
}
