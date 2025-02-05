<form method="POST" action="{{ route('password.update') }}">
    @csrf

    <!-- Session Status -->
    {{-- @if (Session::has('status'))
        <div class="alert alert-warning alert-dismissible" role="alert">
            <div class="alert-message">
                {{ Session::get('status') }}
            </div>
        </div>
    @endif --}}

    <!-- Password Reset Token -->
    <input type="hidden" name="token" value="{{ $request->route('token') }}">

    <!-- Email -->
    <div class="mb-3">
        <label for="signupEmailInput" class="form-label">
            {{ __('Email') }}
            <span class="text-danger">*</span>
        </label>
        <input type="email" class="form-control" id="signupEmailInput" required name="email"
            placeholder="Enter your email" :value="old('email')" />
        <div class="invalid-feedback">Please enter email.</div>
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="reset-password" class="form-label">
            {{ __('Password') }}
            <span class="text-danger">*</span>
        </label>
        <input type="password" class="form-control" id="reset-password" required name="password"
            :value="old('password')" />

        @if (session('_password'))
            <div class="invalid-feedback">
                {{ session('_password') }}
            </div>
        @endif
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="reset-password" class="form-label">
            {{ __('Confirm Password') }}
            <span class="text-danger">*</span>
        </label>
        <input type="password" class="form-control" id="reset-password" required
            name="password_confirmation" />
    </div>

    <div class="d-grid">
        <button class="btn btn-primary" type="submit">
            {{ __('Reset Password') }}
        </button>
    </div>
</form>
