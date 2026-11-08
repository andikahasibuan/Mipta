@props(['active' => 'users'])

@php
    $tabs = [
        ['key' => 'users', 'label' => 'Pengguna', 'route' => 'admin.users.index'],
        ['key' => 'distributors', 'label' => 'Distributor', 'route' => 'admin.distributors.index'],
        ['key' => 'markets', 'label' => 'Pasar', 'route' => 'admin.markets.index'],
        ['key' => 'stores', 'label' => 'Toko Penerima', 'route' => 'admin.recipient-stores.index'],
        ['key' => 'uptd', 'label' => 'Kontributor UPTD', 'route' => 'admin.uptd-contributors.index'],
    ];
@endphp

<div class="-mt-2 mb-4 min-w-0">
    <h2 class="text-2xl font-semibold text-[#1F3A5F]">Pengguna &amp; Data Master</h2>
    <p class="mt-1 text-sm text-[#2D507E]">Cari, filter, dan kelola data sistem secara terpusat</p>
</div>

<div class="mb-4 flex flex-wrap items-center justify-between gap-3">
    <nav class="flex flex-wrap items-center gap-2" aria-label="Navigasi Pengguna dan Data Master">
        @foreach ($tabs as $tab)
            <a
                href="{{ route($tab['route']) }}"
                @if ($active === $tab['key']) aria-current="page" @endif
                @class([
                    'rounded-md border px-4 py-2 text-sm transition',
                    'border-[#2D507E] bg-white font-semibold text-[#1F3A5F] shadow-sm' => $active === $tab['key'],
                    'border-transparent bg-[#DCE6F5] text-[#2D507E] hover:bg-[#CBD9EE]' => $active !== $tab['key'],
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
