@extends('jiny-auth::layouts.app')

@section('title', '승인 대기')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <svg class="mx-auto h-12 w-12 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>

            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                계정 승인 대기 중
            </h2>

            <p class="mt-2 text-sm text-gray-600">
                회원가입이 완료되었습니다.<br>
                관리자의 승인 후 서비스를 이용하실 수 있습니다.
            </p>

            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-md p-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-800">승인 안내</h3>
                        <div class="mt-2 text-sm text-blue-700">
                            <ul class="list-disc pl-5 space-y-1">
                                <li>승인은 영업일 기준 1-2일 소요됩니다.</li>
                                <li>승인 완료 시 이메일로 안내드립니다.</li>
                                <li>문의사항은 고객센터로 연락주세요.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ route('home') }}"
                   class="w-full inline-flex justify-center items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    홈으로 돌아가기
                </a>

                @auth
                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit"
                            class="w-full inline-flex justify-center items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                        로그아웃
                    </button>
                </form>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection