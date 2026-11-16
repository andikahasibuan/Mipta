<aside id="app-sidebar" class="no-scrollbar flex h-full w-64 shrink-0 flex-col overflow-y-auto border-r border-white/15 bg-[#018F98]">
    <div data-sidebar-logo-wrapper class="px-6 py-6">
        <div class="flex w-full items-center justify-center">
            <img
                data-sidebar-logo
                src="{{ asset('images/logo-sidebar.png') }}"
                alt="MIPTA"
                class="block h-[120px] w-[120px] shrink-0 object-contain"
            >
        </div>
    </div>

    <nav data-sidebar-nav class="flex-1 px-3 pb-6" aria-label="Menu utama">
        <ul class="flex flex-col gap-2.5">
            @foreach ($menus as $menu)
                @php($isActive = request()->routeIs(...($menu['active'] ?? [$menu['route']])))
                <li>
                    <a
                        href="{{ route($menu['route']) }}"
                        @if ($isActive) aria-current="page" @endif
                        aria-label="{{ $menu['label'] }}"
                        title="{{ $menu['label'] }}"
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
                        <span data-sidebar-label class="min-w-0 truncate">{{ $menu['label'] }}</span>
                    </a>
                </li>
            @endforeach
        </ul>
    </nav>

    @unless (auth()->user()->role?->isAdmin())
        <div data-sidebar-help-section class="px-3" style="padding-bottom: 0;">
            <div data-sidebar-help class="rounded-lg p-5">
                <div data-sidebar-help-content class="flex items-start gap-3">
                    <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#FDBA12]/20 text-[#FDBA12]">
                        <x-heroicon-o-shield-check class="h-5 w-5" />
                    </span>
                    <div data-sidebar-help-text class="min-w-0">
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
    @endunless

    <div data-sidebar-logout-section class="border-t border-white/15 px-3 py-4">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button
                type="submit"
                aria-label="Logout"
                title="Logout"
                class="flex w-full cursor-pointer items-center gap-3 rounded-md px-4 py-3 text-left text-sm font-medium text-[#E6F7F8] transition hover:bg-[#FDBA12]/40 hover:text-white"
            >
                <x-heroicon-o-arrow-right-on-rectangle class="h-6 w-6 shrink-0" />
                <span data-sidebar-label>Logout</span>
            </button>
        </form>
    </div>
</aside>
