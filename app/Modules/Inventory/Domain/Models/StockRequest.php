<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel; use Illuminate\Database\Eloquent\Relations\{BelongsTo,HasMany};
final class StockRequest extends BaseModel { protected $table='inventory.stock_requests'; protected $guarded=[]; protected function casts():array{return parent::casts()+['requested_at'=>'immutable_datetime','approved_at'=>'immutable_datetime','fulfilled_at'=>'immutable_datetime'];} public function warehouse():BelongsTo{return $this->belongsTo(Warehouse::class,'warehouse_id');} public function items():HasMany{return $this->hasMany(StockRequestItem::class,'stock_request_id');} }
