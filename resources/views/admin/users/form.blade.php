<div>
    <x-form-hor>
        <x-form-label>이름</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.name")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>

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
        <x-form-label>페스워드</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.password")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>
</div>
