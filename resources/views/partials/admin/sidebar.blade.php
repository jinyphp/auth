<!-- Sidebar -->
<style>
    .navbar-heading {
        color: #8492a6 !important;
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1rem 0.25rem 1rem;
        opacity: 0.8;
    }

    .navbar-vertical.navbar .navbar-heading {
        color: #8492a6 !important;
    }

    /* 다크 테마에서 더 밝게 */
    .navbar-vertical.navbar-dark .navbar-heading,
    .navbar-vertical .navbar-heading {
        color: #a6b0cf !important;
    }
</style>

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
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.languages.index') }}">
                                언어
                            </a>
                        </li>
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
                            <a class="nav-link {{ request()->routeIs('admin.user-unregist.*') ? 'active' : '' }}"
                                href="{{ route('admin.user-unregist.index') }}">
                                탈퇴 신청
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.blacklist.index') }}">
                                블랙리스트
                            </a>
                        </li>
                        {{-- JWT 관련 메뉴는 jiny/jwt 패키지에서 관리됩니다 --}}
                        @if (class_exists(\Jiny\Jwt\Http\Controllers\Admin\Jwt\IndexController::class))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.auth.jwt.*') ? 'active' : '' }}"
                                href="{{ route('admin.auth.jwt.index') }}">
                                JWT 설정
                            </a>
                        </li>
                        @endif
                        @if (class_exists(\Jiny\Jwt\Http\Controllers\Admin\UserToken\IndexController::class))
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('admin.auth.token.*') ? 'active' : '' }}"
                                href="{{ route('admin.auth.token.index') }}">
                                JWT 토큰 목록
                            </a>
                        </li>
                        @endif
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

            {{-- 소셜 정보 - 임시 비활성화 (jiny/social 패키지 라우트 로딩 문제) --}}
            {{--
            <li class="nav-item">
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#navUserSocial"
                    aria-expanded="false" aria-controls="navUserSocial">
                    <i class="nav-icon fe fe-share-2 me-2"></i>
                    소셜 연동
                </a>
                <div id="navUserSocial" class="collapse" data-bs-parent="#sideNavbar">
                    <ul class="nav flex-column">

                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('admin.auth.user.social.index') }}">
                                소셜 계정 관리
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
            --}}

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
            @includeIf('jiny-mail::partials.admin.menu')

            {{-- jiny/emoney 패키지 참조 --}}
            @includeIf('jiny-emoney::partials.admin.menu')

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
