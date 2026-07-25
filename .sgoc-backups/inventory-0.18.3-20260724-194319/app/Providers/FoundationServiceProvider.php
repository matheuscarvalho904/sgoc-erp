<?php

namespace App\Providers;

use App\Modules\Foundation\Application\Services\CurrentTenant;
use Illuminate\Support\ServiceProvider;

final class FoundationServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(CurrentTenant::class);
    }

    public function boot(): void
    {
        //
    }
}