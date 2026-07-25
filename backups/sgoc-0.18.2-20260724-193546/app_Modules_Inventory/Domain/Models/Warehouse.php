<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel; use Illuminate\Database\Eloquent\Relations\HasMany;
final class Warehouse extends BaseModel { protected $table='inventory.warehouses'; protected $guarded=[]; public function locations():HasMany{return $this->hasMany(InventoryLocation::class,'warehouse_id');} public function balances():HasMany{return $this->hasMany(StockBalance::class,'warehouse_id');} }
