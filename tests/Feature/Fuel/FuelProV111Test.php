<?php

declare(strict_types=1);
namespace Tests\Feature\Fuel;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
final class FuelProV111Test extends TestCase
{
    #[Test] public function fuel_pro_services_and_widgets_are_available(): void
    {
        self::assertTrue(class_exists(\App\Modules\Fuel\Application\Services\FuelAlertService::class));
        self::assertTrue(class_exists(\App\Modules\Fuel\Domain\Models\FuelAlert::class));
        self::assertTrue(class_exists(\App\Filament\Widgets\FuelOverview::class));
        self::assertTrue(class_exists(\App\Filament\Widgets\FuelConsumptionChart::class));
    }
    #[Test] public function fuel_transaction_service_contains_operational_guards(): void
    {
        $source=file_get_contents(app_path('Modules/Fuel/Application/Services/FuelTransactionService.php'));
        self::assertStringContainsString('Possível abastecimento duplicado', $source);
        self::assertStringContainsString('Combustível incompatível', $source);
        self::assertStringContainsString('leitura atual não pode ser menor', mb_strtolower($source));
    }
}
