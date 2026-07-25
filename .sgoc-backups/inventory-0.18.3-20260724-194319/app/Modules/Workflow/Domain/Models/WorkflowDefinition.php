<?php
declare(strict_types=1);
namespace App\Modules\Workflow\Domain\Models;
use App\Shared\Models\BaseModel;
final class WorkflowDefinition extends BaseModel { protected $table='workflow.definitions'; protected $guarded=[]; protected function casts(): array { return [...parent::casts(),'settings'=>'array','conditions'=>'array','metadata'=>'array','started_at'=>'datetime','completed_at'=>'datetime','decided_at'=>'datetime']; } }
