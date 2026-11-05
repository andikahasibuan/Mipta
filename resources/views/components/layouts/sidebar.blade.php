@props(['menus'])

<aside class="flex h-full w-64 shrink-0 flex-col overflow-y-auto border-r border-blue-200 bg-[linear-gradient(to_bottom,#5191E4_3%,#2D507E_13%)]">
    <div class="px-6 py-6">
        <div class="mx-auto w-30">
            <img
                src="{{ asset('images/mipta-logo.png') }}"
                alt="MIPTA"
                class="block h-auto w-full drop-shadow-[0_2px_4px_rgba(0,0,0,0.35)]"
            >
        </div>
        <div class="mt-4 flex items-center gap-3 rounded-lg bg-[#0B2559]/40 px-3 py-2 ring-1 ring-white/10">
            <img src="{{ asset('images/logo.svg') }}"
                alt="Logo Kota Padang"
                width="36"
                height="36"
                class="h-9 w-9 shrink-0 object-contain">
            <div class="min-w-0 leading-tight">
                <p class="truncate text-[10px] uppercase tracking-wide text-[#A9C0D6]">Dinas Perdagangan</p>
                <p class="truncate text-sm font-semibold text-white">Kota Padang</p>
            </div>
        </div>
    </div>

    <nav class="flex-1 px-3 pb-6" aria-label="Menu utama">
        <ul class="flex flex-col gap-1">
            @foreach ($menus as $menu)
                <li>
                    <a
                        href="{{ route($menu['route']) }}"
                        @if (request()->routeIs(...($menu['active'] ?? [$menu['route']]))) aria-current="page" @endif
                        @class([
                            'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition',
                            'bg-[#FDBA12]/40 font-semibold text-white' => request()->routeIs(...($menu['active'] ?? [$menu['route']])),
                            'text-[#A9C0D6] hover:bg-[#FDBA12]/40 hover:font-semibold hover:text-white' => ! request()->routeIs(...($menu['active'] ?? [$menu['route']])),
                        ])
                    >
                        <x-dynamic-component
                            :component="'heroicon-o-'.$menu['icon']"
                            class="h-5 w-5 shrink-0"
                            data-icon="{{ $menu['icon'] }}"
                        />
                        <span class="min-w-0 truncate">{{ $menu['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="border-t border-blue-200 px-3 py-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="w-full cursor-pointer rounded-md px-3 py-2 text-left text-sm font-medium text-[#A9C0D6] transition hover:bg-[#FDBA12]/40 hover:text-white"
            >
                Keluar dari Sistem
            </button>
        </form>
    </div>
</aside>
