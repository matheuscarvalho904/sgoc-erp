<?php

declare(strict_types=1);

namespace App\Modules\Fuel\Domain\Models;

use App\Shared\Models\BaseModel;

final class FuelPump extends BaseModel
{
    protected $table = 'fuel.pumps';
    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + ['current_meter'=>'decimal:3'];
    }
}
