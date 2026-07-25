<?php

declare(strict_types=1);

namespace Tests\Unit\Inventory;

use App\Modules\Inventory\Application\Services\InventoryTransactionService;
use App\Modules\Inventory\Domain\Models\StockBalance;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Shared\Models\SnapshotModel;
use App\Shared\Models\TransactionModel;
use PHPUnit\Framework\TestCase;

final class InventoryTransactionArchitectureTest extends TestCase
{
    public function test_inventory_models_use_correct_lifecycle_bases(): void
    {
        self::assertTrue(is_subclass_of(StockMovement::class, TransactionModel::class));
        self::assertTrue(is_subclass_of(StockBalance::class, SnapshotModel::class));
        self::assertTrue(class_exists(InventoryTransactionService::class));
    }
}
