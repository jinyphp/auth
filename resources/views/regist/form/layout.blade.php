<div>
    {{-- 에러가없시 회원가입 동작시 스피너 활성화화--}}
    @if($spinner == 0b1111)
        <x-loading-indicator/>
    @endif

    {{-- 회원명 --}}
    <div class="mb-3">
        <label class="form-label">
            이름
        </label>
        <input type="text" required name="name"
            class="form-control"
            placeholder="회원명을 입력해 주세요"
            wire:model="forms.name"
            wire:keydown.enter="checkName"
            wire:blur="checkName"/>
        @if(isset($errors['name']))
            <div class="text-sm text-{{$errors['name']['status']}}">
                {{ $errors['name']['message'] }}
            </div>
        @endif
    </div>

    {{-- 이메일 --}}
    <div class="mb-3">
        <label class="form-label">
            이메일
        </label>
        <input type="text" required name="email"
            class="form-control"
            placeholder="이메일을 입력해 주세요"
            wire:model="forms.email"
            wire:keydown.enter="checkEmail"
            wire:blur="checkEmail"/>
        @if(isset($errors['email']))
            <div class="text-sm text-{{$errors['email']['status']}}">
                {{ $errors['email']['message'] }}
            </div>
        @endif
    </div>

    {{-- 비밀번호 --}}
    <div class="mb-3">
        <label class="form-label">
            비밀번호
        </label>
        <input type="password" required name="password"
            class="form-control"
            placeholder="비밀번호를 입력해 주세요"
            wire:model="forms.password"
            wire:keydown.enter="checkPassword"
            wire:blur="checkPassword"/>
        @if(isset($errors['password']))
            <div class="text-sm text-{{$errors['password']['status']}}">
                {{ $errors['password']['message'] }}
            </div>
        @endif
    </div>

    {{-- 비밀번호 확인 --}}
    <div class="mb-3">
        <label class="form-label">
            비밀번호 확인
        </label>
        <input type="password" required name="confirm_password"
            class="form-control"
            placeholder="비밀번호를 다시한번 입력해 주세요"
            wire:model="forms.confirm_password"
            wire:keydown.enter="confirmPassword"
            wire:blur="confirmPassword"/>
        @if(isset($errors['confirm_password']))
            <div class="text-sm text-{{$errors['confirm_password']['status']}}">
                {{ $errors['confirm_password']['message'] }}
            </div>
        @endif
    </div>

    {{-- 회원가입 버튼 --}}
    <div class="mt-4">
        <div class="d-grid">
            <button class="btn btn-primary btn-lg w-100" wire:click="submit">
                {{ __('가입신청') }}
            </button>
        </div>
    </div>




</div>
