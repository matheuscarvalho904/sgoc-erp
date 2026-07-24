<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Brand;
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class BrandSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            '3M','Amanco','Bosch','Bridgestone','Case','Caterpillar','Cummins','DeWalt','Docol','Eaton',
            'Eucatex','Gerdau','Goodyear','Husqvarna','Iveco','John Deere','Komatsu','Makita','Mercedes-Benz',
            'Michelin','Mobil','Pirelli','Quartzolit','Scania','Shell','Sika','Stanley','Tekbond','Tigre',
            'Tramontina','Valtra','Volkswagen','Volvo','Vonder','WEG','Yanmar','ZF','Ipiranga','Petrobras','Lubrax',
        ];

        foreach (Tenant::query()->get() as $tenant) {
            foreach ($brands as $index => $name) {
                Brand::withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'name' => $name],
                    ['code' => 'MAR-' . str_pad((string) ($index + 1), 3, '0', STR_PAD_LEFT), 'status' => 'active', 'deleted_at' => null],
                );
            }
        }
    }
}
