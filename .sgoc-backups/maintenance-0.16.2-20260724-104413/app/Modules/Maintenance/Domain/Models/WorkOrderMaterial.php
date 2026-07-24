<?php
declare(strict_types=1);
namespace App\Modules\Maintenance\Domain\Models;
use App\Shared\Models\BaseModel;
final class WorkOrderMaterial extends BaseModel { protected $table='maintenance.work_order_materials'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'quantity_requested'=>'decimal:4','quantity_applied'=>'decimal:4','unit_cost'=>'decimal:4','total_cost'=>'decimal:4']; } }
