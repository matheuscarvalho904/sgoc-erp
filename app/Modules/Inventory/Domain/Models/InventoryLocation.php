<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel;
final class InventoryLocation extends BaseModel { protected $table='inventory.locations'; protected $guarded=[]; }
