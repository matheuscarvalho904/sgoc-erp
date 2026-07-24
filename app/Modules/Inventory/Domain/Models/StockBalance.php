<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel; use App\Modules\Catalog\Domain\Models\Product; use Illuminate\Database\Eloquent\Relations\BelongsTo;
final class StockBalance extends BaseModel { protected $table='inventory.stock_balances'; protected $guarded=[]; public $timestamps=true; public function warehouse():BelongsTo{return $this->belongsTo(Warehouse::class,'warehouse_id');} public function product():BelongsTo{return $this->belongsTo(Product::class,'product_id');} }
