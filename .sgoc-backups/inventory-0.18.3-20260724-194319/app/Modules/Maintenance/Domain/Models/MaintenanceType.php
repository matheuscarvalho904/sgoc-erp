<?php
declare(strict_types=1);
namespace App\Modules\Maintenance\Domain\Models;
use App\Shared\Models\BaseModel;
final class MaintenanceType extends BaseModel { protected $table='maintenance.maintenance_types'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'is_preventive'=>'boolean','requires_approval'=>'boolean']; } }
