<div class="bg-white">
    <x-loading-indicator/>

    <!-- Search -->
    <div class="p-5 lg:p-6 grow w-full border-b border-gray-200">

        <div class="flex">
            <div class="w-48 pr-5 ">
                <div class="space-y-6">
                    <div class="space-y-1 md:space-y-0 md:flex md:items-center">
                        <label for="filter-country" class="font-medium md:w-1/3 flex-none md:mr-2">페이징</label>
                        <select
                    class="w-full block border border-gray-200 rounded px-3 py-2 focus:border-blue-500 focus:ring focus:ring-blue-500 focus:ring-opacity-50"
                    id="select" name="select"
                    model="paging">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
                    </div>
                </div>

            </div>
            <div class="grow pl-5">
                {{-- 필터를 적용시 filter.blade.php 를 읽어 옵니다. --}}
                @if (isset($actions['view_filter']))

                    @includeIf($actions['view_filter'])

                @endif

            </div>
        </div>




        @if (session()->has('message'))
            <div class="alert alert-success">{{session('message')}}</div>
        @endif
    </div>


    <div class="p-5 lg:p-6 flex-grow w-full">
        <!-- Responsive Table Container -->
        <div class="border border-gray-200 rounded overflow-x-auto min-w-full bg-white">
            @if (isset($actions['view_list']))
                @includeIf($actions['view_list'])
            @endif
        </div>
        <!-- END Responsive Table Container -->

        {{-- 선택갯수 표시--}}
        <div class="py-2">
            @if (count($selected))
            <!-- Button (extra small) -->
            <button type="button"
            id="selected-delete" wire:click="popupDeleteOpen"
            class="inline-flex justify-center items-center space-x-2 rounded border font-semibold focus:outline-none px-2 py-1 leading-5 text-sm border-red-700 bg-red-700 text-white hover:text-white hover:bg-red-800 hover:border-red-800 focus:ring focus:ring-red-500 focus:ring-opacity-50 active:bg-red-700 active:border-red-700">
                선택삭제
            </button>
            <!-- END Button (extra small) -->


            @else
            <!-- Secondary Button (extra small) -->
            <button type="button"
            id="selected-delete" wire:click="popupDeleteOpen" disabled
            class="inline-flex justify-center items-center space-x-2 rounded border font-semibold focus:outline-none px-2 py-1 leading-5 text-sm border-red-200 bg-red-200 text-red-700 hover:text-red-700 hover:bg-red-300 hover:border-red-300 focus:ring focus:ring-red-500 focus:ring-opacity-50 active:bg-red-200 active:border-red-200">
                선택삭제
            </button>
            <!-- END Secondary Button (extra small) -->
            @endif

            <span class="px-2">selected</span>
            <span id="selected-num">{{count($selected)}}</span>
        </div>

    </div>


    <!-- Card Footer: Pagination -->
    <div class="py-4 px-5 lg:px-6 w-full border-t border-gray-200">
        <!-- Visible in mobile -->
        <nav class="flex sm:hidden">
            <a href="javascript:void(0)"
                class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none px-3 py-2 leading-6 rounded border-gray-300 bg-white text-gray-800 shadow-sm hover:text-gray-800 hover:bg-gray-100 hover:border-gray-300 hover:shadow focus:ring focus:ring-gray-500 focus:ring-opacity-25 active:bg-white active:border-white active:shadow-none">
                <svg class="hi-solid hi-chevron-left inline-block w-5 h-5" fill="currentColor"
                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
            </a>
            <div class="flex items-center grow justify-center px-2 sm:px-4 text-sm">
                <span>Page <span class="font-semibold">2</span> of <span
                        class="font-semibold">52</span></span>
            </div>
            <a href="javascript:void(0)"
                class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none px-3 py-2 leading-6 rounded border-gray-300 bg-white text-gray-800 shadow-sm hover:text-gray-800 hover:bg-gray-100 hover:border-gray-300 hover:shadow focus:ring focus:ring-gray-500 focus:ring-opacity-25 active:bg-white active:border-white active:shadow-none">
                <svg class="hi-solid hi-chevron-right inline-block w-5 h-5" fill="currentColor"
                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                    <path fill-rule="evenodd"
                        d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z"
                        clip-rule="evenodd"></path>
                </svg>
            </a>
        </nav>
        <!-- END Visible in mobile -->

        <!-- Visible in desktop -->
        <div class="hidden sm:flex sm:justify-between sm:items-center text-sm">
            <div>Page <span class="font-semibold">{{ $currentPage }}</span> of <span class="font-semibold">{{$totalPages}}</span></div>
            @if (isset($rows) && is_object($rows))
                @if(method_exists($rows, "links"))
                {{ $rows->links() }}
                @endif
            @endif
        </div>
        <!-- END Visible in desktop -->
    </div>
    <!-- END Card Footer: Pagination -->



    <div class="bg-white">
        {{-- header --}}



        {{-- footer --}}
        <div class="p-2">








        </div>
    </div>


    {{-- 선택삭제 --}}
    @include("jinytable::livewire.popup.delete")

    {{-- 퍼미션 알람--}}
    @include("jinytable::error.popup.permit")

</div>
