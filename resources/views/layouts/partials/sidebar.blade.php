<aside class="no-scrollbar flex h-full w-64 shrink-0 flex-col overflow-y-auto border-r border-white/15 bg-[#018F98]">
    <div class="px-6 py-6">
        <div class="flex w-full items-center justify-center">
            <img
                src="{{ asset('images/mipta-logo.png') }}"
                alt="MIPTA"
                class="block shrink-0 object-contain drop-shadow-[0_2px_4px_rgba(0,0,0,0.35)]"
                style="width: 100px; height: 100px;"
            >
        </div>
    </div>

    <nav class="flex-1 px-3 pb-6" aria-label="Menu utama">
        <ul class="flex flex-col gap-2.5">
            @foreach ($menus as $menu)
                @php($isActive = request()->routeIs(...($menu['active'] ?? [$menu['route']])))
                <li>
                    <a
                        href="{{ route($menu['route']) }}"
                        @if ($isActive) aria-current="page" @endif
                        @class([
                            'flex items-center gap-2.5 rounded-md px-4 py-3 text-sm transition',
                            'bg-[#FDBA12]/40 font-semibold text-white' => $isActive,
                            'text-[#E6F7F8] hover:bg-[#FDBA12]/40 hover:font-semibold hover:text-white' => ! $isActive,
                        ])
                    >
                        <x-dynamic-component
                            :component="'heroicon-o-'.$menu['icon']"
                            class="h-6 w-6 shrink-0"
                            data-icon="{{ $menu['icon'] }}"
                        />
                        <span class="min-w-0 truncate">{{ $menu['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    <div class="px-3" style="padding-bottom: 0;">
        <div class="rounded-lg p-5">
            <div class="flex items-start gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#FDBA12]/20 text-[#FDBA12]">
                    <x-heroicon-o-shield-check class="h-5 w-5" />
                </span>
                <div class="min-w-0">
                    <p class="text-base font-semibold text-white">Pusat Bantuan</p>
                    <a
                        href="mailto:husain@hayoo.com"
                        class="inline-flex items-center py-1 text-sm font-semibold text-[#FDBA12] transition hover:underline"
                    >
                        Hubungi Admin
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div class="border-t border-white/15 px-3 py-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                class="flex w-full cursor-pointer items-center gap-3 rounded-md px-4 py-3 text-left text-sm font-medium text-[#E6F7F8] transition hover:bg-[#FDBA12]/40 hover:text-white"
            >
                <x-heroicon-o-arrow-right-on-rectangle class="h-6 w-6 shrink-0" />
                Logout
            </button>
        </form>
    </div>
</aside>
