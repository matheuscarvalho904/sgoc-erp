<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Commercial\Domain\Models\PaymentMethod;
use App\Modules\Foundation\Domain\Models\Tenant;
use Illuminate\Database\Seeder;

final class PaymentMethodSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            ['PIX', 'PIX', 'pix', true], ['BOL', 'Boleto bancário', 'boleto', true],
            ['TED', 'Transferência bancária', 'bank_transfer', true], ['DIN', 'Dinheiro', 'cash', false],
            ['CC', 'Cartão de crédito', 'credit_card', false], ['CD', 'Cartão de débito', 'debit_card', false],
            ['CHQ', 'Cheque', 'check', true], ['OUT', 'Outros', 'other', false],
        ];

        foreach (Tenant::query()->get() as $tenant) {
            foreach ($records as [$code, $name, $type, $bankData]) {
                PaymentMethod::withTrashed()->updateOrCreate(
                    ['tenant_id' => $tenant->id, 'code' => $code],
                    ['name' => $name, 'type' => $type, 'requires_bank_data' => $bankData, 'status' => 'active', 'deleted_at' => null],
                );
            }
        }
    }
}
