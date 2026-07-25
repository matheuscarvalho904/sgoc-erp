<?php
declare(strict_types=1);
namespace App\Modules\Assets\Domain\Models;
use App\Shared\Models\BaseModel;
final class AssetCategory extends BaseModel { protected $table='assets.asset_categories'; protected $guarded=[]; }
