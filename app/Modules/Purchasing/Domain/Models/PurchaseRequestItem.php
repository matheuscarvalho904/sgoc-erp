<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Catalog\Domain\Models\{Product, Unit};
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseRequestItem extends BaseModel
{
    protected $table = 'purchasing.purchase_request_items';

    protected $fillable = [
        'tenant_id', 'purchase_request_id', 'sequence', 'item_type', 'product_id', 'unit_id',
        'description', 'service_description', 'specification', 'unit', 'quantity',
        'estimated_unit_price', 'application_type_id', 'application_target_id', 'application_label',
        'application_data', 'allocation_percentage', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [
            ...parent::casts(), 'quantity' => 'decimal:4', 'estimated_unit_price' => 'decimal:4',
            'estimated_total' => 'decimal:4', 'allocation_percentage' => 'decimal:4', 'application_data' => 'array',
        ];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class, 'product_id'); }
    public function measurementUnit(): BelongsTo { return $this->belongsTo(Unit::class, 'unit_id'); }
    public function applicationType(): BelongsTo { return $this->belongsTo(ApplicationType::class, 'application_type_id'); }
}
