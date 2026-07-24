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
    <div class="sgoc-current-company">
        <span class="sgoc-current-company__label">Empresa atual</span>
        <span class="sgoc-current-company__name">{{ $companyName }}</span>
    </div>
@endif
