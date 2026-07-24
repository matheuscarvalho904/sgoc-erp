<?php

declare(strict_types=1);

namespace Database\Seeders;

use Illuminate\Database\Seeder;

final class GeneralCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UnitSeeder::class,
            BrandSeeder::class,
            PaymentMethodSeeder::class,
            PaymentTermSeeder::class,
            ProductCategorySeeder::class,
            ProductSeeder::class,
        ]);
    }
}
