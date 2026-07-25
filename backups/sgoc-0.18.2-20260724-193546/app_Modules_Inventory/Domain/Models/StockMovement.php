<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Catalog\Domain\Models\Product;
use App\Shared\Models\TransactionModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockMovement extends TransactionModel
{
    protected $table = 'inventory.stock_movements';

    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + [
            'occurred_at' => 'immutable_datetime',
            'expires_at' => 'date',
            'quantity' => 'decimal:4',
            'unit_cost' => 'decimal:4',
            'total_cost' => 'decimal:4',
            'metadata' => 'array',
        ];
    }

    public function warehouse(): BelongsTo
    {
        return $this->belongsTo(Warehouse::class, 'warehouse_id');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(InventoryLocation::class, 'location_id');
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    protected static function booted(): void
    {
        static::saving(function (self $movement): void {
            $movement->total_cost = bcmul(
                (string) ($movement->quantity ?? '0'),
                (string) ($movement->unit_cost ?? '0'),
                4,
            );
        });
    }
}
