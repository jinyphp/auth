@extends('jiny-auth::layouts.app')

@section('title', '로그인 중단')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                로그인이 일시 중단되었습니다
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                {{ config('admin.auth.maintenance_message', '시스템 유지보수 중입니다.') }}
            </p>

            <div class="mt-6">
                <a href="{{ route('home') }}"
                   class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    홈으로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>
@endsection