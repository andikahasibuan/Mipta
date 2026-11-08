<header class="sticky top-0 z-10 flex items-center justify-between gap-4 border-b border-slate-200 bg-white px-6 py-5 lg:px-8">
    <div class="min-w-0">
        <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">
            Sistem Monitoring Distribusi
        </p>
        <h1 class="mt-1 truncate text-2xl font-semibold text-slate-900">
            {{ $activeMenuLabel }}
        </h1>
    </div>

    @auth
        <div class="flex shrink-0 items-center gap-3">
            <details class="relative" data-notification-menu>
                <summary
                    aria-label="Notifikasi"
                    class="flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-lg border border-slate-200 bg-white text-[#2D507E] transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50 [&::-webkit-details-marker]:hidden"
                >
                    <x-heroicon-o-bell class="h-5 w-5" />
                </summary>

                <div class="absolute right-0 z-20 mt-2 w-64 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Notifikasi</p>
                    <p class="mt-2 text-sm text-slate-500">Belum ada notifikasi</p>
                </div>
            </details>

            <details class="relative" data-profile-menu>
            <summary
                aria-label="Menu profil"
                class="flex cursor-pointer list-none items-center gap-3 rounded-lg border border-slate-200 bg-white px-3 py-2 transition hover:bg-slate-50 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50 [&::-webkit-details-marker]:hidden"
            >
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-[#2D507E] text-sm font-semibold text-white">
                    {{ auth()->user()->initials() }}
                </span>
                <span class="hidden min-w-0 text-left sm:block">
                    <span class="block max-w-[9rem] truncate text-sm font-semibold text-slate-900">{{ auth()->user()->name }}</span>
                    <span class="block max-w-[9rem] truncate text-xs text-slate-500">{{ auth()->user()->role->label() }}</span>
                </span>
                <svg class="h-4 w-4 shrink-0 text-slate-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.22 8.22a.75.75 0 0 1 1.06 0L10 11.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 9.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd" />
                </svg>
            </summary>

            <div class="absolute right-0 z-20 mt-2 w-64 rounded-lg border border-slate-200 bg-white p-4 shadow-lg">
                <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Masuk sebagai</p>
                <p class="mt-1 truncate text-sm font-medium text-slate-900">{{ auth()->user()->email }}</p>

                <form method="POST" action="{{ route('logout') }}" class="mt-4">
                    @csrf
                    <button
                        type="submit"
                        class="w-full cursor-pointer rounded-md bg-[#2D507E] px-3 py-2 text-sm font-medium text-white transition hover:bg-[#2D507E]/90"
                    >
                        Keluar
                    </button>
                </form>
            </div>
        </details>
        </div>
    @endauth
</header>
