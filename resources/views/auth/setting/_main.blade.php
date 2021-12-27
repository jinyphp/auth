<x-theme theme="admin.sidebar2">
    <x-theme-layout>
        <!-- start page title -->
        @if (isset($actions['view_title']))
            @includeIf($actions['view_title'])
        @endif
        <!-- end page title -->

        <style>
            .cate-submenu {
                -ms-flex: 0 0 230px;
                flex: 0 0 230px;
            }
        </style>
        <x-row>
            <div class="col cate-submenu">
                @include("jinyauth::auth.submenu")
            </div>
            <div class="col">
                @livewire('WireConfig', ['actions'=>$actions])
            </div>
        </x-row>

    </x-theme-layout>
</x-theme>

