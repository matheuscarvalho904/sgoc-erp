<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class PurchaseCategory extends BaseModel
{
    protected $table = 'purchasing.purchase_categories';

    protected $fillable = ['tenant_id', 'code', 'name', 'description', 'status'];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
