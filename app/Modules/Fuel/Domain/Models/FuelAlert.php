<?php

declare(strict_types=1);
namespace App\Modules\Fuel\Domain\Models;
use App\Shared\Models\TransactionModel;
final class FuelAlert extends TransactionModel
{
    protected $table = 'fuel.alerts';
    protected $guarded = [];
    protected function casts(): array
    {
        return parent::casts() + ['detected_at'=>'immutable_datetime','resolved_at'=>'immutable_datetime','metadata'=>'array'];
    }
}
