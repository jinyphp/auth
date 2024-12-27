<div class="dropdown">
    <button type="button"
        class="btn btn-icon btn-lg btn-outline-secondary
            fs-lg border-0"
        data-bs-toggle="dropdown"
        aria-expanded="false">

        {{-- <i class="ci-user animate-target"></i> --}}
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
            fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
            <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
        </svg>

        <span class="visually-hidden">Account</span>
    </button>

    <ul class="dropdown-menu" style="--cz-dropdown-min-width: 9rem">
        @if(Auth::check())
        {{-- 홈 화면--}}
        <li>
            <a href="/home" class="dropdown-item">
                <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-sun"></i>
                </span>
                <span class="theme-label">계정정보</span>
                <i class="item-active-indicator ci-check ms-auto"></i>
            </a>
        </li>

        {{-- 관리자 --}}
        @if(isAdmin())
        <li>
            <a href="/admin" class="dropdown-item">
                <span class="theme-icon d-flex fs-base me-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                        <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                        <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                    </svg>
                </span>
                <span class="theme-label">Admin</span>
                {{-- <i class="item-active-indicator ci-check ms-auto"></i> --}}
            </a>
        </li>
        @endif

        {{-- 로그아웃--}}
        <li>
            <a href="/logout" class="dropdown-item">
                <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-log-out fs-base opacity-75"></i>
                </span>
                <span class="theme-label">Logout</span>
                {{-- <i class="item-active-indicator ci-check ms-auto"></i> --}}
            </a>
        </li>


        @else
        {{-- 로그인 --}}
        <li>
            <a href="/login" class="dropdown-item">
                <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-sun"></i>
                </span>
                <span class="theme-label">로그인</span>
                <i class="item-active-indicator ci-check ms-auto"></i>
            </a>
        </li>
        {{-- 회원가입 --}}
        <li>
            <a href="/regist" class="dropdown-item">
                <span class="theme-icon d-flex fs-base me-2">
                    <i class="ci-sun"></i>
                </span>
                <span class="theme-label">회원가입</span>
                <i class="item-active-indicator ci-check ms-auto"></i>
            </a>
        </li>
        @endif
    </ul>
</div>
