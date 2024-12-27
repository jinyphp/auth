{{-- 회원 가입양식 --}}
<x-register-name></x-register-name>

<x-register-email></x-register-email>

<x-register-password></x-register-password>

<x-register-password-confirm></x-register-password-confirm>

<div class="mb-3">
    <div class="mb-4 d-flex align-items-center justify-content-between">
        <div class="form-check">
            <input class="form-check-input" type="checkbox"
            id="signupCheckTextCheckbox" name="terms" />
            <label class="form-check-label ms-2"
                for="signupCheckTextCheckbox">
                <a href="/terms">Terms of Use</a>
                &
                <a href="/terms">Privacy Policy</a>
            </label>
        </div>
    </div>
</div>

<div class="d-grid">
    <x-register-submit class="btn-lg w-100">
        {{ __('가입신청') }}
    </x-register-submit>
</div>


