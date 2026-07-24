<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseRequestItem extends BaseModel
{
    protected $table = 'purchasing.purchase_request_items';

    protected $fillable = [
        'tenant_id', 'purchase_request_id', 'sequence', 'description', 'specification',
        'unit', 'quantity', 'estimated_unit_price', 'notes', 'status',
    ];

    protected function casts(): array
    {
        return [...parent::casts(), 'quantity' => 'decimal:4', 'estimated_unit_price' => 'decimal:4', 'estimated_total' => 'decimal:4'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class, 'purchase_request_id'); }
}
