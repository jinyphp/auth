<x-navtab class="mb-3 nav-bordered">
    <!-- formTab -->
    <x-navtab-item class="show active" >

        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">회원가입</span>
        </x-navtab-link>

        <div class="row cap-2">
            <div class="col-12 col-md-6">
                <x-form-hor>
                    <label class="col-3 col-form-label">
                        회원가입 허용
                    </label>
                    <div class="col-9">
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.regist.enable")
                        !!}
                        <br/>
                        <x-form-text>
                            사이트내 회원가입 폼을 활성화 할 것인지를 선택합니다.
                            <br/>
                            비활성화 되는 경우 사용자가 직접 회원양식을 통한 가입이 제한됩니다.
                        </x-form-text>
                    </div>
                </x-form-hor>

                <x-form-hor>
                    <label class="col-3 col-form-label">
                        회원가입 중단
                    </label>
                    <div class="col-9">
                        {!! xInputText()
                            ->setWire('model.defer',"forms.regist.reject")
                            ->setWidth("standard")
                        !!}
                        <br/>
                        <x-form-text>
                            회원가입 중단 화면 blade 리소스.
                        </x-form-text>
                    </div>
                </x-form-hor>

                <x-form-hor>
                    <label class="col-3 col-form-label">
                        회원가입 View
                    </label>
                    <div class="col-9">
                        {!! xInputText()
                            ->setWire('model.defer',"forms.view.regist.view")
                            ->setWidth("standard")
                        !!}
                        <br/>
                        <x-form-text>
                            지정한 양식의 blade 파일을 사용합니다.
                        </x-form-text>
                    </div>
                </x-form-hor>

            </div>
            <div class="col-12 col-md-6">


                <x-form-hor>
                    <label class="col-3 col-form-label">
                        성공처리
                    </label>
                    <div class="col-9">
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.success.enable")
                        !!}
                        <br/>
                        <x-form-text>
                            회원가입 성공시 페이지를 이동합니다.
                        </x-form-text>
                    </div>
                </x-form-hor>


                <x-form-hor>
                    <label class="col-3 col-form-label">
                        성공페이지
                    </label>
                    <div class="col-9">
                        {!! xInputText()
                            ->setWire('model.defer',"forms.success.view")
                            ->setWidth("standard")
                        !!}
                        <br/>
                        <x-form-text>
                            회원가입 성공시 페이지를 이동합니다.
                        </x-form-text>
                    </div>
                </x-form-hor>
            </div>
        </div>

    </x-navtab-item>

    <x-navtab-item>
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">회원검증</span>
        </x-navtab-link>

        <div class="row">
            <div class="col-12 col-md-6">
                <x-form-hor>
                    <label class="col-3 col-form-label">
                        회원승인
                    </label>
                    <div class="col-9">
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.auth.enable")
                        !!}
                        <br/>
                        <x-form-text>
                            회원 승인적용
                        </x-form-text>
                    </div>
                </x-form-hor>

                <x-form-hor>
                    <label class="col-3 col-form-label">
                        자동승인
                    </label>
                    <div class="col-9">
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.auth.auto")
                        !!}
                        <br/>
                        <x-form-text>
                            회원가입시 자동으로 승인을 처리합니다.
                        </x-form-text>
                    </div>
                </x-form-hor>
            </div>
            <div class="col-12 col-md-6">
                <x-form-hor>
                    <label class="col-3 col-form-label">
                        Email verified
                    </label>
                    <div class="col-9">
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.regist.verified")
                        !!}
                        <br/>
                        <x-form-text>
                            이메일 검증확인.
                        </x-form-text>
                    </div>
                </x-form-hor>
            </div>
        </div>

    </x-navtab-item>


    <x-navtab-item>
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">회원동의</span>
        </x-navtab-link>

        <div class="row">
            <div class="col-12 col-md-6">
                <x-form-hor>
                    <x-form-label>약관동의</x-form-label>
                    <x-form-item>
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.agree.enable")
                        !!}
                        <br>
                        <x-form-text>
                            회원 가입시 약관 동의 절차가 필요로 합니다.
                        </x-form-text>
                    </x-form-item>
                </x-form-hor>

                <x-form-hor>
                    <x-form-label>동의서 View</x-form-label>
                    <x-form-item>
                        {!! xInputText()
                            ->setWire('model.defer',"forms.agree.view")
                            ->setWidth("standard")
                        !!}
                        <x-form-text>
                            지정한 양식의 blade 파일을 사용합니다.
                        </x-form-text>
                    </x-form-item>
                </x-form-hor>

            </div>
            <div class="col-12 col-md-6">
                <ul>
                    <li><a href="/admin/auth/agree">약관동의서</a></li>
                </ul>
            </div>
        </div>

    </x-navtab-item>

    <x-navtab-item>
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">메일발송</span>
        </x-navtab-link>

        <div class="row">
            <div class="col-12 col-md-6">
                <x-form-hor>
                    <x-form-label>가입메일</x-form-label>
                    <x-form-item>
                        {!! xCheckbox()
                            ->setWire('model.defer',"forms.regist.mail")
                        !!}
                        <br>
                        <x-form-text>
                            회원 가입시 축하 메일을 발송합니다.
                        </x-form-text>
                    </x-form-item>
                </x-form-hor>
            </div>
        </div>

    </x-navtab-item>





</x-navtab>
