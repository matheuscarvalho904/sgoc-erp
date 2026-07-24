<?php

declare(strict_types=1);

namespace App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;

final class SaasLogin extends BaseLogin
{
    protected string $view = 'filament.pages.auth.saas-login';

    public function getHeading(): string | Htmlable
    {
        return 'Acesse sua conta';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Informe suas credenciais para continuar.';
    }

    public function getTitle(): string | Htmlable
    {
        return 'Entrar — SGOC ERP';
    }
}
