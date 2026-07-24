<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Assets\Domain\Models\{AssetCategory, AssetPrefix, AssetType, Fuel};
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class AssetFoundationSeeder extends Seeder
{
    public function run(): void
    {
        foreach (Tenant::query()->pluck('id') as $tenantId) {
            $types=['CAM'=>'Caminhão','VEI'=>'Veículo leve','ESC'=>'Escavadeira','MOT'=>'Motoniveladora','TRA'=>'Trator','PAC'=>'Pá carregadeira','ROL'=>'Rolo compactador','RET'=>'Retroescavadeira','USI'=>'Usina','BRI'=>'Britador','GER'=>'Gerador','COM'=>'Compressor'];
            foreach ($types as $code=>$name) AssetType::query()->updateOrCreate(['tenant_id'=>$tenantId,'code'=>$code],['name'=>$name,'status'=>'active']);

            $categories=[
                ['CB','Caminhão Basculante','CAM'],['CP','Caminhão Pipa','CAM'],['BET','Caminhão Betoneira','CAM'],['PR','Cavalo Mecânico / Prancha','CAM'],
                ['VL','Veículo Leve','VEI'],['EH','Escavadeira Hidráulica','ESC'],['MN','Motoniveladora','MOT'],['TR','Trator de Esteira','TRA'],
                ['PC','Pá Carregadeira','PAC'],['RL','Rolo Liso','ROL'],['RC','Rolo Pé de Carneiro','ROL'],['RE','Retroescavadeira','RET'],
            ];
            foreach ($categories as [$code,$name,$typeCode]) {
                $typeId=AssetType::query()->where('tenant_id',$tenantId)->where('code',$typeCode)->value('id');
                AssetCategory::query()->updateOrCreate(['tenant_id'=>$tenantId,'code'=>$code],['asset_type_id'=>$typeId,'name'=>$name,'status'=>'active']);
                AssetPrefix::query()->updateOrCreate(['tenant_id'=>$tenantId,'code'=>$code],['asset_type_id'=>$typeId,'name'=>$name,'next_number'=>1,'digits'=>3,'status'=>'active']);
            }

            foreach (['S10'=>'Diesel S10','S500'=>'Diesel S500','GAS'=>'Gasolina','ETA'=>'Etanol','GNV'=>'GNV','ARLA'=>'ARLA 32','ELE'=>'Elétrico'] as $code=>$name) {
                Fuel::query()->updateOrCreate(['tenant_id'=>$tenantId,'code'=>$code],['name'=>$name,'unit'=>$code==='ELE'?'kWh':'L','status'=>'active']);
            }
        }
    }
}
