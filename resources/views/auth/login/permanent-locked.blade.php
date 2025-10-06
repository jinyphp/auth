@extends('jiny-auth::layouts.app')

@section('title', '계정 영구 잠금')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                계정이 영구 잠금되었습니다
            </h2>

            <div class="mt-6 bg-red-50 border-l-4 border-red-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <p class="text-sm font-medium text-red-800">
                            보안을 위해 계정이 영구적으로 잠금되었습니다.
                        </p>
                        <p class="mt-2 text-sm text-red-700">
                            {{ session('error') ?? '여러 번의 로그인 실패로 계정이 잠금되었습니다.' }}
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-8 bg-gray-50 rounded-lg p-6 border border-gray-200">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">잠금 사유</h3>
                <ul class="text-sm text-gray-700 space-y-2 text-left">
                    <li class="flex items-start">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-red-100 text-red-800 mr-3 mt-0.5">
                            <span class="text-xs font-semibold">1</span>
                        </span>
                        비밀번호를 5회 잘못 입력하여 15분 잠금
                    </li>
                    <li class="flex items-start">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-red-100 text-red-800 mr-3 mt-0.5">
                            <span class="text-xs font-semibold">2</span>
                        </span>
                        추가로 10회 잘못 입력하여 60분 잠금
                    </li>
                    <li class="flex items-start">
                        <span class="inline-flex items-center justify-center h-6 w-6 rounded-full bg-red-500 text-white mr-3 mt-0.5">
                            <span class="text-xs font-semibold">3</span>
                        </span>
                        <strong>15회 이상 잘못 입력하여 영구 잠금 (현재 단계)</strong>
                    </li>
                </ul>
            </div>

            <div class="mt-8 bg-blue-50 rounded-lg p-6 border border-blue-200">
                <h3 class="text-sm font-semibold text-blue-900 mb-3">잠금 해제 방법</h3>
                <div class="text-sm text-blue-800 space-y-3 text-left">
                    <p class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>
                            관리자의 승인이 필요합니다.<br>
                            아래 고객센터로 문의하여 계정 잠금 해제를 요청하세요.
                        </span>
                    </p>
                    <p class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                        <span>
                            문의 시 필요 정보:<br>
                            - 등록된 이메일 주소<br>
                            - 본인 확인 정보
                        </span>
                    </p>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <a href="mailto:{{ config('mail.support_email', 'support@example.com') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    고객센터 이메일 문의
                </a>

                <a href="tel:{{ config('support.phone', '1588-0000') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                    고객센터 전화 문의
                </a>

                <a href="{{ route('password.request') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    비밀번호 재설정
                </a>

                <a href="{{ route('home') }}"
                   class="block text-sm text-gray-500 hover:text-gray-700 mt-4">
                    홈으로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>
@endsection