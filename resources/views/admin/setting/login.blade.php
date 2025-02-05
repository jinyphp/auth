<div>
    <script>
        var hash = window.location.hash;  // #section1
        if (hash) {
            // 해시 값에 따른 처리
            // console.log("현재 해시 값: " + hash);
        }
    </script>



    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active">

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">로그인</span>
            </x-navtab-link>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.login.enable"
                    {{ isset($forms['login']['enable']) && $forms['login']['enable'] ? 'checked' : '' }}>
                <label class="form-label">로그인 허용</label>
                <x-form-text>
                    사이트 회원가입을 허용합니다.
                </x-form-text>
            </div>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.login.remember"
                    {{ isset($forms['login']['remember']) && $forms['login']['remember'] ? 'checked' : '' }}>
                <label class="form-label">Remember Me</label>
                <x-form-text>
                    로그인 접속기록을 일시적으로 브라우저에 기억합니다.
                </x-form-text>
            </div>

            <div class="mb-3">
                <label class="form-label">로그인 화면 View</label>
                <input type="number" class="form-control"
                    wire:model="forms.login.view">
                <x-form-text>
                    지정한 Blade 파일로 view를 출력합니다.
                </x-form-text>
            </div>

            <div class="mb-3">
                <label class="form-label">로그인 방지 화면 View</label>
                <input type="number" class="form-control"
                    wire:model="forms.login.disable">
                <x-form-text>
                    로그인 방지시 표시되는 화면입니다.
                </x-form-text>
            </div>

        </x-navtab-item>
        <!-- tab end -->


        <x-navtab-item class="">
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">검증</span>
            </x-navtab-link>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.login.verified"
                    {{ isset($forms['login']['verified']) && $forms['login']['verified'] ? 'checked' : '' }}>
                <label class="form-label">Verified Login</label>
                <x-form-text>
                    이메일 검증된 회원만 접속이 가능합니다.
                </x-form-text>
            </div>

            <x-form-hor> <!-- -->
                <label class="col-3 col-form-label">
                    Email verification
                </label>
                <div class="col-9">
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.login.verification")
                    !!}
                    <br/>
                    <x-form-text>
                        이메일을 통하여 실제 회원을 검증합니다.
                    </x-form-text>
                </div>
            </x-form-hor>

            <div>이메일 발송 및 검증을 하기 위해서는 메일서버 설징이 되어 있어야 합니다.</div>
                    <br>



        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">리다이엑션</span>
            </x-navtab-link>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.home.redirect"
                    {{ isset($forms['home']['redirect']) && $forms['home']['redirect'] ? 'checked' : '' }}>
                <label class="form-label">사용자 리다이렉션</label>
                <x-form-text>
                    로그인 성공시 지정한 uri로 리다이렉션 합니다.
                </x-form-text>
            </div>

            <div class="mb-3">
                <label class="form-label">home 경로</label>
                <input type="number" class="form-control"
                    wire:model="forms.home.path">
                <x-form-text>
                    로그인후 지정한 경로로 이동합니다.
                </x-form-text>
            </div>


        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item class="">
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">휴면회원</span>
            </x-navtab-link>

            <div class="row">
                <div class="col-6"> <!-- 왼쪽 -->
                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            휴면회원
                        </label>
                        <div class="col-9">
                            <input type="checkbox" class="form-check-input"
                                wire:model="forms.sleeper.enable"
                                {{ isset($forms['sleeper']['enable']) && $forms['sleeper']['enable'] ? 'checked' : '' }}>
                            <br/>
                            <x-form-text>
                                휴면회원 시스템을 적용합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>

                    <x-form-hor>
                        <x-form-label>휴면 기간</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.sleeper.period")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>
                                지정한 기간동안 로그인을 하지 않는 경우 휴면 회원으로 전환됩니다.

                            </x-form-text>
                        </x-form-item>
                    </x-form-hor>

                    <x-form-hor>
                        <x-form-label>휴면회원 View</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.sleeper.view")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>
                                휴면회원 화면 Blade 리소스를 설정합니다.

                            </x-form-text>
                        </x-form-item>
                    </x-form-hor>

                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            자동해제
                        </label>
                        <div class="col-9">
                            <input type="checkbox" class="form-check-input"
                                wire:model="forms.sleeper.auto"
                                {{ isset($forms['sleeper']['auto']) && $forms['sleeper']['auto'] ? 'checked' : '' }}>
                            <br/>
                            <x-form-text>
                                사용자 재접속시 자동으로 해제합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>




                </div>
            </div>

        </x-navtab-item>



        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">로그기록</span>
            </x-navtab-link>

            <x-form-hor>
                <label class="col-3 col-form-label">
                    로그기록
                </label>
                <div class="col-9">
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.login.log")
                    !!}
                    <br>
                    <x-form-text>
                        로그인 접속기록을 데이터베이스에 저장합니다.
                    </x-form-text>
                </div>
            </x-form-hor>

        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">로그아웃</span>
            </x-navtab-link>
            <div class="row">
                <div class="col-6"> <!-- 왼쪽 -->
                    <x-form-hor> <!-- -->
                        <x-form-label>logout 이동</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.logout")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>
                                로그아웃시 지정한 url로 리다이렉션 합니다.
                            </x-form-text>
                        </x-form-item>
                    </x-form-hor>

                </div>
            </div>



        </x-navtab-item>


    </x-navtab>
</div>
