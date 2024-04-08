<x-app>
    <x-bootstrap>
        <x-page-center>
            <div class="text-center mt-4">
                <h1 class="h2">비밀번호 찾기</h1>
                <p class="lead"></p>
            </div>

            <div class="card">
                <div class="card-body">

                    <!-- Session Status -->
                    @if(Session::has('status'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <div class="alert-message">
                            {{Session::get('status')}}
                        </div>
                    </div>
                    @endif

                    <div class="text-center">
                        {{ __('혹시 비밀번호를 잊어버리셨나요?') }}
                        <br>
                        {{__('걱정하지 마세요. 등록하신 이메일 주소로 비밀번호를 초기화 할 수 있는 링크를 보내드립니다.')}}
                    </div>

                    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                        @csrf

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


