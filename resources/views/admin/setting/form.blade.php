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
                        ->setWire('model.defer',"forms.login")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>회원가입 허용</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.register")
                    !!}
                </x-form-item>
            </x-form-hor>


            <hr>

            <x-form-hor>
                <x-form-label>로그인 View</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.view_login")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>


            <x-form-hor>
                <x-form-label>dashboard</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.dashboard")
                        ->setWidth("standard")
                    !!}
                    <p>로그인 성공시 이동하는 uri</p>
                </x-form-item>
            </x-form-hor>


            <x-form-hor>
                <x-form-label>logout uri</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.logout")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>logout redirect uri</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.logout_redirect")
                        ->setWidth("standard")
                    !!}
                    <p>로그아웃후 이동되는 기본 경로입니다.</p>
                </x-form-item>
            </x-form-hor>

            <hr>

            <x-form-hor>
                <x-form-label>회원가입 View</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.view_regist")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.enable")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>은행명</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.name")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>예금주</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.user")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>계좌번호</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.account")
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
                        ->setWire('model.defer',"forms.description")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

    </x-navtab>
</div>
