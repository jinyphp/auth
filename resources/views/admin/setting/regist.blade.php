<x-navtab class="mb-3 nav-bordered">
    <!-- formTab -->
    <x-navtab-item class="show active">
        <x-navtab-link class="rounded-0 active">
            <span class="d-none d-md-block">회원가입</span>
        </x-navtab-link>

        <div class="mb-3">
            <input type="checkbox" class="form-check-input"
                wire:model="forms.regist.enable"
                {{ isset($forms['regist']['enable']) && $forms['regist']['enable'] ? 'checked' : '' }}>
            <label class="form-label">회원가입 허용</label>
            <x-form-text>
                사이트내 회원가입 폼을 활성화 할 것인지를 선택합니다.
                비활성화 되는 경우 사용자가 직접 회원양식을 통한 가입이 제한됩니다.
            </x-form-text>
        </div>


        <div class="mb-3">
            <label class="form-label">회원가입 View</label>
            <input type="number" class="form-control"
                wire:model="forms.regist.view">
            <x-form-text>
                회원가입 성공시 페이지를 이동합니다.
            </x-form-text>
        </div>


        <div class="mb-3">
            <label class="form-label">회원가입 중단</label>
            <input type="number" class="form-control"
                wire:model="forms.regist.reject">
            <x-form-text>
                회원가입 중단 화면 blade 리소스.
            </x-form-text>
        </div>

    </x-navtab-item>

    <x-navtab-item>
        <x-navtab-link class="rounded-0">
            <span class="d-none d-md-block">성공</span>
        </x-navtab-link>

        <div class="mb-3">
            <input type="checkbox" class="form-check-input"
                wire:model="forms.regist.success"
                {{ isset($forms['regist']['success']) && $forms['regist']['success'] ? 'checked' : '' }}>
            <label class="form-label">성공처리</label>
            <x-form-text>
                사이트내 회원가입 폼을 활성화 할 것인지를 선택합니다.
                비활성화 되는 경우 사용자가 직접 회원양식을 통한 가입이 제한됩니다.
            </x-form-text>
        </div>

        <div class="mb-3">
            <label class="form-label">성공페이지 View</label>
            <input type="number" class="form-control"
                wire:model="forms.regist.success_view">
            <x-form-text>
                회원가입 성공시 페이지를 이동합니다.
            </x-form-text>
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
                        {!! xCheckbox()->setWire('model.defer', 'forms.auth.enable') !!}
                        <br />
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
                        {!! xCheckbox()->setWire('model.defer', 'forms.auth.auto') !!}
                        <br />
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
                        {!! xCheckbox()->setWire('model.defer', 'forms.regist.verified') !!}
                        <br />
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

        <div class="mb-3">
            <input type="checkbox" class="form-check-input"
                wire:model="forms.agree.enable"
                {{ isset($forms['agree']['enable']) && $forms['agree']['enable'] ? 'checked' : '' }}>
            <label class="form-label">약관동의 허용</label>
            <x-form-text>
                회원 가입시 약관 동의 절차가 필요로 합니다.
            </x-form-text>
        </div>

        <div class="mb-3">
            <label class="form-label">동의서 화면 View</label>
            <input type="number" class="form-control"
                wire:model="forms.agree.view">
            <x-form-text>
                지정한 Blade 파일로 view를 출력합니다.
            </x-form-text>
        </div>

        <ul>
            <li><a href="/admin/auth/agree">약관동의서</a></li>
        </ul>

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
                        {!! xCheckbox()->setWire('model.defer', 'forms.regist.mail') !!}
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
