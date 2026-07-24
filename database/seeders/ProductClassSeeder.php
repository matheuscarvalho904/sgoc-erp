<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Catalog\Domain\Models\Product;
use App\Modules\Catalog\Domain\Models\ProductClass;
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class ProductClassSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tenant::query()->pluck('id') as $tenantId) {
            $classes = [
                ['MAT','Material',true,false,false,false,true,false,true,false,false,false],
                ['PEC','Peça',true,false,false,false,true,false,true,false,false,true],
                ['COMB','Combustível',true,true,false,false,true,false,false,true,false,false],
                ['LUB','Lubrificante',true,true,true,false,true,false,true,false,false,false],
                ['EPI','EPI',true,true,true,false,true,false,true,false,false,false],
                ['FER','Ferramenta',true,false,false,true,true,false,true,false,false,true],
                ['PAT','Patrimônio',false,false,false,true,true,false,false,false,true,true],
                ['INS','Insumo',true,true,true,false,true,false,true,false,false,false],
                ['PRE','Pré-moldado',true,true,false,false,true,true,false,false,false,false],
            ];

            foreach ($classes as [$code,$name,$stock,$lot,$expiration,$asset,$purchase,$sale,$os,$fueling,$depreciation,$serial]) {
                ProductClass::query()->updateOrCreate(
                    ['tenant_id'=>$tenantId,'code'=>$code],
                    ['name'=>$name,'controls_stock'=>$stock,'requires_lot'=>$lot,'requires_expiration'=>$expiration,'requires_asset'=>$asset,'allows_purchase'=>$purchase,'allows_sale'=>$sale,'allows_os_consumption'=>$os,'allows_fueling'=>$fueling,'generates_depreciation'=>$depreciation,'controls_serial_number'=>$serial,'status'=>'active']
                );
            }

            $map=['material'=>'MAT','part'=>'PEC','fuel'=>'COMB','epi'=>'EPI','asset'=>'PAT','input'=>'INS'];
            foreach ($map as $type=>$code) {
                $classId=ProductClass::query()->where('tenant_id',$tenantId)->where('code',$code)->value('id');
                Product::query()->where('tenant_id',$tenantId)->where('product_type',$type)->whereNull('product_class_id')->update(['product_class_id'=>$classId]);
            }
        }
    }
}
