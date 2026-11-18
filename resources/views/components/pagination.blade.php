@if ($paginator->hasPages())
    <nav role="navigation" aria-label="Pagination Navigation" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-6 pb-2 border-t border-slate-200/80">
        <!-- Item count summary -->
        <div class="text-xs text-slate-500">
            <span>Hiển thị</span>
            <span class="font-bold text-slate-700">{{ $paginator->firstItem() }}</span>
            <span>-</span>
            <span class="font-bold text-slate-700">{{ $paginator->lastItem() }}</span>
            <span>trong tổng số</span>
            <span class="font-bold text-[#CE171F]">{{ $paginator->total() }}</span>
            <span>sản phẩm</span>
        </div>

        <!-- Navigation Buttons -->
        <div class="inline-flex items-center gap-1.5 flex-wrap justify-center">
            {{-- Previous Page Link --}}
            @if ($paginator->onFirstPage())
                <span class="px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-100/70 border border-slate-200 rounded-xl cursor-not-allowed">
                    <i class="fa-solid fa-chevron-left text-[9px] mr-1"></i> Trước
                </span>
            @else
                <button 
                    type="button" 
                    wire:click="previousPage" 
                    wire:loading.attr="disabled" 
                    class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-red-50 hover:text-[#CE171F] hover:border-red-300 transition-colors shadow-2xs cursor-pointer"
                >
                    <i class="fa-solid fa-chevron-left text-[9px] mr-1"></i> Trước
                </button>
            @endif

            {{-- Pagination Elements --}}
            @foreach ($elements as $element)
                {{-- "Three Dots" Separator --}}
                @if (is_string($element))
                    <span class="px-2.5 py-1.5 text-xs text-slate-400 font-semibold">{{ $element }}</span>
                @endif

                {{-- Array Of Links --}}
                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span class="w-8 h-8 flex items-center justify-center text-xs font-bold text-white bg-[#CE171F] border border-[#CE171F] rounded-xl shadow-xs">
                                {{ $page }}
                            </span>
                        @else
                            <button 
                                type="button" 
                                wire:click="gotoPage({{ $page }})" 
                                class="w-8 h-8 flex items-center justify-center text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-red-50 hover:text-[#CE171F] hover:border-red-300 transition-colors shadow-2xs cursor-pointer"
                            >
                                {{ $page }}
                            </button>
                        @endif
                    @endforeach
                @endif
            @endforeach

            {{-- Next Page Link --}}
            @if ($paginator->hasMorePages())
                <button 
                    type="button" 
                    wire:click="nextPage" 
                    wire:loading.attr="disabled" 
                    class="px-3 py-1.5 text-xs font-semibold text-slate-700 bg-white border border-slate-200 rounded-xl hover:bg-red-50 hover:text-[#CE171F] hover:border-red-300 transition-colors shadow-2xs cursor-pointer"
                >
                    Sau <i class="fa-solid fa-chevron-right text-[9px] ml-1"></i>
                </button>
            @else
                <span class="px-3 py-1.5 text-xs font-medium text-slate-400 bg-slate-100/70 border border-slate-200 rounded-xl cursor-not-allowed">
                    Sau <i class="fa-solid fa-chevron-right text-[9px] ml-1"></i>
                </span>
            @endif
        </div>
    </nav>
@endif
