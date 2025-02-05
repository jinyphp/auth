<div>
    <x-form-hor>
        <x-form-label>회원 이메일</x-form-label>
        <x-form-item>
            {!! xInputText()
                ->setWire('model.defer',"forms.email")
                ->setWidth("standard")
            !!}
        </x-form-item>
    </x-form-hor>


</div>
