<?php
 declare(strict_types=1);
 namespace App\Modules\Commercial\Domain\Models;
 use App\Shared\Models\BaseModel;
 final class PaymentTerm extends BaseModel { protected $table = 'commercial.payment_terms'; protected $fillable = ['tenant_id','code','name','installments','first_due_days','interval_days','is_cash','status']; }
