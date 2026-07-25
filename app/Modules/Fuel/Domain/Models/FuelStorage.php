<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Domain\Models;

use App\Shared\Models\BaseModel;

final class FuelStorage extends BaseModel
{
    protected $table = 'fuel.storages';
    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + ['settings'=>'array','capacity_liters'=>'decimal:4','minimum_level_liters'=>'decimal:4'];
    }
}
