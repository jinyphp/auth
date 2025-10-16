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
        @if(session('success'))
        <div class="rounded-md bg-green-50 p-4">
            <div class="text-sm text-green-700">{{ session('success') }}</div>
        </div>
        @endif

        @if(session('error'))
        <div class="rounded-md bg-red-50 p-4">
            <div class="text-sm text-red-700">{{ session('error') }}</div>
        </div>
        @endif

        @if(session('info'))
        <div class="rounded-md bg-blue-50 p-4">
            <div class="text-sm text-blue-700">{{ session('info') }}</div>
            @if(session('approval_help'))
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
                           value="{{ old('email') }}"
                           placeholder="your@email.com">
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
        @if(config('admin.auth.social.enable'))
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
                @foreach(config('admin.auth.social.providers', []) as $provider => $config)
                    @if($config['enabled'] ?? false)
                    <a href="{{ route('login.social', $provider) }}"
                       class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                        {{ ucfirst($provider) }}
                    </a>
                    @endif
                @endforeach
            </div>
        </div>
        @endif
    </div>
</div>
@endsection