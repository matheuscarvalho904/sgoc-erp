<?php

declare(strict_types=1);

namespace Tests\Architecture;

use App\Modules\Documents\Domain\Models\Document;
use App\Modules\Inventory\Domain\Models\Reservation;
use App\Modules\Inventory\Domain\Models\StockBalance;
use App\Modules\Inventory\Domain\Models\StockMovement;
use App\Modules\Inventory\Domain\Models\StockRequest;
use App\Modules\Maintenance\Domain\Models\MaintenanceRequest;
use App\Modules\Maintenance\Domain\Models\WorkOrder;
use App\Modules\Purchasing\Domain\Models\PurchaseOrder;
use App\Modules\Purchasing\Domain\Models\PurchaseRequest;
use App\Shared\Models\SnapshotModel;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

final class ModelLifecycleArchitectureTest extends TestCase
{
    #[DataProvider('transactionModels')]
    public function test_transaction_models_never_use_soft_deletes(string $model): void
    {
        self::assertTrue(is_subclass_of($model, TransactionModel::class));
        self::assertNotContains(SoftDeletes::class, class_uses_recursive($model));
    }

    public function test_stock_balance_is_a_snapshot_without_soft_deletes(): void
    {
        self::assertTrue(is_subclass_of(StockBalance::class, SnapshotModel::class));
        self::assertNotContains(SoftDeletes::class, class_uses_recursive(StockBalance::class));
    }

    public static function transactionModels(): array
    {
        return [
            [Document::class],
            [StockMovement::class],
            [Reservation::class],
            [StockRequest::class],
            [MaintenanceRequest::class],
            [WorkOrder::class],
            [PurchaseRequest::class],
            [PurchaseOrder::class],
        ];
    }
}
