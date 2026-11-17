<div class="flex items-center justify-between gap-3 border-b border-slate-100 px-4 py-3">
    <p class="text-[11px] font-semibold uppercase tracking-widest text-slate-400">Notifikasi</p>

    @if ($unreadNotificationCount > 0)
        <form method="POST" action="{{ route('notifications.read-all') }}">
            @csrf
            <button
                type="submit"
                class="cursor-pointer text-xs font-semibold text-[#018F98] transition hover:underline"
            >
                Tandai semua telah dibaca
            </button>
        </form>
    @endif
</div>

<div class="max-h-80 overflow-y-auto">
    @forelse ($notifications as $notification)
        <form
            method="POST"
            action="{{ route('notifications.read', $notification) }}"
            data-notification-item
            @class([
                'block border-b border-slate-100 last:border-b-0',
                'bg-[#E0F2F3]/60' => $notification->read_at === null,
            ])
        >
            @csrf
            <button
                type="submit"
                class="block w-full cursor-pointer px-4 py-3 text-left transition hover:bg-slate-50"
            >
                <p class="text-sm font-semibold leading-snug text-slate-900">
                    {{ data_get($notification->data, 'title', 'Pemberitahuan') }}
                </p>
                <p class="mt-1 text-xs leading-snug text-slate-600">
                    {{ data_get($notification->data, 'message', 'Tidak ada pesan.') }}
                </p>
                <p class="mt-1 text-[11px] text-slate-400">
                    {{ $notification->created_at?->diffForHumans() }}
                </p>
            </button>
        </form>
    @empty
        <p class="px-4 py-5 text-sm text-slate-500">Belum ada notifikasi</p>
    @endforelse
</div>
