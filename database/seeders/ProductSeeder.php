<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\{Product, ProductCategory, Unit};
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['MAT-000001','Cimento CP II 50 kg','CIM','SC','material',true],
            ['MAT-000002','Cimento CP V-ARI 40 kg','CIM','SC','material',true],
            ['MAT-000003','Areia média lavada','AGREG','M3','material',true],
            ['MAT-000004','Areia fina lavada','AGREG','M3','material',true],
            ['MAT-000005','Brita 0','AGREG','M3','material',true],
            ['MAT-000006','Brita 1','AGREG','M3','material',true],
            ['MAT-000007','Brita 2','AGREG','M3','material',true],
            ['MAT-000008','Pó de brita','AGREG','M3','material',true],
            ['MAT-000009','Rachão','AGREG','M3','material',true],
            ['MAT-000010','Aço CA-50 10 mm','ACO','KG','material',true],
            ['MAT-000011','Aço CA-50 12,5 mm','ACO','KG','material',true],
            ['MAT-000012','Tela soldada POP','ACO','PC','material',true],
            ['HID-000001','Tubo PVC soldável 50 mm','HID','M','material',true],
            ['HID-000002','Tubo PVC esgoto 100 mm','HID','M','material',true],
            ['HID-000003','Tubo PEAD corrugado 600 mm','HID','M','material',true],
            ['PRE-000001','Meio-fio de concreto','PRE','PC','material',true],
            ['PRE-000002','Aduela de concreto 2,00 x 2,00 m','PRE','PC','material',true],
            ['ASF-000001','CAP 50/70','ASF','TON','input',true],
            ['ASF-000002','Emulsão asfáltica RR-2C','ASF','TON','input',true],
            ['COM-000001','Óleo diesel S10','COMB','L','fuel',true],
            ['COM-000002','ARLA 32','COMB','L','fuel',true],
            ['LUB-000001','Óleo hidráulico ISO VG 68','LUB','L','input',true],
            ['LUB-000002','Óleo motor 15W40','LUB','L','input',true],
            ['LUB-000003','Graxa para rolamentos','LUB','KG','input',true],
            ['EPI-000001','Capacete de segurança','EPI','UN','epi',true],
            ['EPI-000002','Luva de raspa','EPI','PAR','epi',true],
            ['EPI-000003','Óculos de proteção','EPI','UN','epi',true],
            ['EPI-000004','Protetor auricular','EPI','UN','epi',true],
            ['EPI-000005','Colete refletivo','EPI','UN','epi',true],
            ['PEC-000001','Filtro de óleo do motor','FILTRO','UN','part',true],
            ['PEC-000002','Filtro de combustível','FILTRO','UN','part',true],
            ['PEC-000003','Filtro de ar primário','FILTRO','UN','part',true],
            ['PEC-000004','Correia em V','PEC','UN','part',true],
            ['PNE-000001','Pneu 275/80 R22.5','PNEU','UN','part',true],
            ['SER-000001','Serviço de usinagem','SERV','SERV','service',false],
            ['SER-000002','Serviço de soldagem','SERV','H','service',false],
        ];

        foreach (Tenant::query()->get() as $tenant) {
            $categories = ProductCategory::query()->where('tenant_id', $tenant->id)->pluck('id', 'code');
            $units = Unit::query()->where('tenant_id', $tenant->id)->pluck('id', 'code');

            foreach ($records as [$code, $name, $categoryCode, $unitCode, $type, $trackStock]) {
                if (! isset($categories[$categoryCode], $units[$unitCode])) {
                    continue;
                }

                Product::withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $code],
                    [
                        'name' => $name,
                        'category_id' => $categories[$categoryCode],
                        'unit_id' => $units[$unitCode],
                        'product_type' => $type,
                        'track_stock' => $trackStock,
                        'minimum_stock' => 0,
                        'average_cost' => 0,
                        'last_purchase_price' => 0,
                        'status' => 'active',
                        'deleted_at' => null,
                    ],
                );
            }
        }
    }
}
