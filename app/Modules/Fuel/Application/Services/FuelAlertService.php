<?php

declare(strict_types=1);
namespace App\Modules\Fuel\Application\Services;
use App\Modules\Fuel\Domain\Models\{FuelAlert,Fueling,FuelStockBalance,FuelStorage};
final class FuelAlertService
{
    public function evaluate(Fueling $fueling): void
    {
        $storage = FuelStorage::query()->find($fueling->storage_id);
        $balance = FuelStockBalance::query()->where('storage_id',$fueling->storage_id)->where('fuel_id',$fueling->fuel_id)->first();
        if ($storage && $balance && (float)$balance->quantity_liters <= (float)$storage->minimum_level_liters) {
            $this->create($fueling, 'low_stock', 'critical', 'Estoque abaixo do mínimo', 'O ponto de combustível atingiu o nível mínimo configurado.');
        }
        $hour = $fueling->fueled_at?->hour;
        if ($hour !== null && ($hour < 5 || $hour >= 22)) {
            $this->create($fueling, 'unusual_time', 'warning', 'Abastecimento em horário incomum', 'Abastecimento registrado fora da faixa operacional padrão (05h às 22h).');
        }
        if ((float)($fueling->calculated_consumption ?? 0) > 0) {
            $avg = Fueling::query()->where('asset_id',$fueling->asset_id)->whereNotNull('calculated_consumption')->whereKeyNot($fueling->id)->latest('fueled_at')->limit(10)->avg('calculated_consumption');
            if ($avg && (float)$fueling->calculated_consumption > ((float)$avg * 1.35)) {
                $this->create($fueling, 'high_consumption', 'warning', 'Consumo acima da média', 'O indicador calculado ficou mais de 35% acima da média recente do ativo.');
            }
        }
    }
    private function create(Fueling $fueling, string $type, string $severity, string $title, string $message): void
    {
        FuelAlert::query()->firstOrCreate(
            ['fueling_id'=>$fueling->id,'alert_type'=>$type,'status'=>'open'],
            ['tenant_id'=>$fueling->tenant_id,'storage_id'=>$fueling->storage_id,'asset_id'=>$fueling->asset_id,'severity'=>$severity,'title'=>$title,'message'=>$message,'detected_at'=>now()]
        );
    }
}
