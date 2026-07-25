<?php

declare(strict_types=1);

namespace Tests\Unit\Inventory;

use App\Modules\Inventory\Application\Services\InventoryReservationService;
use App\Modules\Inventory\Application\Services\InventoryTransferService;
use PHPUnit\Framework\TestCase;

final class ReservationArchitectureTest extends TestCase
{
    public function test_inventory_services_are_final(): void
    {
        self::assertTrue((new \ReflectionClass(InventoryReservationService::class))->isFinal());
        self::assertTrue((new \ReflectionClass(InventoryTransferService::class))->isFinal());
    }
}
