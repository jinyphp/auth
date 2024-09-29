<x-app>
    <x-bootstrap>
        <x-page-center>
            <div class="text-center mt-4">
                <h1 class="h2">회원 로그인</h1>
                <p class="lead">Sign in to your account to continue</p>
            </div>

            <!-- Session Status -->
            @if (session('status'))
                <div class="mb-4 font-medium text-sm text-green-600">
                    {{ session('status') }}
                </div>
            @endif

            <div class="card">
                <div class="card-body">
                    @if (session('error'))
                    <div class="alert alert-warning alert-dismissible" role="alert">
                        <div class="alert-message">
                            {{ session('error') }}
                        </div>
                    </div>
                    @endif

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input class="form-control form-control-lg"
                                type="email" name="email"
                                placeholder="Enter your email"
                                :value="old('email')" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Password</label>
                            <input class="form-control form-control-lg"
                                type="password" name="password"
                                placeholder="Enter your password"
                                required>
                            <small>
                                @if (Route::has('password.request'))
                                <a href="{{ route('password.request') }}" class="inline-block text-indigo-600 hover:text-indigo-400">
                                    {{ __('Forgot your password?') }}
                                </a>
                                @endif

                            </small>
                        </div>

                        <div>
                            <div class="form-check align-items-center">
                                <input id="customControlInline"
                                    type="checkbox"
                                    class="form-check-input"
                                    name="remember" checked="">
                                <label class="form-check-label text-small" for="customControlInline"> {{ __('Remember me') }}</label>
                            </div>
                        </div>

                        <div class="d-grid gap-2 mt-3">
                            <button type="submit" class="btn btn-lg btn-primary">
                                {{ __('Log in') }}
                            </button>
                        </div>

                    </form>

                    <br>

                    <div class="text-center">
                        아직 회원이 아니세요? <a href="/register">회원가입</a>
                    </div>
                </div>
            </div>
        </x-page-center>

        {{-- 사이트 설정 --}}
        <x-set-actions></x-set-actions>
        <x-site-setting></x-site-setting>

        {{-- HotKey 단축키 이벤트 --}}
		@livewire('HotKeyEvent')

    </x-bootstrap>

</x-app>

