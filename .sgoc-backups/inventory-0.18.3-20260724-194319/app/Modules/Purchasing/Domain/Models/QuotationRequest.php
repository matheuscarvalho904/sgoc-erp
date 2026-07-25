<?php

declare(strict_types=1);

namespace App\Modules\Purchasing\Domain\Models;

use App\Models\User;
use App\Modules\Foundation\Domain\Models\Tenant;
use App\Shared\Models\BaseModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

final class QuotationRequest extends BaseModel
{
    protected $table = 'purchasing.quotation_requests';
    protected $fillable = ['tenant_id','purchase_request_id','number','issued_at','response_deadline','status','notes','created_by','closed_at'];
    protected function casts(): array { return [...parent::casts(),'issued_at'=>'date','response_deadline'=>'date','closed_at'=>'immutable_datetime']; }
    public function tenant(): BelongsTo { return $this->belongsTo(Tenant::class); }
    public function purchaseRequest(): BelongsTo { return $this->belongsTo(PurchaseRequest::class); }
    public function creator(): BelongsTo { return $this->belongsTo(User::class, 'created_by'); }
    public function suppliers(): HasMany { return $this->hasMany(QuotationSupplier::class); }
}
