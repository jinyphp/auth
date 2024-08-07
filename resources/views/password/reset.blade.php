<x-app>
    <x-bootstrap>
        <x-page-center>
            <div class="text-center mt-4">
                <h1 class="h2">비밀번호 변경</h1>
                <p class="lead"></p>
            </div>



        <!-- Validation Errors -->
        @if ($errors->any())
        <div class="mb-4">
            <div class="font-medium text-red-600">
                {{ __('Whoops! Something went wrong.') }}
            </div>

            <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
        @endif

        <div class="card">
            <div class="card-body">



                <form method="POST" action="{{ route('password.update') }}">
                    @csrf

                    <!-- Session Status -->
                @if(Session::has('status'))
                <div class="alert alert-warning alert-dismissible" role="alert">
                    <div class="alert-message">
                        {{Session::get('status')}}
                    </div>
                </div>
                @endif

                    <!-- Password Reset Token -->
                    <input type="hidden" name="token" value="{{ $request->route('token') }}">

                    <!-- Email -->
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

                    <!-- Password -->
                    <div class="mb-3">
                        <label for="reset-password" class="form-label">
                            {{ __('Password') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control"
                            id="reset-password"
                            required name="password"
                            :value="old('password')" />
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-3">
                        <label for="reset-password" class="form-label">
                            {{ __('Confirm Password') }}
                            <span class="text-danger">*</span>
                        </label>
                        <input type="password" class="form-control"
                            id="reset-password"
                            required name="password_confirmation" />
                    </div>

                    <div class="d-grid">
                        <button class="btn btn-primary" type="submit">
                            {{ __('Reset Password') }}
                        </button>
                    </div>
                </form>

                <div class="mt-3">
                    <div class="text-center">
                        <a class="font-medium text-indigo-600 hover:text-indigo-400" href="/login">로그인 이동</a>
                    </div>
                </div>

            </div>
        </div>
        </x-page-center>
    </x-bootstrap>
</x-app>
