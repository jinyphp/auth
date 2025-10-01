@extends('layouts.app')

@section('title', '회원 탈퇴')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                회원 탈퇴
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                정말로 탈퇴하시겠습니까?
            </p>
        </div>

        {{-- 탈퇴 안내 --}}
        <div class="bg-red-50 border-l-4 border-red-400 p-4">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-red-400" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-red-800">탈퇴 시 주의사항</h3>
                    <div class="mt-2 text-sm text-red-700">
                        <ul class="list-disc list-inside space-y-1">
                            <li>회원 탈퇴 시 모든 개인정보가 삭제되며 복구할 수 없습니다.</li>
                            <li>보유 중인 포인트와 전자화폐가 모두 소멸됩니다.</li>
                            <li>작성한 게시글과 댓글은 삭제되지 않습니다.</li>
                            @if($require_approval)
                            <li>관리자 승인 후 탈퇴가 완료됩니다.</li>
                            @else
                            <li>{{ $auto_delete_days }}일 후 자동으로 계정이 삭제됩니다.</li>
                            @endif
                            @if($create_backup)
                            <li>개인정보는 {{ config('admin.auth.account_deletion.backup_retention_days', 90) }}일간 백업 보관됩니다.</li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        @if(session('error'))
        <div class="bg-red-50 border border-red-200 rounded-md p-4">
            <p class="text-sm text-red-700">{{ session('error') }}</p>
        </div>
        @endif

        {{-- 탈퇴 신청 폼 --}}
        <form method="POST" action="{{ route('account.delete.submit') }}" class="bg-white shadow rounded-lg p-6">
            @csrf

            {{-- 탈퇴 사유 --}}
            <div class="mb-6">
                <label for="reason" class="block text-sm font-medium text-gray-700 mb-2">
                    탈퇴 사유 (선택)
                </label>
                <textarea id="reason" name="reason" rows="4"
                          class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                          placeholder="탈퇴 사유를 입력해주세요 (서비스 개선에 참고됩니다)">{{ old('reason') }}</textarea>
                @error('reason')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 비밀번호 확인 --}}
            <div class="mb-6">
                <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                    비밀번호 확인 <span class="text-red-600">*</span>
                </label>
                <input type="password" id="password" name="password" required
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="본인 확인을 위해 비밀번호를 입력해주세요">
                @error('password')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 동의 체크박스 --}}
            <div class="mb-6">
                <label class="flex items-start">
                    <input type="checkbox" name="confirm" value="1" required
                           class="mt-1 rounded border-gray-300 text-red-600 focus:ring-red-500">
                    <span class="ml-2 text-sm text-gray-700">
                        위 안내사항을 모두 확인하였으며, 탈퇴에 동의합니다.
                    </span>
                </label>
                @error('confirm')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 버튼 --}}
            <div class="flex justify-end space-x-3">
                <a href="{{ route('home') }}"
                   class="px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
                    취소
                </a>

                <button type="submit"
                        class="px-4 py-2 border border-transparent text-sm font-medium rounded-md text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    탈퇴 신청
                </button>
            </div>
        </form>
    </div>
</div>
@endsection