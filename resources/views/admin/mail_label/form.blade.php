<div>
    <x-form-hor>
        <x-form-label>활성화</x-form-label>
        <x-form-item>
            <input type="checkbox" class="form-check-input"
                wire:model="forms.enable"
                @if(isset($forms['enable']) && $forms['enable']) checked @endif>
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>라벨명</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.name")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>색상</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.color")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

</div>
