<?php

declare(strict_types=1);

namespace App\Modules\Catalog\Domain\Models;

use App\Shared\Models\BaseModel;

final class ProductClass extends BaseModel
{
    protected $table = 'catalog.product_classes';

    protected $fillable = ['tenant_id','code','name','description','controls_stock','requires_lot','requires_expiration','requires_asset','allows_purchase','allows_sale','allows_os_consumption','allows_fueling','generates_depreciation','controls_serial_number','status'];

    protected function casts(): array
    {
        return [...parent::casts(),'controls_stock'=>'boolean','requires_lot'=>'boolean','requires_expiration'=>'boolean','requires_asset'=>'boolean','allows_purchase'=>'boolean','allows_sale'=>'boolean','allows_os_consumption'=>'boolean','allows_fueling'=>'boolean','generates_depreciation'=>'boolean','controls_serial_number'=>'boolean'];
    }
}
