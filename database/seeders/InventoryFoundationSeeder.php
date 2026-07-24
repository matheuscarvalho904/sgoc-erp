<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder; use Illuminate\Support\Facades\DB; use Illuminate\Support\Str;
final class InventoryFoundationSeeder extends Seeder { public function run(): void { $now=now(); foreach ([['GERAL','Almoxarifado Geral','general'],['OFICINA','Almoxarifado da Oficina','workshop']] as [$code,$name,$type]) { if (! DB::table('inventory.warehouses')->where('code',$code)->exists()) DB::table('inventory.warehouses')->insert(['id'=>(string)Str::uuid(),'code'=>$code,'name'=>$name,'type'=>$type,'status'=>'active','allows_negative_stock'=>false,'created_at'=>$now,'updated_at'=>$now]); } } }
