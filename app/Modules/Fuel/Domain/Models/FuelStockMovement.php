<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Domain\Models;

use App\Shared\Models\TransactionModel;

final class FuelStockMovement extends TransactionModel
{
    protected $table = 'fuel.stock_movements';
    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + ['quantity_liters'=>'decimal:4','unit_cost'=>'decimal:6','total_cost'=>'decimal:4','occurred_at'=>'immutable_datetime','metadata'=>'array'];
    }
}
