<x-tailwindcss>
    <main id="page-content" class="flex flex-auto flex-col max-w-full pt-16">
        <div class="p-5">

            @livewire('WireConfig', ['actions' => $actions])
        </div>
    </main>


    {{-- SuperAdmin Actions Setting --}}
    @if(Module::has('Actions'))
        @livewire('setActionRule', ['actions'=>$actions])
    @endif
</x-tailwindcss>
