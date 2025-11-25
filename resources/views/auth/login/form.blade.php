@extends('jiny-auth::layouts.app')

@section('title', '로그인')

@section('content')
    <div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-md w-full space-y-8">
            {{-- 헤더 --}}
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                    로그인
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600">
                    계정이 없으신가요?
                    <a href="{{ route('register') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                        회원가입하기
                    </a>
                </p>
            </div>

            {{-- 알림 메시지 --}}
            @if (session('success'))
                <div class="rounded-md bg-green-50 p-4">
                    <div class="text-sm text-green-700">{{ session('success') }}</div>
                </div>
            @endif

            @if (session('error'))
                <div class="rounded-md bg-red-50 p-4">
                    <div class="text-sm text-red-700">{{ session('error') }}</div>
                </div>
            @endif

            @if (session('info'))
                <div class="rounded-md bg-blue-50 p-4">
                    <div class="text-sm text-blue-700">{{ session('info') }}</div>
                    @if (session('approval_help'))
                        <div class="mt-3 text-xs text-blue-600">
                            <div class="font-medium mb-1">승인 대기 계정 안내:</div>
                            <ul class="list-disc list-inside space-y-1">
                                <li>회원가입 시 입력한 이메일과 비밀번호로 로그인해주세요</li>
                                <li>로그인 후 자동으로 승인 대기 페이지로 이동합니다</li>
                                <li>관리자 승인까지 시간이 소요될 수 있습니다</li>
                            </ul>
                        </div>
                    @endif
                </div>
            @endif

            {{-- 로그인 폼 --}}
            <form class="mt-8 space-y-6" action="{{ route('login.submit') }}" method="POST">
                @csrf

                <div class="rounded-md shadow-sm space-y-4">
                    {{-- 이메일 --}}
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700">
                            이메일
                        </label>
                        <input id="email" name="email" type="email" autocomplete="email" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            value="{{ old('email') }}" placeholder="your@email.com">
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    {{-- 비밀번호 --}}
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700">
                            비밀번호
                        </label>
                        <input id="password" name="password" type="password" autocomplete="current-password" required
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="비밀번호를 입력하세요">
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- 로그인 유지 --}}
                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox"
                            class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <label for="remember" class="ml-2 block text-sm text-gray-900">
                            로그인 상태 유지
                        </label>
                    </div>

                    <div class="text-sm">
                        <a href="{{ route('password.request') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                            비밀번호를 잊으셨나요?
                        </a>
                    </div>
                </div>

                {{-- 제출 버튼 --}}
                <div>
                    <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        로그인
                    </button>
                </div>
            </form>

            {{-- 소셜 로그인 --}}
            @if (class_exists('Jiny\Auth\Social\Models\UserOAuthProvider'))
                @php
                    $socialProviders = \Jiny\Auth\Social\Models\UserOAuthProvider::getEnabled();
                @endphp
                @if ($socialProviders->count() > 0)
                    <div class="mt-6">
                        <div class="relative">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-2 bg-gray-50 text-gray-500">또는</span>
                            </div>
                        </div>

                        <div class="mt-6 grid grid-cols-2 gap-3">
                            @foreach ($socialProviders as $provider)
                                <a href="{{ route('social.login', $provider->provider) }}"
                                    class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                    @if ($provider->provider == 'google')
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path
                                                d="M12.545,10.239v3.821h5.445c-0.712,2.315-2.647,3.972-5.445,3.972c-3.332,0-6.033-2.701-6.033-6.032s2.701-6.032,6.033-6.032c1.498,0,2.866,0.549,3.921,1.453l2.814-2.814C17.503,2.988,15.139,2,12.545,2C7.021,2,2.543,6.477,2.543,12s4.478,10,10.002,10c8.396,0,10.249-7.85,9.426-11.748L12.545,10.239z" />
                                        </svg>
                                    @elseif($provider->provider == 'facebook')
                                        <svg class="h-5 w-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24"
                                            aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M22 12c0-5.523-4.477-10-10-10S2 6.477 2 12c0 4.991 3.657 9.128 8.438 9.878v-6.987h-2.54V12h2.54V9.797c0-2.506 1.492-3.89 3.777-3.89 1.094 0 2.238.195 2.238.195v2.46h-1.26c-1.243 0-1.63.771-1.63 1.562V12h2.773l-.443 2.89h-2.33v6.988C18.343 21.128 22 16.991 22 12z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @elseif($provider->provider == 'github')
                                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                            <path fill-rule="evenodd"
                                                d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @elseif($provider->icon)
                                        <i class="{{ $provider->icon }}"></i>
                                    @endif
                                    <span class="ml-2">{{ ucfirst($provider->provider) }}</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection
