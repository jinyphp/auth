@extends('layouts.app')

@section('title', '계정 일시 잠금')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-16 w-16 text-yellow-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                계정이 일시적으로 잠금되었습니다
            </h2>

            <div class="mt-6 bg-yellow-50 border-l-4 border-yellow-400 p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <p class="text-sm text-yellow-700">
                            {{ session('error') ?? '비밀번호를 여러 번 잘못 입력하여 계정이 일시적으로 잠금되었습니다.' }}
                        </p>
                    </div>
                </div>
            </div>

            @if(session('remaining_minutes'))
            <div class="mt-6">
                <div class="text-4xl font-bold text-gray-900" id="countdown">
                    {{ session('remaining_minutes') }}분
                </div>
                <p class="text-sm text-gray-500 mt-2">남은 시간</p>
            </div>
            @endif

            @if(session('unlocks_at'))
            <div class="mt-4 text-sm text-gray-600">
                {{ session('unlocks_at')->format('Y-m-d H:i') }}에 자동으로 해제됩니다.
            </div>
            @endif

            <div class="mt-8 bg-blue-50 rounded-lg p-6">
                <h3 class="text-sm font-medium text-blue-900 mb-3">안내 사항</h3>
                <ul class="text-sm text-blue-700 space-y-2 text-left">
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        지정된 시간 후 자동으로 잠금이 해제됩니다.
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        비밀번호를 잊으신 경우 비밀번호 재설정을 이용하세요.
                    </li>
                    <li class="flex items-start">
                        <svg class="h-5 w-5 text-blue-500 mr-2 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        반복적인 잠금 시 영구 잠금될 수 있습니다.
                    </li>
                </ul>
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ route('password.request') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    비밀번호 재설정
                </a>

                <a href="{{ route('home') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    홈으로 돌아가기
                </a>
            </div>
        </div>
    </div>
</div>

@if(session('remaining_minutes'))
@push('scripts')
<script>
// 카운트다운 타이머
let remainingSeconds = {{ session('remaining_minutes') * 60 }};

setInterval(function() {
    if (remainingSeconds > 0) {
        remainingSeconds--;
        const minutes = Math.floor(remainingSeconds / 60);
        const seconds = remainingSeconds % 60;
        document.getElementById('countdown').textContent =
            minutes + '분 ' + seconds + '초';
    } else {
        location.reload(); // 시간 만료 시 페이지 새로고침
    }
}, 1000);
</script>
@endpush
@endif
@endsection