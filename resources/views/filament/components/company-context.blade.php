@php
    $company = null;
    $work = null;

    try {
        $company = \App\Modules\Foundation\Domain\Models\Company::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->first();

        $work = \App\Modules\Foundation\Domain\Models\Work::query()
            ->whereIn('status', ['planning', 'mobilizing', 'in_progress'])
            ->orderByRaw("CASE WHEN status = 'in_progress' THEN 0 ELSE 1 END")
            ->orderBy('name')
            ->first();
    } catch (\Throwable) {
        $company = null;
        $work = null;
    }
@endphp

<div class="sgoc-context-bar">
    @if ($company)
        <button type="button" class="sgoc-context-item" title="Empresa ativa">
            <span class="sgoc-context-item__icon">
                <x-filament::icon icon="heroicon-o-building-office-2" />
            </span>

            <span class="sgoc-context-item__content">
                <small>Empresa</small>
                <strong>{{ $company->name }}</strong>
            </span>

            <x-filament::icon icon="heroicon-m-chevron-down" class="sgoc-context-item__chevron" />
        </button>
    @endif

    @if ($work)
        <button type="button" class="sgoc-context-item sgoc-context-item--work" title="Obra ativa">
            <span class="sgoc-context-item__icon">
                <x-filament::icon icon="heroicon-o-building-office" />
            </span>

            <span class="sgoc-context-item__content">
                <small>Obra</small>
                <strong>{{ $work->name }}</strong>
            </span>

            <x-filament::icon icon="heroicon-m-chevron-down" class="sgoc-context-item__chevron" />
        </button>
    @endif
</div>
