<div class="d-flex flex-column gap-1">
    <span class="navbar-header">Account Settings</span>
    <ul class="list-unstyled mb-0">

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.account.edit') }}">
                <i class="fe fe-settings nav-icon"></i>
                프로필 수정
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.account.avatar') }}">
                <i class="fe fe-image nav-icon"></i>
                아바타 관리
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.profile.phone') }}">
                <i class="fe fe-phone nav-icon"></i>
                전화번호 관리
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.profile.address') }}">
                <i class="fe fe-map-pin nav-icon"></i>
                주소 관리
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.profile.linked-accounts') }}">
                <i class="fe fe-user nav-icon"></i>
                소셜 프로파일
            </a>
        </li>


        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.profile.security') }}">
                <i class="fe fe-user nav-icon"></i>
                보안설정
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.profile.privacy') }}">
                <i class="fe fe-lock nav-icon"></i>
                프라이버시 설정
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('account.terms.index') }}">
                <i class="fe fe-file-text nav-icon"></i>
                약관동의
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.account.logs') }}">
                <i class="fe fe-clock nav-icon"></i>
                활동 로그
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('account.deletion.show') }}">
                <i class="fe fe-trash nav-icon"></i>
                회원 탈퇴
            </a>
        </li>

        <!-- Nav item -->
        <li class="nav-item">
            <a class="nav-link" href="/logout">
                <i class="fe fe-power nav-icon"></i>
                로그아웃
            </a>
        </li>

    </ul>
</div>
