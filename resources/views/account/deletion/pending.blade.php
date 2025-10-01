@extends('layouts.app')

@section('title', '탈퇴 신청 진행 중')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-orange-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                회원 탈퇴 신청 진행 중
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                회원 탈퇴 신청이 진행 중입니다.
            </p>

            <div class="mt-6 bg-orange-50 border border-orange-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-orange-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <h3 class="text-sm font-medium text-orange-800">탈퇴 진행 상태</h3>
                        <div class="mt-2 text-sm text-orange-700">
                            <p class="mb-2">신청일: {{ session('deletion_requested_at', now())->format('Y-m-d H:i') }}</p>

                            @if(config('admin.auth.account_deletion.require_approval'))
                            <p>관리자 승인 대기 중입니다.</p>
                            @else
                            <p>
                                자동 삭제 예정일:
                                {{ session('deletion_auto_delete_at', now()->addDays(30))->format('Y-m-d') }}
                                ({{ session('deletion_remaining_days', 30) }}일 남음)
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 bg-blue-50 rounded-lg p-6">
                <h3 class="text-sm font-semibold text-blue-900 mb-3">안내</h3>
                <ul class="text-sm text-blue-800 space-y-2 text-left">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        탈퇴 신청 기간 중에는 로그인이 제한됩니다.
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        탈퇴를 취소하려면 고객센터로 문의하세요.
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-600 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        삭제 예정일 전까지 취소가 가능합니다.
                    </li>
                </ul>
            </div>

            <div class="mt-6 space-y-3">
                <a href="mailto:{{ config('mail.support_email', 'support@example.com') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                    탈퇴 취소 문의
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