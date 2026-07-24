@php
    $companyName = null;

    try {
        $companyName = \App\Modules\Foundation\Domain\Models\Company::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->value('name');
    } catch (\Throwable) {
        $companyName = null;
    }
@endphp

@if (filled($companyName))
    <div class="sgoc-company-right" title="{{ $companyName }}">
        <div class="sgoc-company-right__icon">
            <x-filament::icon icon="heroicon-o-building-office-2" />
        </div>

        <div class="sgoc-company-right__text">
            <span class="sgoc-company-right__label">Empresa</span>
            <strong class="sgoc-company-right__name">{{ $companyName }}</strong>
        </div>
    </div>
@endif
