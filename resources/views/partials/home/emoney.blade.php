<div class="d-flex flex-column gap-1">
    <span class="navbar-header">Emoney & Points</span>
    <ul class="list-unstyled mb-0">
        <!-- 이머니 대시보드 -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.emoney.index') }}">
                <i class="fe fe-dollar-sign nav-icon"></i>
                이머니 관리
            </a>
        </li>

        <!-- 이머니 충전 -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.emoney.deposit') }}">
                <i class="fe fe-plus-circle nav-icon"></i>
                이머니 충전
            </a>
        </li>

        <!-- 이머니 출금 -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.emoney.withdraw') }}">
                <i class="fe fe-minus-circle nav-icon"></i>
                이머니 출금
            </a>
        </li>

        <!-- 거래 내역 -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.emoney.log') }}">
                <i class="fe fe-clock nav-icon"></i>
                거래 내역
            </a>
        </li>

        <!-- 포인트 관리 -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.emoney.point.index') }}">
                <i class="fe fe-star nav-icon"></i>
                포인트 관리
            </a>
        </li>

        <!-- 은행 계좌 관리 -->
        <li class="nav-item">
            <a class="nav-link" href="{{ route('home.emoney.bank.index') }}">
                <i class="fe fe-credit-card nav-icon"></i>
                계좌 관리
            </a>
        </li>
    </ul>
</div>
