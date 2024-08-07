<form method="POST" action="{{ route('password.email') }}" class="space-y-6">
    @csrf

    <!-- Session Status -->
    @if(Session::has('status'))
    <div class="alert alert-warning alert-dismissible" role="alert">
        <div class="alert-message">
            {{Session::get('status')}}
        </div>
    </div>
    @endif

    <div class="mb-3">
        <label for="signupEmailInput" class="form-label">
            {{ __('Email') }}
            <span class="text-danger">*</span>
        </label>
        <input type="email" class="form-control"
            id="signupEmailInput"
            required name="email"
            placeholder="Enter your email"
            :value="old('email')" />
        <div class="invalid-feedback">Please enter email.</div>
    </div>

    <div class="d-grid">
        <button class="btn btn-primary" type="submit">{{ __('Email Password Reset Link') }}</button>
    </div>
</form>
