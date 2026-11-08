@props(['title', 'subtitle'])

<div class="-mt-2 mb-4 flex flex-wrap items-start justify-between gap-3">
    <div class="min-w-0">
        <h2 class="text-2xl font-semibold text-[#1F3A5F]">{{ $title }}</h2>
        <p class="mt-1 text-sm text-[#2D507E]">{{ $subtitle }}</p>
    </div>

    @if (! $slot->isEmpty())
        <div class="flex shrink-0 items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
