<?php

declare(strict_types=1);

namespace App\Filament\Resources\Assets\Pages;

use App\Filament\Resources\Assets\AssetResource;
use App\Modules\Assets\Domain\Models\AssetPrefix;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\DB;

final class CreateAsset extends CreateRecord
{
    protected static string $resource = AssetResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        if (filled($data['code'] ?? null) || blank($data['asset_prefix_id'] ?? null)) return $data;

        return DB::transaction(function () use ($data): array {
            $prefix = AssetPrefix::query()->lockForUpdate()->findOrFail($data['asset_prefix_id']);
            $number = (int) $prefix->next_number;
            $data['prefix_number'] = $number;
            $data['code'] = $prefix->code . '-' . str_pad((string) $number, (int) $prefix->digits, '0', STR_PAD_LEFT);
            $prefix->update(['next_number' => $number + 1]);
            return $data;
        });
    }
}
