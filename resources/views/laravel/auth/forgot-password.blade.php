<x-theme theme="default.laravel">
    <x-theme-auth-card>
        <x-slot name="logo"></x-slot>

        @if(Session::has('status'))
            <div class="mb-4 text-sm font-medium text-green-600">{{Session::get('status')}}</div>
        @endif

        <div class="mb-4 text-sm text-gray-600">
            {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
        </div>

        <!-- Session Status -->
        <x-theme-auth-session-status class="mb-4" :status="session('status')" />

        <!-- Validation Errors -->
        <x-theme-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <!-- Email Address -->
            <div>
                <x-theme-label for="email" :value="__('Email')" />

                <x-theme-input id="email" class="block w-full mt-1" type="email" name="email" :value="old('email')" required autofocus />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-theme-button>
                    {{ __('Email Password Reset Link') }}
                </x-theme-button>
            </div>
        </form>
    </x-theme-auth-card>
</x-theme>
