<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Unit;
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class UnitSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['UN', 'Unidade', 'UN', 0], ['PC', 'Peça', 'PÇ', 0], ['PAR', 'Par', 'PAR', 0],
            ['JG', 'Jogo', 'JG', 0], ['KIT', 'Kit', 'KIT', 0], ['CX', 'Caixa', 'CX', 0],
            ['PCT', 'Pacote', 'PCT', 0], ['RL', 'Rolo', 'RL', 0], ['SC', 'Saco', 'SC', 0],
            ['BD', 'Balde', 'BD', 0], ['GL', 'Galão', 'GL', 0], ['TB', 'Tambor', 'TB', 0],
            ['KG', 'Quilograma', 'KG', 3], ['G', 'Grama', 'G', 3], ['TON', 'Tonelada', 'TON', 3],
            ['L', 'Litro', 'L', 3], ['ML', 'Mililitro', 'ML', 3], ['M', 'Metro', 'M', 3],
            ['CM', 'Centímetro', 'CM', 2], ['MM', 'Milímetro', 'MM', 2],
            ['M2', 'Metro quadrado', 'M²', 3], ['M3', 'Metro cúbico', 'M³', 3],
            ['H', 'Hora', 'H', 2], ['DIA', 'Dia', 'DIA', 0], ['MES', 'Mês', 'MÊS', 0],
            ['KM', 'Quilômetro', 'KM', 3], ['KM2', 'Quilômetro quadrado', 'KM²', 3],
            ['VB', 'Verba', 'VB', 2], ['SERV', 'Serviço', 'SERV', 2], ['CARGA', 'Carga', 'CARGA', 0],
        ];

        foreach (Tenant::query()->get() as $tenant) {
            foreach ($records as [$code, $name, $symbol, $decimals]) {
                Unit::withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $code],
                    ['name' => $name, 'symbol' => $symbol, 'decimal_places' => $decimals, 'status' => 'active', 'deleted_at' => null],
                );
            }
        }
    }
}
