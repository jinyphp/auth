<x-theme theme="admin.sidebar">
    <x-theme-layout>

        <!-- Module Title Bar -->
        @if(Module::has('Titlebar'))
            @livewire('TitleBar', ['actions'=>$actions])
        @endif



    </x-theme-layout>
</x-theme>
