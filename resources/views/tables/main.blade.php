<x-tailwindcss>

    <x-tail-main>
        <div class="container xl:max-w-7xl mx-auto p-4 lg:p-8">
            <div class="space-y-4 lg:space-y-8">
                <!-- All Contacts -->
                <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                    <div class="py-4 px-5 lg:px-6 w-full bg-gray-50 flex justify-between items-center">
                        <div>
                            <h3 class="font-semibold flex">
                                <span>회원목록</span>

                                <span id="btn-livepopup-manual" wire:click="$emit('popupManualOpen')">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
                                    </svg>
                                </span>
                            </h3>
                            <h4 class="text-gray-500 text-sm">
                                You have <span class="font-medium">260 contacts</span>
                            </h4>
                        </div>
                        <div class="text-right sm:w-48">
                            <!-- Button (small) -->
                            <button
                                type="button"
                                id="btn-livepopup-create"
                                primary wire:click="$emit('popupFormOpen')"
                                class="inline-flex justify-center items-center space-x-2 rounded border font-semibold focus:outline-none px-3 py-2 leading-5 text-sm border-blue-700 bg-blue-700 text-white hover:text-white hover:bg-blue-800 hover:border-blue-800 focus:ring focus:ring-blue-500 focus:ring-opacity-50 active:bg-blue-700 active:border-blue-700">
                                <svg class="hi-solid hi-plus inline-block w-4 h-4 sm:opacity-50" fill="currentColor"
                                    viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd"
                                        d="M10 5a1 1 0 011 1v3h3a1 1 0 110 2h-3v3a1 1 0 11-2 0v-3H6a1 1 0 110-2h3V6a1 1 0 011-1z"
                                        clip-rule="evenodd"></path>
                                </svg>
                                <span class="hidden sm:inline-block">신규추가</span>
                            </button>
                            <!-- END Button (small) -->
                        </div>

                    </div>

                </div>
                <!-- END All Contacts -->
            </div>
            @livewire('WireTable', ['actions' => $actions])
        </div>
    </x-tail-main>


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


    @livewire('Popup-LiveForm', ['actions' => $actions])

    @livewire('Popup-LiveManual')

    {{-- Admin Rule Setting --}}
    @include('jinytable::setActionRule')

    {{-- popup UI Design mode --}}
    <!-- ui design form -->
    @livewire('DesignForm')

</x-tailwindcss>
