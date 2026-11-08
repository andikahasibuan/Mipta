@if ($paginator->hasPages())
    <nav role="navigation" aria-label="{{ __('Pagination Navigation') }}" class="flex flex-wrap items-center gap-1">
        {{-- Previous Page Link --}}
        @if ($paginator->onFirstPage())
            <span
                aria-disabled="true"
                aria-label="{{ __('pagination.previous') }}"
                class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-white text-slate-300"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
        @else
            <a
                href="{{ $paginator->previousPageUrl() }}"
                rel="prev"
                aria-label="{{ __('pagination.previous') }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-[#2D507E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @endif

        {{-- Pagination Elements --}}
        @foreach ($elements as $element)
            {{-- "Three Dots" Separator --}}
            @if (is_string($element))
                <span
                    aria-disabled="true"
                    class="inline-flex h-9 min-w-9 cursor-default items-center justify-center rounded-md border border-slate-200 bg-white px-2 text-sm font-medium text-slate-500"
                >
                    {{ $element }}
                </span>
            @endif

            {{-- Array Of Links --}}
            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span
                            aria-current="page"
                            class="inline-flex h-9 min-w-9 cursor-default items-center justify-center rounded-md border border-[#2D507E] bg-[#2D507E] px-2 text-sm font-semibold text-white"
                        >
                            {{ $page }}
                        </span>
                    @else
                        <a
                            href="{{ $url }}"
                            aria-label="{{ __('Go to page :page', ['page' => $page]) }}"
                            class="inline-flex h-9 min-w-9 items-center justify-center rounded-md border border-slate-200 bg-white px-2 text-sm font-medium text-slate-700 transition hover:bg-slate-50 hover:text-[#2D507E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50"
                        >
                            {{ $page }}
                        </a>
                    @endif
                @endforeach
            @endif
        @endforeach

        {{-- Next Page Link --}}
        @if ($paginator->hasMorePages())
            <a
                href="{{ $paginator->nextPageUrl() }}"
                rel="next"
                aria-label="{{ __('pagination.next') }}"
                class="inline-flex h-9 w-9 items-center justify-center rounded-md border border-slate-200 bg-white text-slate-500 transition hover:bg-slate-50 hover:text-[#2D507E] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#5191E4]/50"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </a>
        @else
            <span
                aria-disabled="true"
                aria-label="{{ __('pagination.next') }}"
                class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-md border border-slate-200 bg-white text-slate-300"
            >
                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                </svg>
            </span>
        @endif
    </nav>
@endif
