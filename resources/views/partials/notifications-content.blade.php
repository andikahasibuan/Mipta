@inject('notificationTime', 'App\Services\NotificationTimeFormatter')

<x-ui.flash-messages />

<div>
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="text-lg font-semibold text-slate-900">Pemberitahuan</h2>

        @if ($unreadNotificationCount > 0)
            <form method="POST" action="{{ route('notifications.read-all') }}" class="ms-auto">
                @csrf
                <button
                    type="submit"
                    class="cursor-pointer rounded-lg bg-[#018F98] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#FDBA12]/40 hover:text-white"
                >
                    Tandai semua telah dibaca
                </button>
            </form>
        @endif
    </div>

    <div class="space-y-3">
        @forelse ($notifications as $notification)
            <article
                data-notification-card
                data-notification-read="{{ $notification->read_at === null ? 'false' : 'true' }}"
                @class([
                    'flex min-h-24 flex-col gap-4 rounded-xl border p-5 sm:flex-row sm:items-start',
                    'border-[#018F98]/30 bg-[#E0F2F3]/60' => $notification->read_at === null,
                    'border-slate-200 bg-white' => $notification->read_at !== null,
                ])
            >
                <div class="min-w-0 flex-1">
                    <form method="POST" action="{{ route('notifications.read', $notification) }}" class="inline">
                        @csrf
                        <button
                            type="submit"
                            class="cursor-pointer text-left text-base font-semibold text-slate-900 transition hover:text-[#018F98] hover:underline"
                        >
                            {{ data_get($notification->data, 'title', 'Pemberitahuan') }}
                        </button>
                    </form>

                    <p class="mt-1 text-sm leading-relaxed text-slate-600">
                        {{ data_get($notification->data, 'message', 'Tidak ada pesan.') }}
                    </p>
                </div>

                <div class="flex shrink-0 items-center gap-3 sm:pt-0.5">
                    <time
                        class="text-xs text-slate-400"
                        datetime="{{ $notification->created_at?->toIso8601String() }}"
                        title="{{ $notificationTime->formatFull($notification->created_at) }}"
                    >
                        {{ $notificationTime->format($notification->created_at) }}
                    </time>

                    <form
                        method="POST"
                        action="{{ route('notifications.destroy', $notification) }}"
                        onsubmit="return confirm('Yakin ingin menghapus notifikasi ini?')"
                    >
                        @csrf
                        @method('DELETE')
                        <button
                            type="submit"
                            aria-label="Hapus"
                            title="Hapus"
                            class="inline-flex h-9 w-9 cursor-pointer items-center justify-center rounded-lg border border-slate-200 bg-white text-red-500 transition hover:border-red-300 hover:bg-red-50"
                        >
                            <x-heroicon-o-trash class="h-4 w-4" />
                        </button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-xl border border-dashed border-slate-200 bg-white px-5 py-10 text-center text-sm text-slate-500">
                Belum ada notifikasi
            </div>
        @endforelse
    </div>

    @if ($notifications->isNotEmpty())
        <div class="mt-4 flex flex-wrap items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                Menampilkan {{ $notifications->count() }} dari {{ $notifications->total() }} notifikasi
            </p>

            @if ($notifications->hasPages())
                {{ $notifications->links() }}
            @endif
        </div>
    @endif
</div>
