@setTheme("admin.sidebar")
<x-theme name="admin.sidebar">
    <x-theme-layout>

        <div class="row">
            <x-col-left class="col-md-10">
                <div class="mb-3">
                    <h1 class="h3 d-inline align-middle">회원관리</h1>
                    <p>가입된 회원을 관리합니다.</p>
                </div>
            </x-col-left>
            <x-col-right class="col-md-2 d-flex justify-content-end align-items-end">
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb">
                      <li class="breadcrumb-item"><a href="#">Admin</a></li>
                      <li class="breadcrumb-item"><a href="#">Auth</a></li>
                      <li class="breadcrumb-item active" aria-current="page">Users</li>
                    </ol>
                </nav>
            </x-col-right>
        </div>






        <div class="row">
            <x-col-left class="col-md-3 col-lg-2">
                {{-- 왼쪽 메뉴 --}}
                @includeIf('jinyauth::admin.dashboard.sidemenu')

            </x-col-left>
            <x-col-right class="col-md-9 col-lg-10">

                <div class="row">
                    <div class="col-xl-6 col-xxl-5 d-flex">
                        <div class="w-100">
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col mt-0">
                                                    <h5><a href="/admin/auth/users">회원정보</a></h5>
                                                </div>

                                                <div class="col-auto">
                                                    <div class="stat text-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                                            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>

                                            <h1 class="mt-1 mb-3">가입: {{table_count_today("users")}} 명</h1>
                                            <div class="mb-0">
                                                <span class="text-muted">전체회원: {{table_count("users")}} 명</span>
                                                <span class="text-muted">관리자: {{table_count("users_admin")}} 명</span>
                                                <span class="text-muted">Super: {{table_count("users_super")}} 명</span>
                                            </div>
                                        </div>
                                    </div>


                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col mt-0">
                                                    <a href="/admin/auth/sleeper">휴면회원</a>
                                                </div>

                                                <div class="col-auto">
                                                    <div class="stat text-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-slash" viewBox="0 0 16 16">
                                                            <path d="M13.879 10.414a2.501 2.501 0 0 0-3.465 3.465zm.707.707-3.465 3.465a2.501 2.501 0 0 0 3.465-3.465m-4.56-1.096a3.5 3.5 0 1 1 4.949 4.95 3.5 3.5 0 0 1-4.95-4.95ZM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <h1 class="mt-1 mb-3">{{userSleeperCount()}}명</h1>
                                            <div class="mb-0">
                                                승인요청 : {{userSleeperUnlockCount()}}명
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col mt-0">
                                                    <a href="/admin/auth/oauth">소셜가입 로그</a>

                                                </div>

                                                <div class="col-auto">
                                                    <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                                                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                                                    </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <h1 class="mt-1 mb-3">연동회원: {{table_count("user_oauth")}} 명</h1>
                                            <div class="mb-0">

                                            </div>
                                        </div>
                                    </div>

                                    <div class="card">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col mt-0">
                                                    <a href="/admin/auth/confirm">미인증회원</a>
                                                </div>

                                                <div class="col-auto">
                                                    <div class="stat text-primary">
                                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-lock" viewBox="0 0 16 16">
                                                            <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m0 5.996V14H3s-1 0-1-1 1-4 6-4q.845.002 1.544.107a4.5 4.5 0 0 0-.803.918A11 11 0 0 0 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664zM9 13a1 1 0 0 1 1-1v-1a2 2 0 1 1 4 0v1a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1h-4a1 1 0 0 1-1-1zm3-3a1 1 0 0 0-1 1v1h2v-1a1 1 0 0 0-1-1"/>
                                                        </svg>
                                                    </div>
                                                </div>
                                            </div>
                                            <h1 class="mt-1 mb-3">미인증: {{$authInfo['today']}} 명</h1>
                                            <div class="mb-0">
                                                <span class="text-muted">인증완료: {{$authInfo['total']}} 명</span>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>


                    <div class="col-xl-6 col-xxl-7">
                        <div class="card flex-fill w-100">
                            <div class="card-header">
                                <div class="float-end">
                                    <form class="row g-2">
                                        <div class="col-auto">
                                            <select class="form-select form-select-sm bg-light border-0">
                                                <option>Jan</option>
                                                <option value="1">Feb</option>
                                                <option value="2">Mar</option>
                                                <option value="3">Apr</option>
                                            </select>
                                        </div>
                                        <div class="col-auto">
                                            <input type="text" class="form-control form-control-sm bg-light rounded-2 border-0" style="width: 100px;" placeholder="Search..">
                                        </div>
                                    </form>
                                </div>
                                <h5 class="card-title mb-0">최근 회원가입 추세</h5>
                            </div>
                            <div class="card-body pt-2 pb-3">
                                <div class="chart chart-sm"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
                                    <canvas id="dailyMembersChart" width="900" height="250" style="display: block; width: 900px; height: 250px;" class="chartjs-render-monitor"></canvas>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>


            <script>
                var dates = {!! json_encode($dailyMembers->pluck('date')) !!};
    var data = {!! json_encode($dailyMembers->pluck('total')) !!};

    var ctx = document.getElementById('dailyMembersChart').getContext('2d');
    var myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: dates,
            datasets: [{
                label: '일일 가입자 추세',
                data: data,
                backgroundColor: 'rgba(54, 162, 235, 0.2)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero: true
                    }
                }]
            }
        }
    });
            </script>



                <div class="row">
                    <div class="col-xl-6 col-xxl-5">
                        <div class="card h-100">
                            <div class="card-body">
                                @livewire('WireDash-UserCount')
                            </div>
                        </div>
                    </div>

                    <div class="col-xl-6 col-xxl-7">
                        <div class="row">
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">
                                                    <a href="/admin/auth/reserved">예약됨</a>
                                                </h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-exclamation" viewBox="0 0 16 16">
                                                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                                        <path d="M16 12.5a3.5 3.5 0 1 1-7 0 3.5 3.5 0 0 1 7 0m-3.5-2a.5.5 0 0 0-.5.5v1.5a.5.5 0 0 0 1 0V11a.5.5 0 0 0-.5-.5m0 4a.5.5 0 1 0 0-1 .5.5 0 0 0 0 1"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="mt-0 mb-1">등록자: {{table_count("user_reserved")}} 명</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- -->
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">
                                                    <a href="/admin/auth/country">국가</a>
                                                </h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-flag" viewBox="0 0 16 16">
                                                        <path d="M14.778.085A.5.5 0 0 1 15 .5V8a.5.5 0 0 1-.314.464L14.5 8l.186.464-.003.001-.006.003-.023.009a12 12 0 0 1-.397.15c-.264.095-.631.223-1.047.35-.816.252-1.879.523-2.71.523-.847 0-1.548-.28-2.158-.525l-.028-.01C7.68 8.71 7.14 8.5 6.5 8.5c-.7 0-1.638.23-2.437.477A20 20 0 0 0 3 9.342V15.5a.5.5 0 0 1-1 0V.5a.5.5 0 0 1 1 0v.282c.226-.079.496-.17.79-.26C4.606.272 5.67 0 6.5 0c.84 0 1.524.277 2.121.519l.043.018C9.286.788 9.828 1 10.5 1c.7 0 1.638-.23 2.437-.477a20 20 0 0 0 1.349-.476l.019-.007.004-.002h.001M14 1.221c-.22.078-.48.167-.766.255-.81.252-1.872.523-2.734.523-.886 0-1.592-.286-2.203-.534l-.008-.003C7.662 1.21 7.139 1 6.5 1c-.669 0-1.606.229-2.415.478A21 21 0 0 0 3 1.845v6.433c.22-.078.48-.167.766-.255C4.576 7.77 5.638 7.5 6.5 7.5c.847 0 1.548.28 2.158.525l.028.01C9.32 8.29 9.86 8.5 10.5 8.5c.668 0 1.606-.229 2.415-.478A21 21 0 0 0 14 7.655V1.222z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="mt-0 mb-1">가입국가: {{table_count("user_country")}}</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- -->
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">
                                                    <a href="/admin/auth/blacklist">블랙리스트</a>
                                                </h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-x" viewBox="0 0 16 16">
                                                        <path d="M11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.5 4.5 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10q.39 0 .74.025c.226-.341.496-.65.804-.918Q8.844 9.002 8 9c-5 0-6 3-6 4s1 1 1 1z"/>
                                                        <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7m-.646-4.854.646.647.646-.647a.5.5 0 0 1 .708.708l-.647.646.647.646a.5.5 0 0 1-.708.708l-.646-.647-.646.647a.5.5 0 0 1-.708-.708l.647-.646-.647-.646a.5.5 0 0 1 .708-.708"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="mt-0 mb-1">등록자: {{table_count("user_blacklist")}} 명</h4>
                                    </div>
                                </div>
                            </div>

                            <!-- -->
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">
                                                    <a href="/admin/auth/roles">역할</a>
                                                </h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-command" viewBox="0 0 16 16">
                                                        <path d="M3.5 2A1.5 1.5 0 0 1 5 3.5V5H3.5a1.5 1.5 0 1 1 0-3M6 5V3.5A2.5 2.5 0 1 0 3.5 6H5v4H3.5A2.5 2.5 0 1 0 6 12.5V11h4v1.5a2.5 2.5 0 1 0 2.5-2.5H11V6h1.5A2.5 2.5 0 1 0 10 3.5V5zm4 1v4H6V6zm1-1V3.5A1.5 1.5 0 1 1 12.5 5zm0 6h1.5a1.5 1.5 0 1 1-1.5 1.5zm-6 0v1.5A1.5 1.5 0 1 1 3.5 11z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>


                                        <h4 class="mt-0 mb-1">역할: {{table_count("roles")}} 명</h4>

                                        @foreach (table_top5("roles") as $item)
                                        <div class="badge bg-primary my-2">{{$item->name}}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- -->
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">
                                                    <a href="/admin/auth/grade">등급</a>
                                                </h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-lightning" viewBox="0 0 16 16">
                                                        <path d="M5.52.359A.5.5 0 0 1 6 0h4a.5.5 0 0 1 .474.658L8.694 6H12.5a.5.5 0 0 1 .395.807l-7 9a.5.5 0 0 1-.873-.454L6.823 9.5H3.5a.5.5 0 0 1-.48-.641zM6.374 1 4.168 8.5H7.5a.5.5 0 0 1 .478.647L6.78 13.04 11.478 7H8a.5.5 0 0 1-.474-.658L9.306 1z"/>
                                                    </svg>
                                                </div>
                                            </div>
                                        </div>


                                        <h4 class="mt-0 mb-1">등급: {{table_count("user_grade")}}</h4>

                                        @foreach (table_top5("user_grade") as $item)
                                        <div class="badge bg-primary my-2">{{$item->name}}</div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <!-- -->
                            <div class="col-12 col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col mt-0">
                                                <h5 class="card-title">
                                                    <a href="/admin/auth/agreement">동의서</a>
                                                </h5>
                                            </div>
                                            <div class="col-auto">
                                                <div class="stat text-primary">
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-bandaid" viewBox="0 0 16 16">
                                                        <path d="M14.121 1.879a3 3 0 0 0-4.242 0L8.733 3.026l4.261 4.26 1.127-1.165a3 3 0 0 0 0-4.242M12.293 8 8.027 3.734 3.738 8.031 8 12.293zm-5.006 4.994L3.03 8.737 1.879 9.88a3 3 0 0 0 4.241 4.24l.006-.006 1.16-1.121ZM2.679 7.676l6.492-6.504a4 4 0 0 1 5.66 5.653l-1.477 1.529-5.006 5.006-1.523 1.472a4 4 0 0 1-5.653-5.66l.001-.002 1.505-1.492z"/>
                                                        <path d="M5.56 7.646a.5.5 0 1 1-.706.708.5.5 0 0 1 .707-.708Zm1.415-1.414a.5.5 0 1 1-.707.707.5.5 0 0 1 .707-.707M8.39 4.818a.5.5 0 1 1-.708.707.5.5 0 0 1 .707-.707Zm0 5.657a.5.5 0 1 1-.708.707.5.5 0 0 1 .707-.707ZM9.803 9.06a.5.5 0 1 1-.707.708.5.5 0 0 1 .707-.707Zm1.414-1.414a.5.5 0 1 1-.706.708.5.5 0 0 1 .707-.708ZM6.975 9.06a.5.5 0 1 1-.707.708.5.5 0 0 1 .707-.707ZM8.39 7.646a.5.5 0 1 1-.708.708.5.5 0 0 1 .707-.708Zm1.413-1.414a.5.5 0 1 1-.707.707.5.5 0 0 1 .707-.707"/>
                                                      </svg>
                                                </div>
                                            </div>
                                        </div>

                                        <h4 class="mt-0 mb-1">동의서: {{table_count("user_agreement")}} </h4>
                                        <span class="text-muted">로그: {{table_count("user_agreement_logs")}} </span>
                                    </div>
                                </div>
                            </div>




                        </div>
                    </div>























                </div>












            </x-col-right>
        </div>

    </x-theme-layout>
</x-theme>
