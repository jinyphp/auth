@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', 'My Dashboard')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex flex-row align-items-center justify-content-between">
                    <h1 class="h2 mb-0">My Dashboard</h1>

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



        @includeIf("jiny-auth::home.dashboard.revenue")
    </div>
@endsection

@push('scripts')
    <script src="../assets/libs/apexcharts/dist/apexcharts.min.js"></script>
    <script src="../assets/js/vendors/chart.js"></script>
    <script src="../assets/js/vendors/navbar-nav.js"></script>
@endpush
