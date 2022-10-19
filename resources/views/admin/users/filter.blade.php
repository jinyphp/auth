<div class="bg-white p-4 mb-2">

    <x-form-hor>
        <x-form-label>국가</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"filter.country")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>이메일</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"filter.email")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

    {{--
    <x-tw-datepicker label="Starts at" wire:model="filter.starts_at">
    </x-tw-datepicker>
    --}}

    <button wire:clic="filter_search">검색</button>
    <button wire:clic="filter_reset">취소</button>
</div>

