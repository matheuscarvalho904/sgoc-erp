<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
final class SgocModuleThreeSeeder extends Seeder { public function run(): void { $this->call([MaintenanceFoundationSeeder::class]); } }
