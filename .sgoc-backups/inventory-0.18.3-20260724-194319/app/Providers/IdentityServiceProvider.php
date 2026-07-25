<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

final class IdentityServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::before(function ($user, string $ability): ?bool {
            $tenantId = request()->attributes->get('tenant_id');

            if (is_string($tenantId) && $user->isSuperAdmin($tenantId)) {
                return true;
            }

            return null;
        });
    }
}