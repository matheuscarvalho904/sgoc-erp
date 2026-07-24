<?php
declare(strict_types=1);
namespace App\Modules\Assets\Domain\Models;
use App\Shared\Models\BaseModel;
final class Asset extends BaseModel {
 protected $table='assets.assets'; protected $guarded=[];
 protected function casts(): array { return [...parent::casts(),'settings'=>'array','acquisition_date'=>'date','warranty_until'=>'date','acquisition_value'=>'decimal:4','residual_value'=>'decimal:4','current_odometer'=>'decimal:2','current_hourmeter'=>'decimal:2']; }
}
