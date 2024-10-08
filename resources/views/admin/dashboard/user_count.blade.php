<div class="card">
    <div class="card-body">
        <div class="row">
            <div class="col mt-0">
                <h5 class="card-title">
                    <a href="/admin/auth/users">
                        회원정보
                    </a>
                </h5>
                <h6 class="card-subtitle text-muted">
                    회원 인증을 처리합니다.
                </h6>
            </div>

            <div class="col-auto">
                <div class="stat text-primary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6m2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0m4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4m-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10s-3.516.68-4.168 1.332c-.678.678-.83 1.418-.832 1.664z"/>
                    </svg>
                </div>
            </div>
        </div>
        <h1 class="mt-1 mb-3">
            {{user_count()}} 명
        </h1>
        <div class="mb-0">
            <x-badge-secondary>동의서</x-badge-secondary>
                <x-badge-secondary>동의서로그</x-badge-secondary>
                <x-badge-info>설정</x-badge-info>
        </div>
    </div>
</div>
