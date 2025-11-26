<div class="card">
    <div class="card-header">
        <h5 class="mb-0">빠른 링크</h5>
    </div>
    <div class="card-body">
        <div class="d-grid gap-2">
            <a href="{{ route('home.account.avatar') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-person-circle me-2"></i>아바타 변경
            </a>
            <a href="{{ route('home.profile.phone') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-telephone me-2"></i>전화번호 관리
            </a>
            <a href="{{ route('home.profile.address') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-geo-alt me-2"></i>주소 관리
            </a>
            <a href="{{ route('home.account.logs') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-clock-history me-2"></i>활동 로그
            </a>
            <a href="{{ route('account.terms.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-file-text me-2"></i>약관 동의 관리
            </a>
            <a href="{{ route('account.deletion.show') }}" class="btn btn-outline-danger btn-sm">
                <i class="bi bi-trash me-2"></i>회원 탈퇴
            </a>
        </div>
    </div>
</div>
