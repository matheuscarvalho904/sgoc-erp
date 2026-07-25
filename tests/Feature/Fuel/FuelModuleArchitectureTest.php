<?php

declare(strict_types=1);
namespace Tests\Feature\Fuel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FuelModuleArchitectureTest extends TestCase {
 #[Test] public function fuel_transaction_models_do_not_use_soft_deletes(): void { foreach (['FuelEntry','Fueling','FuelStockMovement'] as $model) { $class='App\\Modules\\Fuel\\Domain\\Models\\'.$model; $this->assertFalse(in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($class), true), $model.' não pode usar SoftDeletes.'); } }
 #[Test] public function fuel_snapshot_does_not_use_soft_deletes(): void { $class='App\\Modules\\Fuel\\Domain\\Models\\FuelStockBalance'; $this->assertFalse(in_array('Illuminate\\Database\\Eloquent\\SoftDeletes', class_uses_recursive($class), true)); }
 #[Test] public function fuel_resources_are_available(): void { foreach (['FuelStorages\\FuelStorageResource','FuelEntries\\FuelEntryResource','Fuelings\\FuelingResource','FuelStockBalances\\FuelStockBalanceResource'] as $resource) $this->assertTrue(class_exists('App\\Filament\\Resources\\'.$resource)); }
}
