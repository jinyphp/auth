<!-- Sidebar -->
<nav class="navbar-vertical navbar">
    <div class="vh-100" data-simplebar>
        <!-- Brand logo -->
        <a class="navbar-brand" href="/">
            <img src="{{ asset('assets/images/brand/logo/logo-inverse.svg') }}" alt="Jiny" />
        </a>

        <!-- Navbar nav -->
        <ul class="navbar-nav flex-column" id="sideNavbar">
            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link" href="/admin/auth">
                    <i class="nav-icon fe fe-home me-2"></i>
                    Auth 보드
                </a>
            </li>

            <li class="nav-item">
                <div class="nav-divider"></div>
            </li>

            {{-- 사용자 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navUsers"
                    aria-expanded="false" aria-controls="navUsers">
                    <i class="nav-icon fe fe-users me-2"></i>
                    사용자 관리
                </a>
                <div id="navUsers" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.users.index') }}">
                                사용자 목록
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.users.create') }}">
                                사용자 추가
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.addresses.index') }}">
                                주소
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.phones.index') }}">
                                전화번호
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.countries.index') }}">
                                국가
                            </a>
                        </li>
                        {{-- <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.languages.index') }}">
                                언어
                            </a>
                        </li> --}}
                    </ul>
                </div>
            </li>

            {{-- 계정 보안 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navSecurity"
                    aria-expanded="false" aria-controls="navSecurity">
                    <i class="nav-icon fe fe-shield me-2"></i>
                    계정 보안
                </a>
                <div id="navSecurity" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        {{-- <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.lockouts.index') }}">
                                계정 잠금
                            </a>
                        </li> --}}
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.user-unregist.index') }}">
                                탈퇴 신청
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.blacklist.index') }}">
                                블랙리스트
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 사용자 설정 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navUserSettings"
                    aria-expanded="false" aria-controls="navUserSettings">
                    <i class="nav-icon fe fe-settings me-2"></i>
                    사용자 설정
                </a>
                <div id="navUserSettings" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.types.index') }}">
                                사용자 타입
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.grades.index') }}">
                                사용자 등급
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.reserved.index') }}">
                                예약 키워드
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 소셜 정보 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navUserData"
                    aria-expanded="false" aria-controls="navUserData">
                    <i class="nav-icon fe fe-database me-2"></i>
                    소셜 연동
                </a>
                <div id="navUserData" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.social.index') }}">
                                소셜 계정
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.oauth.providers.index') }}">
                                OAuth 프로바이더
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <div class="navbar-heading">커뮤니케이션</div>
            </li>

            {{-- 커뮤니케이션 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.user.messages.index') }}">
                    메시지
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.user.reviews.index') }}">
                    리뷰
                </a>
            </li>

            {{-- 메일 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navMail"
                    aria-expanded="false" aria-controls="navMail">
                    <i class="nav-icon fe fe-mail me-2"></i>
                    메일
                </a>
                <div id="navMail" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.mail.setting.index') }}">
                                <i class="fe fe-settings me-2"></i>메일 설정
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.mail.templates.index') }}">
                                <i class="fe fe-file-text me-2"></i>메일 템플릿
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.cms.mail.create') }}">
                                <i class="fe fe-send me-2"></i>전체 메일 발송
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.mail.logs.index') }}">
                                <i class="fe fe-list me-2"></i>메일 로그
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <div class="navbar-heading">금융</div>
            </li>

            {{-- 금융 관리 --}}
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.bank.index') }}">
                    <i class="fe fe-credit-card me-2"></i>은행목록
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navFinance"
                    aria-expanded="false" aria-controls="navFinance">
                    <i class="nav-icon fe fe-dollar-sign me-2"></i>
                    이머니
                </a>
                <div id="navFinance" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.index') }}">
                                <i class="fe fe-credit-card me-2"></i>이머니 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.bank.index') }}">
                                <i class="fe fe-home me-2"></i>은행 계좌
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.deposit') }}">
                                <i class="fe fe-arrow-down-circle me-2"></i>입금 내역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.withdraw') }}">
                                <i class="fe fe-arrow-up-circle me-2"></i>출금 내역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.log') }}">
                                <i class="fe fe-list me-2"></i>거래 로그
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 포인트 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navPoint"
                    aria-expanded="false" aria-controls="navPoint">
                    <i class="nav-icon fe fe-star me-2"></i>
                    포인트
                </a>
                <div id="navPoint" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.index') }}">
                                <i class="fe fe-star me-2"></i>포인트 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.log') }}">
                                <i class="fe fe-list me-2"></i>거래 로그
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.expiry') }}">
                                <i class="fe fe-clock me-2"></i>만료 관리
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.point.stats') }}">
                                <i class="fe fe-bar-chart-2 me-2"></i>통계
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <div class="navbar-heading">로그</div>
            </li>

            {{-- 시스템 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navSystem"
                    aria-expanded="false" aria-controls="navSystem">
                    <i class="nav-icon fe fe-file-text me-2"></i>
                    시스템
                </a>
                <div id="navSystem" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.logs.index') }}">
                                사용자 로그
                            </a>
                        </li>


                    </ul>
                </div>
            </li>

            <li class="nav-item">
                <div class="navbar-heading">설정</div>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.terms.index') }}">
                    이용약관
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.setting.index') }}">
                    인증설정
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.shards.index') }}">
                    샤딩관리
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('admin.auth.setting.index') }}">
                    <i class="fe fe-settings me-2"></i>Auth 설정
                </a>
            </li>



        </ul>

        <!-- Help Card -->
        <div class="card bg-dark-primary shadow-none text-center mx-4 mt-5">
            <div class="card-body py-4">
                <h5 class="text-white-50">도움이 필요하신가요?</h5>
                <p class="text-white-50 fs-6 mb-3">문서를 확인하세요</p>
                <a href="#" class="btn btn-white btn-sm">문서 보기</a>
            </div>
        </div>
    </div>
</nav>
