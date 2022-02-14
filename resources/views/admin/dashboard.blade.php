<x-theme theme="admin.sidebar2">
    <x-theme-layout>
        <!-- start page title -->
        @livewire('TableTitle', ['actions' => $actions])
        <!-- end page title -->



        {{-- Admin Rule Setting --}}
        @include('jinytable::setActionRule')

    </x-theme-layout>
</x-theme>
