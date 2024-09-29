<div class="row">
    <div class="col-xl-6 col-xxl-5 d-flex">
        <div class="w-100">
            <div class="row">
                <div class="col-sm-6">
                    @includeIf("jinyauth::admin.dashboard.user_count")
                    @includeIf("jinyauth::admin.dashboard.country")
                </div>
                <div class="col-sm-6">
                    @includeIf("jinyauth::admin.dashboard.teams")
                    @includeIf("jinyauth::admin.dashboard.status")
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-6 col-xxl-7">
        <div class="card" >
            <div class="card-body">
                {{-- @includeIf("jinyauth::admin.dashboard.chart") --}}
            </div>
        </div>
    </div>

</div>

<x-ui-divider>회원 설정</x-ui-divider>

<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/auth/agree">
                            동의서
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            가입된 회원을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">

                <x-badge-info>
                    <a href="/admin/auth/agree/log">
                    동의로그
                    </a>
                </x-badge-info>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            회원 유형

                        </h5>
                        <h6 class="card-subtitle text-muted">

                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">
                <x-badge-info>
                    <a href="/admin/auth/grade">
                        등급
                    </a>
                </x-badge-info>

                <x-badge-info>
                    <a href="/admin/auth/roles">
                        권환
                    </a>
                </x-badge-info>




            </div>
        </div>
    </div>


    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            회원제한
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            회원의 가입을 제한합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">
                <x-badge-danger>
                    <a href="/admin/auth/reserved">
                        예약어
                    </a>
                </x-badge-danger>
                <x-badge-danger>
                    <a href="/admin/auth/blacklist">
                        블렉리스트
                    </a>
                </x-badge-danger>

            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            회원지역
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            회원별 지역을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">
                <x-badge-info>
                    <a href="/admin/auth/locale">
                        지역
                    </a>
                </x-badge-info>

            </div>
        </div>
    </div>






</div>

@includeIf("jiny-profile::admin.dash")

<x-ui-divider>소셜 로그인</x-ui-divider>

<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-header border-bottom">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">소설연동</h5>
                        <h6 class="card-subtitle text-muted">
                            소셜로그인 및 연동을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="list-group list-group-flush" role="tablist">
                <a class="list-group-item list-group-item-action"
                    href="/admin/auth/social">
                    연동목록
                </a>
            </div>
        </div>
    </div>
</div>
