<x-theme theme="admin.sidebar3">
    <x-theme-layout>
        <!-- start page title -->
        @if (isset($actions['view']['title']))
            @includeIf($actions['view']['title'])
        @endif
        <!-- end page title -->

        <div class="relative">
            <div class="absolute right-0 bottom-4">
                <div class="btn-group">
                    <x-button id="btn-livepopup-manual" secondary wire:click="$emit('popupManualOpen')">메뉴얼</x-button>
                    <x-button id="btn-livepopup-create" primary wire:click="$emit('popupFormOpen')">신규추가</x-button>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.querySelector("#btn-livepopup-create").addEventListener("click",function(e){
                e.preventDefault();
                Livewire.emit('popupFormCreate');
            });

            document.querySelector("#btn-livepopup-manual").addEventListener("click",function(e){
                e.preventDefault();
                Livewire.emit('popupManualOpen');
            });
        </script>
        @endpush

        <style>
            .cate-submenu {
                -ms-flex: 0 0 230px;
                flex: 0 0 230px;
            }
        </style>
        <x-row>
            <div class="col cate-submenu">
                @include("jinyadmin::auth.submenu")
            </div>
            <div class="col">
                @livewire('WireTable', ['actions'=>$actions])

                @livewire('WirePopupForm', ['actions'=>$actions])

                @livewire('Popup-LiveManual')
            </div>
        </x-row>


    </x-theme-layout>
</x-theme>
