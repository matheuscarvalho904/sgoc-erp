<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Modules\Foundation\Domain\Models\Organization;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

final class Supplier extends BaseModel
{
    protected $table = 'purchasing.suppliers';

    protected $fillable = [
        'tenant_id', 'organization_id', 'code', 'person_type', 'document', 'legal_name',
        'trade_name', 'state_registration', 'municipal_registration', 'email', 'phone',
        'zip_code', 'street', 'number', 'complement', 'district', 'city', 'state',
        'payment_notes', 'status', 'settings',
    ];

    protected function casts(): array
    {
        return [...parent::casts(), 'settings' => 'array'];
    }

    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }
}
