<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Domain\Models;

use App\Modules\Catalog\Domain\Models\Product;
use App\Shared\Models\SnapshotModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class StockBalance extends SnapshotModel
{
    protected $table = 'inventory.stock_balances';

    protected $guarded = [];

    protected function casts(): array
    {
        return parent::casts() + [
            'expires_at' => 'date',
            'quantity_on_hand' => 'decimal:4',
            'quantity_reserved' => 'decimal:4',
            'average_cost' => 'decimal:4',
            'minimum_stock' => 'decimal:4',
            'maximum_stock' => 'decimal:4',
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

    public function getAvailableQuantityAttribute(): string
    {
        return bcsub(
            (string) ($this->quantity_on_hand ?? '0'),
            (string) ($this->quantity_reserved ?? '0'),
            4,
        );
    }
}
