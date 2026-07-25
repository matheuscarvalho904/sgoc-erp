<?php

declare(strict_types=1);

namespace App\Filament\Resources\Permissions\Pages;

use App\Filament\Resources\Permissions\PermissionResource;
use Filament\Resources\Pages\EditRecord;

final class EditPermission extends EditRecord
{
    protected static string $resource = PermissionResource::class;
}
