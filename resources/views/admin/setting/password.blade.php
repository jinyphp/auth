<div>

    <x-navtab class="mb-3 nav-bordered">
        <!-- formTab -->
        <x-navtab-item class="show active">
            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">페스워드</span>
            </x-navtab-link>
            <div class="mb-3">
                <label class="form-label">비밀번호 최소 글자수</label>
                <input type="number" class="form-control"
                    wire:model="forms.password.min">
                <p>패스워드 입력 최소 글자수 지정</p>
            </div>

            <div class="mb-3">
                <label class="form-label">비밀번호 최대 글자수</label>
                <input type="number" class="form-control"
                    wire:model="forms.password.max">
                <p>패스워드 입력 최대 글자수 지정</p>
            </div>


            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.password.special"
                    {{ isset($forms['password']['special']) && $forms['password']['special'] ? 'checked' : '' }}>
                <label class="form-label">특수문자</label>
                <p>패스워드 특수문자 포함 여부</p>
            </div>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.password.number"
                    {{ isset($forms['password']['number']) && $forms['password']['number'] ? 'checked' : '' }}>
                <label class="form-label">숫자</label>
                <p>패스워드 숫자 포함 여부</p>
            </div>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.password.alpha"
                    {{ isset($forms['password']['alpha']) && $forms['password']['alpha'] ? 'checked' : '' }}>
                <label class="form-label">알파벳 대소문자</label>
                <p>패스워드에 대소문자를 포함 여부를 결정합니다.</p>
            </div>







        </x-navtab-item>

        <!-- formTab -->
        <x-navtab-item class="">
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">비밀번호 찾기</span>
            </x-navtab-link>

            <div class="mb-3">
                <label class="form-label">비밀번호 찾기 화면 view</label>
                <input type="text" class="form-control"
                    wire:model="forms.password.forget">
                <x-form-text>
                    <p>지정한 blade 화면으로 출력합니다.</p>
                </x-form-text>
            </div>

            <div class="mb-3">
                <label class="form-label">비밀번호 재설정 화면 view</label>
                <input type="text" class="form-control"
                    wire:model="forms.password.reset">
                <x-form-text>
                    <p>지정한 blade 화면으로 출력합니다.</p>
                </x-form-text>
            </div>
        </x-navtab-item>

        <!-- formTab -->
        <x-navtab-item class="">
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">만료갱신</span>
            </x-navtab-link>

            <div class="mb-3">
                <label class="form-label">갱신주기(개월)</label>
                <input type="number" class="form-control"
                    wire:model="forms.password.period">
                <p>지정한 기간후 비밀번호를 변경합니다.</p>
            </div>

            <div class="mb-3">
                <input type="checkbox" class="form-check-input"
                    wire:model="forms.password.renewal"
                    {{ isset($forms['password']['renewal']) && $forms['password']['renewal'] ? 'checked' : '' }}>
                <label class="form-label">패스워드 갱신</label>
                <p>일정 기간마다 패스워드 갱신이 요청합니다</p>
            </div>

        </x-navtab-item>

    </x-navtab>




</div>
