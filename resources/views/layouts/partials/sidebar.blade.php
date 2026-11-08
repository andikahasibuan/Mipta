<aside class="flex h-full w-64 shrink-0 flex-col overflow-y-auto border-r border-blue-200 bg-[#2D507E]">
    <div class="px-6 py-6">
        <div class="flex items-center gap-3">
            <img
                src="{{ asset('images/mipta-logo.png') }}"
                alt="MIPTA"
                class="block h-14 w-14 shrink-0 object-contain drop-shadow-[0_2px_4px_rgba(0,0,0,0.35)]"
            >
            <div class="min-w-0 leading-tight">
                <p class="truncate text-2xl font-semibold tracking-wide text-white">MIPTA</p>
                <p class="truncate text-xs text-[#A9C0D6]">Monitoring Minyakita</p>
            </div>
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
        <p class="px-3 pb-2 pt-1 text-[10px] font-semibold uppercase tracking-widest text-[#A9C0D6]">
            Menu Utama
        </p>
        <ul class="flex flex-col gap-1">
            @foreach ($menus as $menu)
                @php($isActive = request()->routeIs(...($menu['active'] ?? [$menu['route']])))
                <li>
                    <a
                        href="{{ route($menu['route']) }}"
                        @if ($isActive) aria-current="page" @endif
                        @class([
                            'flex items-center gap-3 rounded-md px-3 py-2 text-sm transition',
                            'bg-[#FDBA12]/40 font-semibold text-white' => $isActive,
                            'text-[#A9C0D6] hover:bg-[#FDBA12]/40 hover:font-semibold hover:text-white' => ! $isActive,
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

    <div class="px-3 pb-8">
        <div class="rounded-lg bg-[#3E6BA5]/50 p-4 ring-1 ring-white/15">
            <div class="flex items-start gap-3">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg bg-[#FDBA12]/20 text-[#FDBA12]">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-sm font-semibold text-white">Pusat Bantuan</p>
                    <p class="mt-0.5 text-xs leading-snug text-[#A9C0D6]">Butuh bantuan pengguna sistem?</p>
                </div>
            </div>
            <a
                href="mailto:admin@mipta.test"
                class="mt-3 inline-block text-xs font-semibold text-[#FDBA12] transition hover:underline"
            >
                Hubungi Admin
            </a>
        </div>
    </div>

    <div class="border-t border-blue-200 px-3 py-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full cursor-pointer items-center gap-3 rounded-md px-3 py-2 text-left text-sm font-medium text-[#A9C0D6] transition hover:bg-[#FDBA12]/40 hover:text-white"
            >
                <x-heroicon-o-arrow-right-on-rectangle class="h-5 w-5 shrink-0" />
                Keluar dari Sistem
            </button>
        </form>
    </div>
</aside>
