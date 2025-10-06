@extends('jiny-auth::layouts.app')

@section('title', '계정 차단')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                계정이 차단되었습니다
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                이용 약관 위반 또는 비정상적인 활동으로<br>
                계정이 차단되었습니다.
            </p>

            <div class="mt-6 bg-red-50 border border-red-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">차단 사유</h3>
                        <div class="mt-2 text-sm text-red-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>이용 약관 위반</li>
                                <li>비정상적인 계정 활동 감지</li>
                                <li>보안 정책 위반</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <p class="text-sm text-gray-600">
                    차단 해제를 원하시면 고객센터로 문의해주세요.
                </p>

                <a href="mailto:support@example.com"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    고객센터 문의
                </a>

                <a href="{{ route('home') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    홈으로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>
@endsection