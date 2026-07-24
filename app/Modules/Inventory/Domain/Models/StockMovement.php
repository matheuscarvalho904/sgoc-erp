<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel; use App\Modules\Catalog\Domain\Models\Product; use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class StockMovement extends BaseModel { protected $table='inventory.stock_movements'; protected $guarded=[]; protected function casts():array{return parent::casts()+['occurred_at'=>'immutable_datetime','expires_at'=>'date'];} public function warehouse():BelongsTo{return $this->belongsTo(Warehouse::class,'warehouse_id');} public function product():BelongsTo{return $this->belongsTo(Product::class,'product_id');} protected static function booted():void{static::saving(function(self $m):void{$m->total_cost=(float)$m->quantity*(float)$m->unit_cost;});} }
