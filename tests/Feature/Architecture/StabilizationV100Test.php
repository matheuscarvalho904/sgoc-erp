<?php

declare(strict_types=1);

namespace Tests\Feature\Architecture;

use App\Support\Filament\BrazilianInput;
use Tests\TestCase;

final class StabilizationV100Test extends TestCase
{
    public function test_purchase_order_does_not_query_nonexistent_company_trade_name_column(): void
    {
        $path = app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php');

        self::assertFileExists($path);

        $resource = (string) file_get_contents($path);

        self::assertStringNotContainsString("Company::query()->pluck('trade_name'", $resource);
        self::assertStringNotContainsString("Company::query()->orderBy('trade_name')->pluck('trade_name'", $resource);
        self::assertStringContainsString("Company::query()->orderBy('name')->pluck('name'", $resource);
    }

    public function test_shared_brazilian_input_library_is_available(): void
    {
        self::assertTrue(class_exists(BrazilianInput::class));
    }
}
