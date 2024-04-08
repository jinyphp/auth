{{-- OAuth 소셜 로그인 --}}
@if (is_package('jiny/social'))
    <x-social-login>
        <span>Sign in with your social network.</span>
    </x-social-login>
@endif
