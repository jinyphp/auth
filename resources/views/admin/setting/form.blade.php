<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">승인</span>
            </x-navtab-link>


            <x-form-hor>
                <x-form-label>승인된 회원만 접속</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.auth.enable")
                    !!}
                    <p>관리자의 승인을 얻은 회원만 로그인을 할 수 있도록 설정을 합니다.</p>
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>자동승인</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.auth.auto")
                    !!}
                    <p>회원 가입시 자동으로 승인 여부를 처리합니다.</p>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">비밀번호</span>
            </x-navtab-link>

            

            <hr>

            <x-form-hor>
                <x-form-label>Forget View</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.view.forget")
                        ->setWidth("standard")
                    !!}
                    <p>지정한 양식의 blade 파일을 사용합니다.</p>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

        <!-- tab start -->
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
        <!-- tab end -->

    </x-navtab>
</div>
