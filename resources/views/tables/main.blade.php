<x-admin-hyper>

    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box">
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Hyper</a></li>
                        <li class="breadcrumb-item"><a href="javascript: void(0);">eCommerce</a></li>
                        <li class="breadcrumb-item active">Products</li>
                    </ol>
                </div>
                <h4 class="page-title">
                    {{ isset($actions['title']) ? $actions['title'] : '' }}

                    <span id="btn-livepopup-manual" wire:click="$emit('popupManualOpen')">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-wallet2" viewBox="0 0 16 16">
                            <path d="M12.136.326A1.5 1.5 0 0 1 14 1.78V3h.5A1.5 1.5 0 0 1 16 4.5v9a1.5 1.5 0 0 1-1.5 1.5h-13A1.5 1.5 0 0 1 0 13.5v-9a1.5 1.5 0 0 1 1.432-1.499zM5.562 3H13V1.78a.5.5 0 0 0-.621-.484zM1.5 4a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h13a.5.5 0 0 0 .5-.5v-9a.5.5 0 0 0-.5-.5z"/>
                        </svg>
                    </span>
                </h4>

            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="row mb-2">
                        <div class="col-sm-5">
                            <a href="javascript:void(0);"
                                class="btn btn-danger mb-2"
                                id="btn-livepopup-create"
                                wire:click="$emit('popupFormOpen')">
                                <i class="mdi mdi-plus-circle me-2"></i> 신규추가
                            </a>
                        </div>
                        <div class="col-sm-7">
                            <div class="text-sm-end">
                                <button type="button" class="btn btn-success mb-2 me-1"><i
                                        class="mdi mdi-cog-outline"></i></button>
                                <button type="button" class="btn btn-light mb-2 me-1">Import</button>
                                <button type="button" class="btn btn-light mb-2">Export</button>
                            </div>
                        </div><!-- end col-->
                    </div>

                    <div class="table-responsive">
                        @livewire('WireTable', ['actions' => $actions])
                    </div>
                </div> <!-- end card-body-->
            </div> <!-- end card-->
        </div> <!-- end col -->
    </div>
    <!-- end row -->

    @push('scripts')
        <script>
            document.querySelector("#btn-livepopup-create").addEventListener("click", function(e) {
                e.preventDefault();
                Livewire.emit('popupFormCreate');
            });

            document.querySelector("#btn-livepopup-manual").addEventListener("click", function(e) {
                e.preventDefault();
                Livewire.emit('popupManualOpen');
            });
        </script>
    @endpush

    @livewire('WirePopupForm', ['actions' => $actions])

    @livewire('Popup-LiveManual')

    {{-- SuperAdmin Actions Setting --}}
    @if(Module::has('Actions'))
        @livewire('setActionRule', ['actions'=>$actions])
    @endif

    {{-- popup UI Design mode --}}
    <!-- ui design form -->
    @livewire('DesignForm')
</x-admin-hyper>
