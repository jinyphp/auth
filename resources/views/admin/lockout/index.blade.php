@extends('jiny-admin::layouts.admin')

@section('title', '계정 잠금 관리')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">계정 잠금 관리</h1>
        <p class="mt-1 text-sm text-gray-600">로그인 실패로 잠금된 계정을 관리합니다</p>
    </div>

    {{-- 통계 카드 --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-red-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">전체 잠금</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['total_locked'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-orange-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-orange-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">관리자 해제 필요</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['requires_admin'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-yellow-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-yellow-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">자동 해제 대기</p>
                    <p class="text-2xl font-semibold text-gray-900">{{ $statistics['auto_unlock_pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="flex-shrink-0 bg-indigo-100 rounded-md p-3">
                    <svg class="h-6 w-6 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                    </svg>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-500">단계별 분포</p>
                    <p class="text-sm text-gray-600">
                        @foreach($statistics['by_level'] as $level => $count)
                        Lv{{ $level }}: {{ $count }}
                        @if(!$loop->last) | @endif
                        @endforeach
                    </p>
                </div>
            </div>
        </div>
    </div>

    {{-- 필터 --}}
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('admin.lockouts.index') }}" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" placeholder="이메일 또는 IP 검색"
                       value="{{ request('search') }}"
                       class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
            </div>

            <select name="status" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">전체 상태</option>
                <option value="locked" {{ request('status') === 'locked' ? 'selected' : '' }}>잠금</option>
                <option value="unlocked" {{ request('status') === 'unlocked' ? 'selected' : '' }}>해제</option>
            </select>

            <select name="level" class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                <option value="">전체 단계</option>
                <option value="1" {{ request('level') == 1 ? 'selected' : '' }}>1단계 (15분)</option>
                <option value="2" {{ request('level') == 2 ? 'selected' : '' }}>2단계 (60분)</option>
                <option value="3" {{ request('level') == 3 ? 'selected' : '' }}>3단계 (영구)</option>
            </select>

            <label class="inline-flex items-center">
                <input type="checkbox" name="requires_admin" value="1"
                       {{ request('requires_admin') ? 'checked' : '' }}
                       class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                <span class="ml-2 text-sm text-gray-700">관리자 해제만</span>
            </label>

            <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                검색
            </button>

            <a href="{{ route('admin.lockouts.index') }}" class="px-4 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300">
                초기화
            </a>
        </form>
    </div>

    {{-- 메시지 --}}
    @if(session('success'))
    <div class="bg-green-50 border-l-4 border-green-400 p-4 mb-6">
        <p class="text-sm text-green-700">{{ session('success') }}</p>
    </div>
    @endif

    {{-- 잠금 목록 테이블 --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">이메일</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">IP</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">단계</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">실패횟수</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">상태</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">잠금시간</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">해제시간</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">작업</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($lockouts as $lockout)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lockout->id }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lockout->email }}</td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $lockout->ip_address }}</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                            {{ $lockout->lockout_level == 1 ? 'bg-yellow-100 text-yellow-800' : '' }}
                            {{ $lockout->lockout_level == 2 ? 'bg-orange-100 text-orange-800' : '' }}
                            {{ $lockout->lockout_level == 3 ? 'bg-red-100 text-red-800' : '' }}">
                            {{ $lockout->lockout_level }}단계
                        </span>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $lockout->failed_attempts }}회</td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($lockout->status === 'locked')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            잠금
                        </span>
                        @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            해제
                        </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ $lockout->locked_at ? \Carbon\Carbon::parse($lockout->locked_at)->format('Y-m-d H:i') : '-' }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        @if($lockout->requires_admin_unlock)
                        <span class="text-red-600 font-semibold">관리자 필요</span>
                        @elseif($lockout->unlocks_at)
                        {{ \Carbon\Carbon::parse($lockout->unlocks_at)->format('Y-m-d H:i') }}
                        @else
                        -
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                        <a href="{{ route('admin.lockouts.show', $lockout->id) }}"
                           class="text-indigo-600 hover:text-indigo-900">상세</a>

                        @if($lockout->status === 'locked')
                        <a href="{{ route('admin.lockouts.unlock.form', $lockout->id) }}"
                           class="text-green-600 hover:text-green-900">해제</a>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-6 py-4 text-center text-sm text-gray-500">
                        잠금된 계정이 없습니다.
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- 페이지네이션 --}}
    <div class="mt-4">
        {{ $lockouts->links() }}
    </div>
</div>
@endsection