@php
    $selectedUptdContributor = request()->attributes->get('uptd_contributor');
    $authUser = auth()->user();
    $profileUsername = $selectedUptdContributor?->username ?? $authUser?->username;
    $profileAccount = $selectedUptdContributor?->username ?? $authUser?->email ?? $authUser?->username;
@endphp

<header
    class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-4 lg:px-8">

    {{-- TOMBOL SIDEBAR --}}
    <button type="button" data-sidebar-toggle aria-controls="app-sidebar" aria-expanded="true"
        aria-label="Sembunyikan menu" title="Sembunyikan menu"
        class="absolute left-0 top-full z-20 flex h-8 w-8 -translate-x-1/2 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-[#018F98] shadow-sm transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/50">
        <x-heroicon-o-arrow-left data-sidebar-icon-collapse class="h-4 w-4" />
        <x-heroicon-o-arrow-right data-sidebar-icon-expand class="hidden h-4 w-4" />
    </button>

    {{-- JUDUL HALAMAN --}}
    <div class="min-w-0">
        <h1 class="truncate text-2xl font-semibold text-slate-900">
            {{ $activeMenuLabel }}
        </h1>
    </div>

    @auth
        <div class="flex shrink-0 items-center gap-3">

            {{-- IKON NOTIFIKASI --}}
            @if (auth()->user()->role?->canReceiveNotifications())
                <details class="relative" data-notification-menu data-notification-feed-url="{{ route('notifications.feed') }}">
                    <summary aria-label="Notifikasi" title="Notifikasi"
                        class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full border border-slate-200 bg-white text-[#018F98] transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/50 [&::-webkit-details-marker]:hidden">
                        <x-heroicon-o-bell class="h-5 w-5" />

                        <span data-notification-badge @class([
                            'absolute -right-1 -top-1 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white',
                            'hidden' => $unreadNotificationCount === 0,
                        ])>
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    </summary>

                    <div
                        class="absolute right-0 z-20 mt-2 w-80 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                        <div data-notification-content>
                            @include('layouts.partials.notification-menu-content', [
                                'notifications' => $notifications,
                                'unreadNotificationCount' => $unreadNotificationCount,
                            ])
                        </div>

                        <div class="border-t border-slate-100" data-notification-footer>
                            <a href="{{ route('notifications.index') }}"
                                class="block px-4 py-3 text-center text-xs font-semibold text-[#018F98] transition hover:bg-slate-50">
                                Lihat semua pemberitahuan
                            </a>
                        </div>
                    </div>
                </details>
            @endif

            {{-- IKON PROFIL TANPA NAMA DAN KOLOM --}}
            <details class="relative" data-profile-menu>
                <summary aria-label="Menu profil" title="Profil"
                    class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full bg-[#018F98] text-white shadow-sm transition hover:bg-[#01777E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/50 [&::-webkit-details-marker]:hidden">
                    <x-heroicon-o-user-circle class="h-6 w-6" />
                </summary>

                {{-- MENU LOGOUT --}}
                <div class="absolute right-0 z-20 mt-2 w-64 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">
                        Masuk sebagai
                    </p>

                    <p class="mt-1 truncate text-sm font-medium text-slate-900">
                        {{ $profileAccount }}
                    </p>

                    <form method="POST" action="{{ route('logout') }}" class="mt-4">
                        @csrf

                        <button type="submit"
                            class="w-full cursor-pointer rounded-md bg-[#018F98] px-3 py-2 text-sm font-medium text-white transition hover:bg-[#018F98]/90">
                            Logout
                        </button>
                    </form>
                </div>
            </details>

        </div>
    @endauth

</header>