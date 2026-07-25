<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Domain\Models;

use App\Shared\Models\TransactionModel;

final class Fueling extends TransactionModel
{
    protected $table = 'fuel.fuelings';
    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + ['fueled_at'=>'immutable_datetime','quantity_liters'=>'decimal:4','unit_cost'=>'decimal:6','total_cost'=>'decimal:4','meter_reading'=>'decimal:2','previous_meter_reading'=>'decimal:2','distance_or_hours'=>'decimal:2','calculated_consumption'=>'decimal:4','metadata'=>'array'];
    }
}
