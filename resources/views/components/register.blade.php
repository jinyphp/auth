{{-- Register Component --}}
@auth
    {{-- 로그인된 상태 - 사용자 정보 드롭다운 --}}
    <div class="dropdown">
        <a href="#" {{ $attributes->merge(['class' => 'btn dropdown-toggle']) }} data-bs-toggle="dropdown" aria-expanded="false">
            {{ Auth::user()->name ?? '사용자' }}
        </a>
        <ul class="dropdown-menu">
            <li><a class="dropdown-item" href="/home">대시보드</a></li>
            <li><a class="dropdown-item" href="/profile">회원정보</a></li>
            <li><a class="dropdown-item" href="/account/settings">계정설정</a></li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item">로그아웃</button>
                </form>
            </li>
        </ul>
    </div>
@else
    {{-- 로그인되지 않은 상태 - 회원가입 버튼 --}}
    <a href="/register" {{ $attributes->merge(['class' => 'btn']) }}>{{ $slot }}</a>
@endauth