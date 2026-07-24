<x-filament-panels::page.simple>
    <div class="sgoc-saas-login-shell">
        <section class="sgoc-saas-login-brand">
            <div class="sgoc-saas-login-brand__top">
                <img
                    src="{{ asset('images/sgoc-logo.svg') }}"
                    alt="SGOC ERP"
                    class="sgoc-saas-login-brand__logo"
                />
            </div>

            <div class="sgoc-saas-login-brand__content">
                <p class="sgoc-saas-login-brand__eyebrow">Bem-vindo ao</p>

                <h1 class="sgoc-saas-login-brand__title">
                    SGOC <span>ERP</span>
                </h1>

                <p class="sgoc-saas-login-brand__description">
                    Plataforma completa para gestão empresarial de obras,
                    projetos e infraestrutura.
                </p>

                <div class="sgoc-saas-login-benefits">
                    <div class="sgoc-saas-login-benefit">
                        <x-filament::icon icon="heroicon-o-shield-check" />
                        <div>
                            <strong>Segurança avançada</strong>
                            <span>Dados protegidos com controle por perfis e permissões.</span>
                        </div>
                    </div>

                    <div class="sgoc-saas-login-benefit">
                        <x-filament::icon icon="heroicon-o-cloud" />
                        <div>
                            <strong>Acesso de qualquer lugar</strong>
                            <span>Ambiente disponível para sua equipe em dispositivos autorizados.</span>
                        </div>
                    </div>

                    <div class="sgoc-saas-login-benefit">
                        <x-filament::icon icon="heroicon-o-chart-pie" />
                        <div>
                            <strong>Gestão integrada</strong>
                            <span>Empresas, filiais, obras e módulos conectados.</span>
                        </div>
                    </div>

                    <div class="sgoc-saas-login-benefit">
                        <x-filament::icon icon="heroicon-o-lifebuoy" />
                        <div>
                            <strong>Suporte especializado</strong>
                            <span>Estrutura preparada para acompanhar sua operação.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="sgoc-saas-login-brand__footer">
                <span>© {{ now()->year }} SGOC ERP. Todos os direitos reservados.</span>
                <span>Versão 1.0.0</span>
            </div>
        </section>

        <section class="sgoc-saas-login-form-area">
            <div class="sgoc-saas-login-form-card">
                <header class="sgoc-saas-login-form-card__header">
                    <h2>{{ $this->getHeading() }}</h2>
                    <p>{{ $this->getSubheading() }}</p>
                </header>

                <form wire:submit="authenticate" class="sgoc-login-form">
                    {{ $this->form }}

                    <x-filament::button
                        type="submit"
                        color="primary"
                        size="lg"
                        class="sgoc-login-submit"
                    >
                        Entrar no sistema
                    </x-filament::button>
                </form>
            </div>

            <div class="sgoc-saas-login-security">
                <div>
                    <x-filament::icon icon="heroicon-o-shield-check" />
                    <span><strong>SSL Seguro</strong>Conexão protegida</span>
                </div>

                <div>
                    <x-filament::icon icon="heroicon-o-lock-closed" />
                    <span><strong>LGPD</strong>Dados protegidos</span>
                </div>

                <div>
                    <x-filament::icon icon="heroicon-o-cloud-arrow-up" />
                    <span><strong>Backup</strong>Ambiente monitorado</span>
                </div>
            </div>
        </section>
    </div>
</x-filament-panels::page.simple>
