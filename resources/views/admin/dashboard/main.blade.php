<!-- Quick Links -->
<div class="row">
    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/auth">
                            가입/인증
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            회원 인증을 처리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">
                <x-badge-secondary>동의서</x-badge-secondary>
                <x-badge-secondary>동의서로그</x-badge-secondary>
                <x-badge-info>설정</x-badge-info>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/auth">
                            회원관리
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
                <x-badge-primary>회원목록</x-badge-primary>
                <x-badge-primary>등급</x-badge-primary>
                <x-badge-danger>예약어</x-badge-danger>
                <x-badge-danger>블렉리스트</x-badge-danger>

                <x-badge-info>국가</x-badge-info>

                <x-badge-info>휴면회원</x-badge-info>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header border-bottom">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">프로필관리</h5>
                        <h6 class="card-subtitle text-muted">
                            회원 프로필을 관리합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="list-group list-group-flush" role="tablist">
                <a class="list-group-item list-group-item-action"
                    href="#">
                    avata 이미지
                </a>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header border-bottom">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">권환관리</h5>
                        <h6 class="card-subtitle text-muted">
                            회원별 권환을 부여할 수 있습니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
            <div class="card-body">
                <x-badge-primary>권환등급</x-badge-primary>
            </div>
        </div>
    </div>

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
                    href="#">
                    연동목록
                </a>
            </div>
        </div>
    </div>

    <div class="col-3">
        <div class="card">
            <div class="card-header">
                <x-flex-between>
                    <div>
                        <h5 class="card-title">
                            <a href="/admin/module/teams">
                                팀관리
                            </a>
                        </h5>
                        <h6 class="card-subtitle text-muted">
                            등록된 회원을 그룹화 하여 팀을 운영합니다.
                        </h6>
                    </div>
                    <div>
                        @icon("info-circle.svg")
                    </div>
                </x-flex-between>
            </div>
        </div>
    </div>

</div>

