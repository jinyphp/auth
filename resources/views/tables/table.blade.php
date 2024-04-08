<div>
    <x-loading-indicator/>

    <div id="products-datatable_wrapper"
    class="dataTables_wrapper dt-bootstrap5 no-footer">
    <div class="row">
        <div class="col-sm-6">
            <div class="dataTables_length" id="products-datatable_length">
                <label
                    class="page-display">
                    Page Display
                    <select
                    class="form-select form-select-sm ms-1 me-1ocus:ring-blue-500 focus:ring-opacity-50"
                    id="page-display" name="select"
                    model="paging">
                    <option value="5">5</option>
                    <option value="10">10</option>
                    <option value="20">20</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                    </select>
                </label>
            </div>
        </div>
        <div class="col-sm-6">
            <div id="products-datatable_filter" class="dataTables_filter">
                <label>Search:
                    <input
                        type="search" class="form-control form-control-sm" placeholder=""
                        aria-controls="products-datatable">
                </label>
                {{-- 필터를 적용시 filter.blade.php 를 읽어 옵니다. --}}
                @if (isset($actions['view']['filter']))

                    @includeIf($actions['view']['filter'])

                @endif
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12">
            @if (isset($actions['view']['list']))
                @includeIf($actions['view']['list'])
            @endif
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12 col-md-5">
            <div class="dataTables_info" id="products-datatable_info" role="status"
                aria-live="polite">
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
        </div>
        <div class="col-sm-12 col-md-7">
            <!-- pagination -->
            <div>Page <span class="font-semibold">{{ $currentPage }}</span> of <span class="font-semibold">{{$totalPages}}</span></div>
            @if (isset($rows) && is_object($rows))
                @if(method_exists($rows, "links"))
                {{ $rows->links() }}
                @endif
            @endif
        </div>
    </div>
</div>

    @if (session()->has('message'))
        <div class="alert alert-success">{{session('message')}}</div>
    @endif












    {{-- 선택삭제 --}}
    @include("jinytable::livewire.popup.delete")

    {{-- 퍼미션 알람--}}
    @include("jinytable::error.popup.permit")

</div>
