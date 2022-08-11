<x-theme theme="default.laravel">
    <x-theme-auth-card>
        <x-slot name="logo">
            <a href="/">
                <x-theme-application-logo class="w-20 h-20 text-gray-500 fill-current" />
            </a>
        </x-slot>

        <div class="mb-4 text-sm text-gray-600">
            {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
        </div>

        <!-- Validation Errors -->
        <x-theme-auth-validation-errors class="mb-4" :errors="$errors" />

        <form method="POST" action="{{ route('password.confirm') }}">
            @csrf

            <!-- Password -->
            <div>
                <x-theme-label for="password" :value="__('Password')" />

                <x-theme-input id="password" class="block w-full mt-1"
                                type="password"
                                name="password"
                                required autocomplete="current-password" />
            </div>

            <div class="flex justify-end mt-4">
                <x-theme-button>
                    {{ __('Confirm') }}
                </x-theme-button>
            </div>
        </form>
    </x-theme-auth-card>
</x-theme>
