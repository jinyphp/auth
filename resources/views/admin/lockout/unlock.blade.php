@extends('jiny-admin::layouts.admin')

@section('title', '계정 잠금 해제')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-2xl">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">계정 잠금 해제</h1>
        <p class="mt-1 text-sm text-gray-600">잠금된 계정을 해제합니다</p>
    </div>

    {{-- 계정 정보 --}}
    <div class="bg-white rounded-lg shadow p-6 mb-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">계정 정보</h2>

        <dl class="grid grid-cols-1 gap-4">
            <div>
                <dt class="text-sm font-medium text-gray-500">이메일</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $lockout->email }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $lockout->ip_address }}</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">잠금 단계</dt>
                <dd class="mt-1">
                    <span class="px-3 py-1 inline-flex text-sm leading-5 font-semibold rounded-full
                        {{ $lockout->lockout_level == 1 ? 'bg-yellow-100 text-yellow-800' : '' }}
                        {{ $lockout->lockout_level == 2 ? 'bg-orange-100 text-orange-800' : '' }}
                        {{ $lockout->lockout_level == 3 ? 'bg-red-100 text-red-800' : '' }}">
                        {{ $lockout->lockout_level }}단계
                        ({{ $lockout->lockout_duration > 0 ? $lockout->lockout_duration . '분' : '영구' }})
                    </span>
                </dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">실패 횟수</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $lockout->failed_attempts }}회</dd>
            </div>

            <div>
                <dt class="text-sm font-medium text-gray-500">잠금 시간</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($lockout->locked_at)->format('Y-m-d H:i:s') }}
                </dd>
            </div>

            @if($lockout->unlocks_at)
            <div>
                <dt class="text-sm font-medium text-gray-500">자동 해제 예정</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    {{ \Carbon\Carbon::parse($lockout->unlocks_at)->format('Y-m-d H:i:s') }}
                    <span class="text-gray-500">
                        ({{ \Carbon\Carbon::parse($lockout->unlocks_at)->diffForHumans() }})
                    </span>
                </dd>
            </div>
            @endif
        </dl>
    </div>

    {{-- 해제 폼 --}}
    <div class="bg-white rounded-lg shadow p-6">
        <h2 class="text-lg font-semibold text-gray-900 mb-4">잠금 해제</h2>

        <form method="POST" action="{{ route('admin.lockouts.unlock', $lockout->id) }}">
            @csrf

            <div class="mb-4">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    해제 사유 <span class="text-red-600">*</span>
                </label>
                <textarea id="reason" name="reason" rows="4" required
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          placeholder="예: 본인 확인 완료, 비밀번호 재설정 완료 등">{{ old('reason') }}</textarea>
                @error('reason')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <svg class="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm text-yellow-700">
                            잠금 해제 시 실패 기록이 초기화되며, 사용자가 즉시 로그인할 수 있습니다.
                        </p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end space-x-3">
                <a href="{{ route('admin.lockouts.index') }}"
                   class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    취소
                </a>

                <button type="submit"
                        class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    잠금 해제
                </button>
            </div>
        </form>
    </div>
</div>
@endsection