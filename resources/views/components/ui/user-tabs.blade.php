@props(['active' => 'admin'])

@php
    $tabs = [
        ['key' => 'admin', 'label' => 'Admin', 'route' => 'admin.users.index', 'params' => ['role' => 'admin']],
        ['key' => 'kepala-dinas', 'label' => 'Kepala Dinas', 'route' => 'admin.users.index', 'params' => ['role' => 'kepala_dinas']],
        ['key' => 'distributor', 'label' => 'Distributor', 'route' => 'admin.distributors.index', 'params' => []],
        ['key' => 'uptd', 'label' => 'UPTD', 'route' => 'admin.users.index', 'params' => ['role' => 'uptd_pasar']],
    ];
@endphp

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <nav class="flex flex-wrap items-center gap-2" aria-label="Navigasi Pengguna">
        @foreach ($tabs as $tab)
            <a
                href="{{ route($tab['route'], $tab['params']) }}"
                @if ($active === $tab['key']) aria-current="page" @endif
                @class([
                    'rounded-lg px-4 py-2 text-sm font-semibold transition',
                    'bg-[#018F98] text-white' => $active === $tab['key'],
                    'bg-white text-slate-600 ring-1 ring-slate-200 hover:bg-slate-50' => $active !== $tab['key'],
                ])
            >
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>

    @if (trim((string) $slot) !== '')
        <div class="flex shrink-0 items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
