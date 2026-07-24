<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Commercial\Domain\Models\PaymentTerm;
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class PaymentTermSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['AV', 'À vista', 1, 0, 0, true], ['7D', '7 dias', 1, 7, 0, false],
            ['10D', '10 dias', 1, 10, 0, false], ['15D', '15 dias', 1, 15, 0, false],
            ['21D', '21 dias', 1, 21, 0, false], ['28D', '28 dias', 1, 28, 0, false],
            ['30D', '30 dias', 1, 30, 0, false], ['45D', '45 dias', 1, 45, 0, false],
            ['60D', '60 dias', 1, 60, 0, false], ['30-60', '30/60 dias', 2, 30, 30, false],
            ['30-60-90', '30/60/90 dias', 3, 30, 30, false], ['30-60-90-120', '30/60/90/120 dias', 4, 30, 30, false],
            ['ENT-30', 'Entrada + 30 dias', 2, 0, 30, false], ['ENT-30-60', 'Entrada + 30/60 dias', 3, 0, 30, false],
        ];

        foreach (Tenant::query()->get() as $tenant) {
            foreach ($records as [$code, $name, $installments, $firstDue, $interval, $cash]) {
                PaymentTerm::withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $code],
                    ['name' => $name, 'installments' => $installments, 'first_due_days' => $firstDue, 'interval_days' => $interval, 'is_cash' => $cash, 'status' => 'active', 'deleted_at' => null],
                );
            }
        }
    }
}
