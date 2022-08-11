<x-theme theme="default.laravel">
    <x-theme-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-theme-application-logo class="w-20 h-20 text-gray-500 fill-current" />
            </a>
        </x-slot>

        <!-- Validation Errors -->
        <x-theme-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.update') }}">
            @csrf

            <!-- Password Reset Token -->
            <input type="hidden" name="token" value="{{ $request->route('token') }}">

            <!-- Email Address -->
            <div>
                <x-theme-label for="email" :value="__('Email')" />

                <x-theme-input id="email" class="block w-full mt-1" type="email" name="email" :value="old('email', $request->email)" required autofocus />
            </div>

            <!-- Password -->
            <div class="mt-4">
                <x-theme-label for="password" :value="__('Password')" />

                <x-theme-input id="password" class="block w-full mt-1" type="password" name="password" required />
            </div>

            <!-- Confirm Password -->
            <div class="mt-4">
                <x-theme-label for="password_confirmation" :value="__('Confirm Password')" />

                <x-theme-input id="password_confirmation" class="block w-full mt-1"
                                    type="password"
                                    name="password_confirmation" required />
            </div>

            <div class="flex items-center justify-end mt-4">
                <x-theme-button>
                    {{ __('Reset Password') }}
                </x-theme-button>
            </div>
        </form>
    </x-theme-auth-card>
</x-theme>
