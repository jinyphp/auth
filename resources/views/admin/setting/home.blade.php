<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">로그인</span>
            </x-navtab-link>

            <div class="row">
                <div class="col-6"> <!-- 왼쪽 -->
                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            로그인 허용
                        </label>
                        <div class="col-9">
                            {!! xCheckbox()
                                ->setWire('model.defer',"forms.login.enable")
                            !!}
                            <br/>
                            <x-form-text>
                                사이트 회원가입을 허용합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>

                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            Remember Me
                        </label>
                        <div class="col-9">
                            {!! xCheckbox()
                                ->setWire('model.defer',"forms.remember")
                            !!}
                            <br>
                            <x-form-text>
                                로그인 접속기록을 일시적으로 브라우저에 기억합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>

                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            home 화면
                        </label>
                        <div class="col-9">
                            {!! xInputText()
                                ->setWire('model.defer',"forms.home")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>
                                로그인 성공시 지정한 uri로 리다이렉션 합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>

                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            사용자 리다이렉션
                        </label>
                        <div class="col-9">
                            {!! xCheckbox()
                                ->setWire('model.defer',"forms.redirect")
                            !!}
                            <br>
                            <x-form-text>
                                로그인시 사용자별로 지정한 경로로 이동합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>

                </div> <!-- end -->

                <div class="col-6"> <!-- 오른쪽 -->

                    <div>이메일 발송 및 검증을 하기 위해서는 메일서버 설징이 되어 있어야 합니다.</div>
                    <br>

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

                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            Verified Login
                        </label>
                        <div class="col-9">
                            {!! xCheckbox()
                                ->setWire('model.defer',"forms.login.verified")
                            !!}
                            <br/>
                            <x-form-text>
                                이메일 검증된 회원만 접속이 가능합니다.
                            </x-form-text>
                        </div>
                    </x-form-hor>




                </div> <!-- end -->
            </div>

        </x-navtab-item>
        <!-- tab end -->

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">패스워드</span>
            </x-navtab-link>

            <div class="row">
                <div class="col-6"> <!-- 왼쪽 -->
                    

                    <x-form-hor>
                        <x-form-label>패스워드 갱신기간</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.password_period")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>패스워드 갱신주기</x-form-text>
                        </x-form-item>
                    </x-form-hor>
                </div>

                <div class="col-6"> <!-- 왼쪽 -->
                    <x-form-hor>
                        <x-form-label>패스워드 최소길이</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.password_min")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>패스워드 입력 최소 길이를 지정합니다.</x-form-text>
                        </x-form-item>
                    </x-form-hor>

                    <x-form-hor>
                        <x-form-label>패스워드 최대길이</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.password_max")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>패스워드 입력 최대 길이를 지정합니다.</x-form-text>

                        </x-form-item>
                    </x-form-hor>
                </div>
            </div>

        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">접속관리</span>
            </x-navtab-link>

            <div class="row">
                <div class="col-6"> <!-- 왼쪽 -->
                    <x-form-hor> <!-- -->
                        <label class="col-3 col-form-label">
                            휴면회원
                        </label>
                        <div class="col-9">
                            {!! xCheckbox()
                                ->setWire('model.defer',"forms.sleeper.enable")
                            !!}
                            <br/>
                            <x-form-text>
                                휴면회원 시스템을 적용합니다.
                            </x-form-text>
                        </div>
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


                </div>
            </div>

        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">화면설정</span>
            </x-navtab-link>

            <div class="row">
                <div class="col-6"> <!-- 왼쪽 -->
                    <x-form-hor>
                        <x-form-label>로그인 View</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.view_login")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>
                                지정한 양식의 blade 파일을 사용합니다.
                            </x-form-text>
                        </x-form-item>
                    </x-form-hor>

                    <x-form-hor>
                        <x-form-label>로그인 Disable</x-form-label>
                        <x-form-item>
                            {!! xInputText()
                                ->setWire('model.defer',"forms.login.disable")
                                ->setWidth("standard")
                            !!}
                            <x-form-text>
                                로그인 방지시 표시되는 화면입니다.
                            </x-form-text>
                        </x-form-item>
                    </x-form-hor>
                </div>

                <div class="col-6"> <!-- 오른쪽 -->
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
