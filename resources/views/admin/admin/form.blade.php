<div>
    <x-form-hor>
        <x-form-label>이메일</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.email")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

    <x-form-hor>
        <x-form-label>Super Admin</x-form-label>
        <x-form-item>
            <input type="checkbox" class="form-check-input"
            wire:model="forms.super"
            {{ isset($forms['super']) && $forms['super'] == 1 ? "checked" : "" }}>
        </x-form-item>
    </x-form-hor>

</div>
