<?php

declare(strict_types=1);
namespace Database\Seeders;
use App\Modules\Fuel\Domain\Models\FuelStorage;
use Illuminate\Database\Seeder;
final class FuelModuleSeeder extends Seeder {
 public function run(): void { if (!FuelStorage::query()->exists()) FuelStorage::query()->create(['code'=>'TQ-01','name'=>'Tanque Principal','storage_type'=>'tank','capacity_liters'=>15000,'minimum_level_liters'=>2000,'status'=>'active']); }
}
