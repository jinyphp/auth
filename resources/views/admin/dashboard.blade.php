<x-theme>
    <x-theme-layout>
        <!-- Module Title Bar -->
        @if(Module::has('Titlebar'))
            @livewire('TitleBar', ['actions'=>$actions])
        @endif
        <!-- end -->

        Auth Admin Dashboard
        사용자수 : {{ user_total_count() }}

        {{-- Admin Rule Setting --}}
        @include('jinytable::setActionRule')

    </x-theme-layout>
</x-theme>
