<?php

namespace App\Modules\Foundation\Application\Services;

use App\Modules\Foundation\Domain\Models\Tenant;
use RuntimeException;

final class CurrentTenant
{
    private ?Tenant $tenant = null;

    public function set(Tenant $tenant): void
    {
        $this->tenant = $tenant;
    }

    public function get(): Tenant
    {
        if ($this->tenant === null) {
            throw new RuntimeException('Nenhum tenant foi definido para o contexto atual.');
        }

        return $this->tenant;
    }

    public function id(): string
    {
        return $this->get()->getKey();
    }

    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    public function clear(): void
    {
        $this->tenant = null;
    }
}