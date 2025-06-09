@php
if (! isset($scrollTo)) {
    $scrollTo = 'body';
}

$scrollIntoViewJsSnippet = ($scrollTo !== false)
    ? <<<JS
       (\$el.closest('{$scrollTo}') || document.querySelector('{$scrollTo}')).scrollIntoView()
    JS
    : '';
@endphp

<div>
    @if ($paginator->hasPages())
        <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
            <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm text-gray-700 dark:text-gray-400 leading-5">
                        Showing {{ $paginator->firstItem() }} to {{ $paginator->lastItem() }} of {{ $paginator->total() }} results
                    </p>
                </div>
                <div>
                    <span class="relative z-0 inline-flex shadow-sm">
                        {{-- Previous Page Link --}}
                        @if ($paginator->onFirstPage())
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-l-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </span>
                        @else
                            <button wire:click="previousPage" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:text-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </button>
                        @endif

                        {{-- Page Numbers --}}
                        @php
                            // Calculate the page range to show
                            $start = max($paginator->currentPage() - 2, 1);
                            $end = min($paginator->currentPage() + 2, $paginator->lastPage());
                        @endphp

                        {{-- Show first page if not in range --}}
                        @if($start > 1)
                            <button wire:click="gotoPage(1)" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">1</button>
                            @if($start > 2)
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">...</span>
                            @endif
                        @endif

                        {{-- Display page numbers --}}
                        @for($i = $start; $i <= $end; $i++)
                            <button wire:click="gotoPage({{ $i }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium {{ $i == $paginator->currentPage() ? 'text-white bg-blue-600 border-blue-600 dark:bg-blue-700 dark:border-blue-700' : 'text-gray-700 bg-white border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300' }}">{{ $i }}</button>
                        @endfor

                        {{-- Show last page if not in range --}}
                        @if($end < $paginator->lastPage())
                            @if($end < $paginator->lastPage() - 1)
                                <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">...</span>
                            @endif
                            <button wire:click="gotoPage({{ $paginator->lastPage() }})" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300">{{ $paginator->lastPage() }}</button>
                        @endif

                        {{-- Next Page Link --}}
                        @if ($paginator->hasMorePages())
                            <button wire:click="nextPage" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:text-gray-400 dark:bg-gray-800 dark:border-gray-600 dark:text-gray-300 dark:hover:text-gray-100">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </button>
                        @else
                            <span class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-default rounded-r-md dark:bg-gray-800 dark:border-gray-600 dark:text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" fill-rule="evenodd"></path></svg>
                            </span>
                        @endif
                    </span>
                </div>
            </div>
        </nav>
    @endif
</div>
