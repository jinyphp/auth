@extends('layouts.app')

@section('title', '휴면 계정 재활성화')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                휴면 계정 재활성화
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                {{ config('admin.auth.login.dormant_days', 365) }}일 이상 미접속으로<br>
                계정이 휴면 상태로 전환되었습니다.
            </p>
        </div>

        <div class="bg-yellow-50 border border-yellow-200 rounded-md p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">재활성화 안내</h3>
                    <div class="mt-2 text-sm text-yellow-700">
                        <p>본인 확인을 위해 이메일과 비밀번호를 입력해주세요.</p>
                    </div>
                </div>
            </div>
        </div>

        <form class="mt-8 space-y-6" action="{{ route('account.reactivate.submit') }}" method="POST">
            @csrf

            @if(session('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-700">{{ session('error') }}</div>
            </div>
            @endif

            <div class="space-y-4">
                {{-- 이메일 --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        이메일
                    </label>
                    <input id="email" name="email" type="email" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('email') }}">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 비밀번호 --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        비밀번호
                    </label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- 제출 버튼 --}}
            <div>
                <button type="submit"
                        class="w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    계정 재활성화
                </button>
            </div>

            <div class="text-center">
                <a href="{{ route('password.request') }}" class="text-sm text-indigo-600 hover:text-indigo-500">
                    비밀번호를 잊으셨나요?
                </a>
            </div>
        </form>
    </div>
</div>
@endsection