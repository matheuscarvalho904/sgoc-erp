<?php

declare(strict_types=1);

namespace Tests\Unit\Architecture;

use App\Modules\Inventory\Domain\Models\Reservation;
use App\Modules\Inventory\Domain\Models\StockBalance;
use App\Modules\Inventory\Domain\Models\StockMovement;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class InventoryModelLifecycleTest extends TestCase
{
    #[DataProvider('modelsWithoutSoftDeletes')]
    public function test_operational_inventory_models_do_not_use_soft_deletes(string $model): void
    {
        self::assertNotContains(SoftDeletes::class, class_uses_recursive($model));
    }

    public static function modelsWithoutSoftDeletes(): array
    {
        return [
            [StockBalance::class],
            [StockMovement::class],
            [Reservation::class],
        ];
    }
}
