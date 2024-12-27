<div class="row">
    <div class="col-xl-3 col-lg-4">
        <div class="card tilebox-one">
            <div class="card-body">
                <a href="/admin/auth/users" class="text-decoration-none">
                    <h6 class="text-uppercase mt-0">활성 사용자</h6>
                    <h2 class="my-2" id="active-users-count">{{ user_count() }} 명</h2>
                </a>

                <div>
                    <a href="/admin/auth/unregist" class="badge bg-danger">
                        탈퇴회원
                    </a>

                    <a href="/admin/auth/auth" class="badge bg-secondary">
                        승인대기
                    </a>

                    <a href="/admin/auth/grade" class="badge bg-secondary">
                        등급
                    </a>

                    <a href="/admin/auth/roles" class="badge bg-secondary">
                        역할
                    </a>

                    <a href="/admin/auth/country" class="badge bg-secondary">
                        국가
                    </a>


                </div>
            </div> <!-- end card-body-->
        </div>
        <!--end card-->

        <div class="card tilebox-one">
            <div class="card-body">
                <a href="/admin/auth/log/count" class="text-decoration-none">
                    <h6 class="text-uppercase mt-0">
                        방문자
                    </h6>
                </a>

                <h2 class="my-2" id="active-views-count">

                    <a href="/admin/auth/log/daily" class="text-decoration-none">
                        {{ $logTotal = Jiny\Auth\User::getLogTotal() }} 명
                    </a>


                </h2>
                <p class="mb-0 text-muted">
                    <span class="text-nowrap">지난주보다 </span>
                    <span class="text-danger me-2">
                        <span class="mdi mdi-arrow-down-bold">
                        </span>
                        {{ $logLastWeekCount = Jiny\Auth\User::getLogLastWeekCount() }}
                        건
                    </span>
                    <span class="text-nowrap">증감</span>
                </p>
            </div> <!-- end card-body-->
        </div>
        <!--end card-->

        <div class="card tilebox-one">
            <div class="card-body">
                <a href="/admin/auth/oauth" class="text-decoration-none">
                    <h6 class="text-uppercase mt-0">소셜연동</h6>
                </a>
                <h2 class="my-2" id="active-views-count">
                    <a href="/admin/auth/oauth/users" class="text-decoration-none">
                        {{ DB::table('user_oauth')->count() }} 명
                    </a>
                </h2>
                <div class="mb-0 d-flex flex-wrap gap-2">
                    @foreach (DB::table('user_oauth_providers')->get() as $item)
                        <a href="/admin/auth/oauth/users/{{ $item->provider }}" class="text-decoration-none">
                            <span class="badge bg-secondary">{{ $item->provider }}</span>
                        </a>
                    @endforeach
                </div>
            </div> <!-- end card-body-->
        </div>
        <!--end card-->


    </div> <!-- end col -->

    <div class="col-xl-9 col-lg-8">
        <div class="card card-h-100">
            <div class="card-body">
                {{-- <ul class="nav float-end d-none d-lg-flex">
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">Today</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">7d</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="#">15d</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">1m</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link text-muted" href="#">1y</a>
                    </li>
                </ul> --}}
                <h4 class="header-title mb-3">일일 방문자</h4>

                <div>
                    <canvas id="userLogChart" style="width:100%; height: 350px;"></canvas>
                    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
                    {{-- @vite(['resources/js/chart.js']) --}}
                    @php
                        $logs = DB::table('user_log_daily')
                            ->orderBy('year')
                            ->orderBy('month')
                            ->orderBy('day')
                            ->limit(15)
                            ->get();

                        $labels = '';
                        foreach ($logs as $i => $log) {
                            if ($i > 0) {
                                $labels .= ',';
                            }
                            $labels .= "'" . $log->month . '/' . $log->day . "'";
                        }
                    @endphp
                    <script>
                        // 차트 데이터 준비
                        var ctx = document.getElementById('userLogChart').getContext('2d');
                        var chart = new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: [
                                    {!! $labels !!}
                                ],
                                datasets: [{
                                    label: '일일 접속자수',
                                    data: [
                                        @php
                                            foreach ($logs as $i => $log) {
                                                if ($i > 0) {
                                                    echo ',';
                                                }
                                                echo $log->cnt;
                                            }
                                        @endphp
                                    ],
                                    borderColor: 'rgb(75, 192, 192)',
                                    tension: 0.1
                                }]
                            },
                            options: {
                                responsive: true,
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            stepSize: 1,
                                            precision: 0
                                        }
                                    }
                                }
                            }
                        });
                    </script>
                </div>
            </div> <!-- end card-body-->
        </div> <!-- end card-->
    </div>
</div>




<div class="row">
    {{-- 회원별 지역국가 --}}
    <div class="col-xl-6 col-xxl-4">
        <div class="card flex-fill w-100">
            <div class="card-header">
                <a href="/admin/auth/country" class="text-decoration-none">
                    <h5 class="card-title mb-0">회원별 지역국가</h5>
                </a>
            </div>
            <table class="table table-striped my-0">
                <thead>
                    <tr>
                        <th>국가</th>
                        <th class="text-end">Users</th>
                        <th class="d-none d-xl-table-cell w-50">% Users</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $total_users = user_count();
                    @endphp
                    @foreach (DB::table('user_country')->limit(5)->get() as $item)
                        <tr>
                            <td>
                                <a href="/admin/auth/locale" class="text-decoration-none">
                                    {{ $item->name }}
                                </a>
                            </td>
                            <td class="text-end">
                                {{ $item->users }}
                            </td>
                            <td class="d-none d-xl-table-cell">
                                {{-- <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 43%;"
                                        aria-valuenow="43" aria-valuemin="0" aria-valuemax="100">43%</div>
                                </div> --}}

                                @php
                                    if ($item->users > 0 && $total_users > 0) {
                                        $percent = ($item->users / $total_users) * 100;
                                        $percent = round($percent, 2);
                                    } else {
                                        $percent = 0;
                                    }

                                @endphp
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar"
                                        style="width: {{ $percent }}%" aria-valuenow="{{ $percent }}"
                                        aria-valuemin="0" aria-valuemax="100">{{ $percent }}%</div>
                                </div>
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xl-6 col-xxl-4">
        <div class="card flex-fill w-100">
            <div class="card-header">
                <a href="/admin/auth/grade" class="text-decoration-none">
                    <h5 class="card-title mb-0">등급</h5>
                </a>
            </div>
            <table class="table table-striped my-0">
                <thead>
                    <tr>
                        <th>등급</th>
                        <th class="text-end">Users</th>
                        <th class="d-none d-xl-table-cell w-50">% Users</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (table_rows('user_grade') as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-end">{{ $item->users }}</td>
                            <td class="d-none d-xl-table-cell">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 43%;"
                                        aria-valuenow="43" aria-valuemin="0" aria-valuemax="100">43%</div>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    <div class="col-xl-6 col-xxl-4">
        <div class="card flex-fill w-100">
            <div class="card-header">
                <a href="/admin/permit/roles" class="text-decoration-none">
                    <h5 class="card-title mb-0">역할</h5>
                </a>
            </div>
            <table class="table table-striped my-0">
                <thead>
                    <tr>
                        <th>역할</th>
                        <th class="text-end">Users</th>
                        <th class="d-none d-xl-table-cell w-50">% Users</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (table_rows('roles') as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td class="text-end">{{ $item->users }}</td>
                            <td class="d-none d-xl-table-cell">
                                <div class="progress">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 43%;"
                                        aria-valuenow="43" aria-valuemin="0" aria-valuemax="100">43%</div>
                                </div>
                            </td>
                        </tr>
                    @endforeach

                </tbody>
            </table>
        </div>
    </div>


</div>



<div class="row">
    {{-- 패스워드 만료 --}}
    <div class="col-12 col-lg-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <div class="card-actions float-end">
                    <a href="/admin/auth/password">
                        <button class="btn btn-sm btn-light">View all</button>
                    </a>
                </div>
                <h5 class="card-title mb-0">패스워드 만료</h5>
            </div>
            <table class="table table-sm table-striped my-0">
                <thead>
                    <tr>
                        <th>이메일</th>
                        <th>만료일자</th>
                    </tr>
                </thead>
                <tbody class="text-end">
                    @foreach (DB::table('user_password')->limit(5)->get() as $item)
                        <tr>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->expire }}</td>

                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 휴면승인 --}}
    <div class="col-12 col-lg-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <div class="card-actions float-end">
                    <a href="/admin/auth/sleeper">
                        <button class="btn btn-sm btn-light">View all</button>
                    </a>
                </div>
                <h5 class="card-title mb-0">휴면회원</h5>
            </div>
            <table class="table table-sm table-striped my-0">
                <thead>
                    <tr>
                        <th>이메일</th>
                        <th>휴면여부</th>
                        <th>해제요청</th>
                    </tr>
                </thead>
                <tbody class="text-end">
                    @foreach (DB::table('user_sleeper')->limit(5)->get() as $item)
                        <tr>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->sleeper }}</td>
                            <td>{{ $item->unlock }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 회원승인 --}}
    <div class="col-12 col-lg-6 col-xxl d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <div class="card-actions float-end">
                    <a href="/admin/auth/auth">
                        <button class="btn btn-sm btn-light">View all</button>
                    </a>
                </div>
                <h5 class="card-title mb-0">회원승인</h5>
            </div>
            <table class="table table-sm table-striped my-0">
                <thead>
                    <tr>

                        <th>이메일</th>
                        <th>승인여부</th>
                        <th>승인일자</th>
                    </tr>
                </thead>
                <tbody class="text-end">
                    @foreach (DB::table('users_auth')->limit(5)->get() as $item)
                        <tr>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->auth }}</td>
                            <td>{{ $item->auth_date }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>




</div>

<div class="row">
    <div class="col-12 col-lg-4  d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <div class="card-actions float-end">
                    <a href="/admin/auth/agree">
                        <button class="btn btn-sm btn-light">View all</button>
                    </a>
                </div>
                <h5 class="card-title mb-0">약관관리</h5>
            </div>
            <table class="table table-sm table-striped my-0">
                <thead>
                    <tr>
                        <th>필수</th>
                        <th>약관</th>
                        <th>회원수</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach (DB::table('user_agreement')->limit(5)->get() as $item)
                        <tr>
                            <td>{{ $item->required }}</td>
                            <td>{{ $item->title }}</td>
                            <td>{{ $item->users }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    {{-- 예약회원 --}}
    <div class="col-12 col-lg-4  d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <div class="card-actions float-end">
                    <a href="/admin/auth/reserved">
                        <button class="btn btn-sm btn-light">View all</button>
                    </a>
                </div>
                <h5 class="card-title mb-0">예약회원</h5>
            </div>
            <table class="table table-sm table-striped my-0">
                <thead>
                    <tr>
                        <th>이름</th>
                        <th>이메일</th>
                    </tr>
                </thead>
                <tbody class="text-end">
                    @foreach (DB::table('user_reserved')->limit(5)->get() as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- 블랙리스트 --}}
    <div class="col-12 col-lg-4  d-flex">
        <div class="card flex-fill">
            <div class="card-header">
                <div class="card-actions float-end">
                    <a href="/admin/auth/blacklist">
                        <button class="btn btn-sm btn-light">View all</button>
                    </a>
                </div>
                <h5 class="card-title mb-0">블랙리스트</h5>
                <h6 class="card-subtitle text-muted">
                    회원의 가입을 제한합니다.
                </h6>
            </div>
            <table class="table table-sm table-striped my-0">
                <thead>
                    <tr>
                        <th>이름</th>
                        <th>이메일</th>
                        <th>IP</th>
                    </tr>
                </thead>
                <tbody class="text-end">
                    @foreach (DB::table('user_blacklist')->limit(5)->get() as $item)
                        <tr>
                            <td>{{ $item->name }}</td>
                            <td>{{ $item->email }}</td>
                            <td>{{ $item->ip }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
