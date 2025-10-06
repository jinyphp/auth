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
                    대시보드
                </a>
            </li>

            <li class="nav-item">
                <div class="nav-divider"></div>
            </li>

            {{-- 사용자 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navUsers" aria-expanded="false" aria-controls="navUsers">
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
                    </ul>
                </div>
            </li>

            {{-- 계정 보안 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navSecurity" aria-expanded="false" aria-controls="navSecurity">
                    <i class="nav-icon fe fe-shield me-2"></i>
                    계정 보안
                </a>
                <div id="navSecurity" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        {{-- <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.lockouts.index') }}">
                                계정 잠금
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.deletions.index') }}">
                                탈퇴 신청
                            </a>
                        </li> --}}
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
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navUserSettings" aria-expanded="false" aria-controls="navUserSettings">
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

            {{-- 사용자 정보 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navUserData" aria-expanded="false" aria-controls="navUserData">
                    <i class="nav-icon fe fe-database me-2"></i>
                    사용자 정보
                </a>
                <div id="navUserData" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
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
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.social.index') }}">
                                소셜 계정
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 커뮤니케이션 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navCommunication" aria-expanded="false" aria-controls="navCommunication">
                    <i class="nav-icon fe fe-mail me-2"></i>
                    커뮤니케이션
                </a>
                <div id="navCommunication" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
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
                    </ul>
                </div>
            </li>

            {{-- 금융 관리 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navFinance" aria-expanded="false" aria-controls="navFinance">
                    <i class="nav-icon fe fe-dollar-sign me-2"></i>
                    금융 관리
                </a>
                <div id="navFinance" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.index') }}">
                                전자지갑
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.deposits') }}">
                                입금 내역
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.emoney.withdrawals') }}">
                                출금 내역
                            </a>
                        </li>
                    </ul>
                </div>
            </li>

            {{-- 시스템 --}}
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse"
                    data-bs-target="#navSystem" aria-expanded="false" aria-controls="navSystem">
                    <i class="nav-icon fe fe-file-text me-2"></i>
                    시스템
                </a>
                <div id="navSystem" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.terms.index') }}">
                                이용약관
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.logs.index') }}">
                                사용자 로그
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.oauth.providers.index') }}">
                                OAuth 프로바이더
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.shards.index') }}">
                                샤딩관리
                            </a>
                        </li>
                    </ul>
                </div>
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
