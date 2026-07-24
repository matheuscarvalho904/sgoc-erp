<?php
namespace Database\Seeders;
use App\Modules\Maintenance\Domain\Models\MaintenancePriority;
use App\Modules\Maintenance\Domain\Models\MaintenanceType;
use Illuminate\Database\Seeder;
final class MaintenanceFoundationSeeder extends Seeder { public function run(): void {
 $tenant=\App\Modules\Foundation\Domain\Models\Tenant::query()->value('id');
 foreach ([['PREV','Preventiva',true,false],['CORR','Corretiva',false,true],['PRED','Preditiva',true,false],['INSP','Inspeção',true,false],['GAR','Garantia',false,true],['RECALL','Recall',false,false],['REF','Reforma',false,true],['LUB','Lubrificação',true,false]] as [$code,$name,$preventive,$approval]) MaintenanceType::query()->updateOrCreate(['tenant_id'=>$tenant,'code'=>$code],['name'=>$name,'is_preventive'=>$preventive,'requires_approval'=>$approval,'status'=>'active']);
 foreach ([['LOW','Baixa',1,168,'gray'],['NORMAL','Normal',2,72,'info'],['HIGH','Alta',3,24,'warning'],['URGENT','Urgente',4,8,'danger'],['EMERGENCY','Emergencial',5,2,'danger']] as [$code,$name,$level,$sla,$color]) MaintenancePriority::query()->updateOrCreate(['tenant_id'=>$tenant,'code'=>$code],['name'=>$name,'level'=>$level,'sla_hours'=>$sla,'color'=>$color,'status'=>'active']);
 }}
