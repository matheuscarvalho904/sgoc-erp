<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Domain\Models;

use App\Shared\Models\TransactionModel;

final class FuelEntry extends TransactionModel
{
    protected $table = 'fuel.entries';
    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + ['invoice_date'=>'date','received_at'=>'immutable_datetime','quantity_liters'=>'decimal:4','unit_cost'=>'decimal:6','total_cost'=>'decimal:4','metadata'=>'array'];
    }
}
