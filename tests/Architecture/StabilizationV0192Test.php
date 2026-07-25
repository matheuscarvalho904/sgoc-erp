<?php

declare(strict_types=1);

it('does not query the non-existent company trade_name column', function (): void {
    $resource = file_get_contents(app_path('Filament/Resources/PurchaseOrders/PurchaseOrderResource.php'));

    expect($resource)
        ->not->toContain("Company::query()->pluck('trade_name'")
        ->toContain("Company::query()->orderBy('name')->pluck('name'");
});

it('provides the shared Brazilian input component library', function (): void {
    expect(class_exists(\App\Support\Filament\BrazilianInput::class))->toBeTrue();
});
