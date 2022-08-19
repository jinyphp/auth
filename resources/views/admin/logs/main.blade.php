{{-- 목록을 출력하기 위한 템플릿 --}}
<x-theme theme="admin.sidebar">
    <x-theme-layout>
        <!-- Module Title Bar -->
        @if(Module::has('Titlebar'))
            @livewire('TitleBar', ['actions'=>$actions])
        @endif

        @livewire('WireTable', ['actions'=>$actions])



        @if(function_exists("is_admin") && is_admin())
            {{-- Admin Rule Setting --}}
            @include('jinytable::setActionRule')

            {{-- popup UI Design mode --}}
            <!-- ui design form -->
            @livewire('DesignForm')
        @endif

    </x-theme-layout>
</x-theme>
