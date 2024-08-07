{{--
<div class="mb-3">
    <label class="form-label">Email</label>
    <input class="form-control form-control-lg" type="email" name="email"
        placeholder="Enter your email"
        :value="old('email')"
        required autofocus>
</div>
--}}
<x-login-email>
    <small>
        회원가입 이메일을 입력해 주세요.
    </small>
</x-login-email>

{{-- <div class="mb-3">
    <label class="form-label">Password</label>
    <input class="form-control form-control-lg" type="password" name="password"
        placeholder="Enter your password" required>
    <small>
        @if (Route::has('password.request'))
            <a href="{{ route('password.request') }}"
                class="inline-block text-indigo-600 hover:text-indigo-400">
                {{ __('Forgot your password?') }}
            </a>
        @endif

    </small>
</div> --}}
<x-login-password>
    <small>
        <x-login-forgot>
            {{ __('Forgot your password?') }}
        </x-login-forgot>
    </small>
</x-login-password>

<div>
    <div class="form-check align-items-center">
        <x-login-remember>
            {{ __('Remember me') }}
        </x-login-remember>
    </div>
</div>

<div class="d-grid gap-2 mt-3">
    <x-login-submit>
        {{ __('Log in') }}
    </x-login-submit>
</div>
