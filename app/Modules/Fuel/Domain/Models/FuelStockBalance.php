<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Domain\Models;

use App\Shared\Models\SnapshotModel;

final class FuelStockBalance extends SnapshotModel
{
    protected $table = 'fuel.stock_balances';
    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + ['quantity_liters'=>'decimal:4','average_cost'=>'decimal:6','total_value'=>'decimal:4','last_movement_at'=>'immutable_datetime'];
    }
}
