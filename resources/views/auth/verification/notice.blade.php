@extends('layouts.app')

@section('title', '이메일 인증')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                이메일 인증이 필요합니다
            </h2>

            @if(session('success'))
            <div class="mt-4 rounded-md bg-green-50 p-4">
                <div class="text-sm text-green-700">{{ session('success') }}</div>
            </div>
            @endif

            @if(session('warning'))
            <div class="mt-4 rounded-md bg-yellow-50 p-4">
                <div class="text-sm text-yellow-700">{{ session('warning') }}</div>
            </div>
            @endif

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3 text-left">
                        <h3 class="text-sm font-medium text-blue-800">인증 안내</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <p class="mb-2">
                                <strong>{{ auth()->user()->email }}</strong><br>
                                위 이메일로 인증 링크를 발송했습니다.
                            </p>
                            <ul class="list-disc pl-5 space-y-1">
                                <li>이메일 확인 후 인증 링크를 클릭해주세요.</li>
                                <li>인증 링크는 24시간 동안 유효합니다.</li>
                                <li>스팸 메일함도 확인해주세요.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <form action="{{ route('verification.resend') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        인증 이메일 재발송
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        로그아웃
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection