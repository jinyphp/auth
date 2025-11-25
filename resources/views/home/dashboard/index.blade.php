@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', 'My Dashboard')

@section('content')
    <div class="container mb-4">

        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <h1 class="h2 mb-0">My Dashboard</h1>

                    <div class="d-flex gap-2">
                        @php
                            // 파트너 패키지가 설치되어 있는지 확인
                            $hasPartnerPackage =
                                class_exists('\Jiny\Partner\Models\PartnerUser') ||
                                class_exists('\Jiny\Partner\Models\PartnerApplication');

                            // 현재 사용자의 파트너 상태 확인
                            $partnerUser = null;
                            $partnerApplication = null;

                            if ($hasPartnerPackage && $user && $user->uuid) {
                                try {
                                    $partnerUser = \Jiny\Partner\Models\PartnerUser::where(
                                        'user_uuid',
                                        $user->uuid,
                                    )->first();
                                    if (!$partnerUser) {
                                        $partnerApplication = \Jiny\Partner\Models\PartnerApplication::where(
                                            'user_uuid',
                                            $user->uuid,
                                        )
                                            ->latest()
                                            ->first();
                                    }
                                } catch (\Exception $e) {
                                    // 테이블이 없거나 오류 발생시 무시
                                }
                            }
                        @endphp

                        @if ($hasPartnerPackage)
                            @if ($partnerUser)
                                <!-- 등록된 파트너인 경우 - 파트너 대시보드로 이동 -->
                                <a href="{{ route('home.partner.index') }}" class="btn btn-success d-flex flex-row gap-2">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-briefcase" viewBox="0 0 16 16">
                                            <path
                                                d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.864 7.85a.5.5 0 0 1-.258 0L1.5 6.116V4.5a.5.5 0 0 1 .5-.5z" />
                                        </svg>
                                    </span>
                                    <span>파트너 대시보드</span>
                                </a>
                            @elseif($partnerApplication)
                                <!-- 신청한 상태인 경우 - 신청 상태 확인으로 이동 -->
                                <a href="{{ route('home.partner.regist.status', $partnerApplication->id) }}"
                                    class="btn btn-warning d-flex flex-row gap-2">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-clock-history" viewBox="0 0 16 16">
                                            <path
                                                d="M8.515 1.019A7 7 0 0 0 8 1V0a8 8 0 0 1 .589.022l-.074.997zm2.004.45a7.003 7.003 0 0 0-.985-.299l.219-.976c.383.086.76.2 1.126.342l-.36.933zm1.37.71a7.01 7.01 0 0 0-.439-.27l.493-.87a8.025 8.025 0 0 1 .979.654l-.615.789a6.996 6.996 0 0 0-.418-.302zm1.834 1.79a6.99 6.99 0 0 0-.653-.796l.724-.69c.27.285.52.59.747.91l-.818.576zm.744 1.352a7.08 7.08 0 0 0-.214-.468l.893-.45a7.976 7.976 0 0 1 .45 1.088l-.95.313a7.023 7.023 0 0 0-.179-.483zm.53 2.507a6.991 6.991 0 0 0-.1-1.025l.985-.17c.067.386.106.778.116 1.17l-1.001.025zm-.131 1.538c.033-.17.06-.339.081-.51l.993.123a7.957 7.957 0 0 1-.23 1.155l-.964-.267c.046-.165.086-.332.12-.501zm-.952 2.379c.184-.29.346-.594.486-.908l.914.405c-.16.36-.345.706-.555 1.038l-.845-.535zm-.964 1.205c.122-.122.239-.248.35-.378l.758.653a8.073 8.073 0 0 1-.401.432l-.707-.707z" />
                                            <path d="M8 1a7 7 0 1 0 4.95 11.95l.707.707A8.001 8.001 0 1 1 8 0v1z" />
                                            <path
                                                d="M7.5 3a.5.5 0 0 1 .5.5v5.21l3.248 1.856a.5.5 0 0 1-.496.868l-3.5-2A.5.5 0 0 1 7 9V3.5a.5.5 0 0 1 .5-.5z" />
                                        </svg>
                                    </span>
                                    <span>신청 상태 확인</span>
                                </a>
                            @else
                                <!-- 파트너 신청하지 않은 경우 - 파트너 소개로 이동 -->
                                <a href="{{ route('home.partner.intro') }}" class="btn btn-info d-flex flex-row gap-2">
                                    <span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16"
                                            fill="currentColor" class="bi bi-person-plus" viewBox="0 0 16 16">
                                            <path
                                                d="M6 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H1s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C9.516 10.68 8.289 10 6 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z" />
                                            <path fill-rule="evenodd"
                                                d="M13.5 5a.5.5 0 0 1 .5.5V7h1.5a.5.5 0 0 1 0 1H14v1.5a.5.5 0 0 1-1 0V8h-1.5a.5.5 0 0 1 0-1H13V5.5a.5.5 0 0 1 .5-.5z" />
                                        </svg>
                                    </span>
                                    <span>파트너 가입</span>
                                </a>
                            @endif
                        @endif

                        <a href="#!" class="btn btn-primary d-flex flex-row gap-2">
                            <span>
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                    class="bi bi-stars" viewBox="0 0 16 16">
                                    <path
                                        d="M7.657 6.247c.11-.33.576-.33.686 0l.645 1.937a2.89 2.89 0 0 0 1.829 1.828l1.936.645c.33.11.33.576 0 .686l-1.937.645a2.89 2.89 0 0 0-1.828 1.829l-.645 1.936a.361.361 0 0 1-.686 0l-.645-1.937a2.89 2.89 0 0 0-1.828-1.828l-1.937-.645a.361.361 0 0 1 0-.686l1.937-.645a2.89 2.89 0 0 0 1.828-1.828zM3.794 1.148a.217.217 0 0 1 .412 0l.387 1.162c.173.518.579.924 1.097 1.097l1.162.387a.217.217 0 0 1 0 .412l-1.162.387A1.73 1.73 0 0 0 4.593 5.69l-.387 1.162a.217.217 0 0 1-.412 0L3.407 5.69A1.73 1.73 0 0 0 2.31 4.593l-1.162-.387a.217.217 0 0 1 0-.412l1.162-.387A1.73 1.73 0 0 0 3.407 2.31zM10.863.099a.145.145 0 0 1 .274 0l.258.774c.115.346.386.617.732.732l.774.258a.145.145 0 0 1 0 .274l-.774.258a1.16 1.16 0 0 0-.732.732l-.258.774a.145.145 0 0 1-.274 0l-.258-.774a1.16 1.16 0 0 0-.732-.732L9.1 2.137a.145.145 0 0 1 0-.274l.774-.258c.346-.115.617-.386.732-.732z">
                                    </path>
                                </svg>
                            </span>
                            <span>Upgrade</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- User Profile & Connection Info Card -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Avatar -->
                            <div class="col-auto">
                                <a href="{{ route('home.account.avatar') }}" title="아바타 변경" style="text-decoration: none;">
                                    @if ($user->avatar)
                                        <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="rounded-circle"
                                            style="width: 80px; height: 80px; object-fit: cover; cursor: pointer;">
                                    @else
                                        <div class="rounded-circle bg-primary d-flex align-items-center justify-content-center text-white"
                                            style="width: 80px; height: 80px; font-size: 32px; font-weight: bold; cursor: pointer;">
                                            {{ mb_substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                </a>
                            </div>

                            <!-- User Info -->
                            <div class="col">
                                <h3 class="mb-1">{{ $user->name }}</h3>
                                <p class="text-muted mb-2">{{ $user->email }}</p>
                                <div class="d-flex gap-3 flex-wrap">
                                    <span class="badge bg-success">{{ $user->status ?? 'active' }}</span>
                                    @if ($user->grade)
                                        <span class="badge bg-info">{{ $user->grade }}</span>
                                    @endif
                                </div>
                            </div>

                            <!-- Connection Info -->
                            <div class="col-lg-4">
                                <div class="small">
                                    <!-- JWT 인증 정보 -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">인증 방식:</span>
                                        <span class="fw-semibold">
                                            <span class="badge bg-primary">{{ $jwtInfo['auth_method'] }}</span>
                                            @if ($jwtInfo['has_access_token'])
                                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14"
                                                    fill="currentColor" class="bi bi-check-circle-fill text-success"
                                                    viewBox="0 0 16 16">
                                                    <path
                                                        d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z" />
                                                </svg>
                                            @endif
                                        </span>
                                    </div>

                                    <!-- 로그인 타입 -->
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">로그인 타입:</span>
                                        <span class="fw-semibold">
                                            @if ($loginType === 'social')
                                                <span class="badge bg-info">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                        fill="currentColor" class="bi bi-google" viewBox="0 0 16 16">
                                                        <path
                                                            d="M15.545 6.558a9.4 9.4 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.7 7.7 0 0 1 5.352 2.082l-2.284 2.284A4.35 4.35 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.8 4.8 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.7 3.7 0 0 0 1.599-2.431H8v-3.08z" />
                                                    </svg>
                                                    {{ ucfirst($socialProvider) }}
                                                </span>
                                            @else
                                                <span class="badge bg-secondary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12"
                                                        fill="currentColor" class="bi bi-envelope-fill" viewBox="0 0 16 16">
                                                        <path
                                                            d="M.05 3.555A2 2 0 0 1 2 2h12a2 2 0 0 1 1.95 1.555L8 8.414zM0 4.697v7.104l5.803-3.558zM6.761 8.83l-6.57 4.027A2 2 0 0 0 2 14h12a2 2 0 0 0 1.808-1.144l-6.57-4.027L8 9.586zm3.436-.586L16 11.801V4.697z" />
                                                    </svg>
                                                    이메일
                                                </span>
                                            @endif
                                        </span>
                                    </div>

                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">현재 IP:</span>
                                        <span class="fw-semibold">{{ $connectionInfo['ip'] }}</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-2">
                                        <span class="text-muted">총 로그인 횟수:</span>
                                        <span
                                            class="fw-semibold">{{ number_format($connectionInfo['login_count']) }}회</span>
                                    </div>
                                    @if ($connectionInfo['last_login_at'])
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="text-muted">마지막 로그인:</span>
                                            <span
                                                class="fw-semibold">{{ $connectionInfo['last_login_at']->format('Y-m-d H:i') }}</span>
                                        </div>
                                    @endif
                                    @if ($connectionInfo['last_activity_at'])
                                        <div class="d-flex justify-content-between">
                                            <span class="text-muted">마지막 활동:</span>
                                            <span
                                                class="fw-semibold">{{ $connectionInfo['last_activity_at']->diffForHumans() }}</span>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 이머니 & 포인트 정보 -->
        @include('jiny-auth::home.dashboard.emoney-content')

        {{-- @include('jiny-auth::home.dashboard.partner') --}}

        <!-- Recent Login History -->
        @if ($recentLogins && count($recentLogins) > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">최근 로그인 기록</h4>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>일시</th>
                                            <th>IP 주소</th>
                                            <th>브라우저</th>
                                            <th>상태</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($recentLogins as $login)
                                            <tr>
                                                <td>{{ \Carbon\Carbon::parse($login->attempted_at)->format('Y-m-d H:i:s') }}
                                                </td>
                                                <td>{{ $login->ip_address }}</td>
                                                <td class="text-truncate" style="max-width: 200px;">
                                                    {{ $login->user_agent ?? '-' }}</td>
                                                <td>
                                                    @if ($login->successful)
                                                        <span class="badge bg-success">성공</span>
                                                    @else
                                                        <span class="badge bg-danger">실패</span>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif



        @includeIf('jiny-auth::home.dashboard.revenue')
    </div>
@endsection

@push('scripts')
    <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
    <script src="../assets/js/vendors/chart.js"></script>
    <script src="../assets/js/vendors/navbar-nav.js"></script>
@endpush
