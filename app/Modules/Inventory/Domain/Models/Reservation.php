<?php
declare(strict_types=1);
namespace App\Modules\Inventory\Domain\Models;
use App\Shared\Models\BaseModel;
final class Reservation extends BaseModel { protected $table='inventory.reservations'; protected $guarded=[]; }
