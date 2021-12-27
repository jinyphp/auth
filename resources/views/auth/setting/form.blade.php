<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>로그인 허용</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"form.login")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>회원가입 허용</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"form.register")
                    !!}
                </x-form-item>
            </x-form-hor>


            <hr>

            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"form.enable")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>은행명</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"form.name")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>예금주</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"form.user")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>계좌번호</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"form.account")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>





        <!-- formTab -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">메모</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>메모</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"form.description")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

    </x-navtab>
</div>
