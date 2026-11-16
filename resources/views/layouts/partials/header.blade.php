<header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-4 lg:px-8">
    <button
        type="button"
        data-sidebar-toggle
        aria-controls="app-sidebar"
        aria-expanded="true"
        aria-label="Sembunyikan menu"
        title="Sembunyikan menu"
        class="absolute left-0 top-full z-20 flex h-8 w-8 -translate-x-1/2 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full border border-slate-200 bg-white text-[#018F98] shadow-sm transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/50"
    >
        <x-heroicon-o-arrow-left data-sidebar-icon-collapse class="h-4 w-4" />
        <x-heroicon-o-arrow-right data-sidebar-icon-expand class="hidden h-4 w-4" />
    </button>

    <div class="min-w-0">
        <h1 class="truncate text-2xl font-semibold text-slate-900">
            {{ $activeMenuLabel }}
        </h1>
    </div>

    @auth
        <div class="flex shrink-0 items-center gap-3">
            @if (auth()->user()->role?->canReceiveNotifications())
                @php
                    $notifications = auth()->user()->notifications()->latest()->take(5)->get();
                    $unreadNotificationCount = auth()->user()->unreadNotifications()->count();
                @endphp

                <details class="relative" data-notification-menu>
                <summary
                    aria-label="Notifikasi"
                    class="relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-[#018F98] transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/50 [&::-webkit-details-marker]:hidden"
                >
                    <x-heroicon-o-bell class="h-5 w-5" />

                    @if ($unreadNotificationCount > 0)
                        <span class="absolute -right-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-red-500 px-1 text-[10px] font-bold text-white">
                            {{ $unreadNotificationCount > 9 ? '9+' : $unreadNotificationCount }}
                        </span>
                    @endif
                </summary>

                <div class="absolute right-0 z-20 mt-2 w-80 overflow-hidden rounded-lg border border-slate-200 bg-white shadow-lg">
                    <div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
                        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Notifikasi</p>

                        @if ($unreadNotificationCount > 0)
                            <form method="POST" action="{{ route('notifications.read-all') }}">
                                @csrf
                                <button
                                    type="submit"
                                    class="cursor-pointer text-xs font-semibold text-[#018F98] transition hover:underline"
                                >
                                    Tandai semua dibaca
                                </button>
                            </form>
                        @endif
                    </div>

                    <div class="max-h-80 overflow-y-auto">
                        @forelse ($notifications as $notification)
                            <div @class([
                                'border-b border-slate-100 px-4 py-3 last:border-b-0',
                                'bg-[#E0F2F3]/60' => $notification->read_at === null,
                            ])>
                                <p class="text-sm leading-snug text-slate-700">
                                    {{ data_get($notification->data, 'message', 'Notifikasi baru') }}
                                </p>
                                <p class="mt-1 text-[11px] text-slate-400">
                                    {{ $notification->created_at?->diffForHumans() }}
                                </p>
                            </div>
                        @empty
                            <p class="px-4 py-5 text-sm text-slate-500">Belum ada notifikasi</p>
                        @endforelse
                    </div>
                </div>
            </details>

            @endif

            <details class="relative" data-profile-menu>
            <summary
                aria-label="Menu profil"
                class="flex cursor-pointer list-none items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#018F98]/50 [&::-webkit-details-marker]:hidden"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#018F98] text-sm font-semibold text-white">
                    {{ auth()->user()->initials() }}
                </span>
                <span class="hidden min-w-0 text-left sm:block">
                    <span class="block max-w-[9rem] truncate text-sm font-semibold text-slate-900">{{ auth()->user()->username }}</span>
                    <span class="block max-w-[9rem] truncate text-xs text-slate-500">{{ auth()->user()->role->label() }}</span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </summary>

            <div class="absolute right-0 z-20 mt-2 w-64 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Masuk sebagai</p>
                <p class="mt-1 truncate text-sm font-medium text-slate-900">{{ auth()->user()->email ?? auth()->user()->username }}</p>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button
                        type="submit"
                        class="w-full cursor-pointer rounded-md bg-[#018F98] px-3 py-2 text-sm font-medium text-white transition hover:bg-[#018F98]/90"
                    >
                        Logout
                    </button>
                </form>
            </div>
        </details>
        </div>
    @endauth
</header>
