<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>탈퇴 승인된 계정</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.css">
</head>
<body class="bg-light">
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow-sm">
                    <div class="card-body p-5 text-center">
                        <!-- Icon -->
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="80" height="80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-danger">
                                <circle cx="12" cy="12" r="10"></circle>
                                <line x1="15" y1="9" x2="9" y2="15"></line>
                                <line x1="9" y1="9" x2="15" y2="15"></line>
                            </svg>
                        </div>

                        <!-- Title -->
                        <h2 class="mb-3">계정 탈퇴 완료</h2>

                        <!-- Message -->
                        @if(session('error'))
                            <div class="alert alert-danger">
                                {{ session('error') }}
                            </div>
                        @endif

                        <div class="text-muted mb-4">
                            <p>귀하의 계정은 탈퇴 승인이 완료되었습니다.</p>
                            <p class="mb-0">더 이상 이 계정으로 로그인하실 수 없습니다.</p>
                        </div>

                        <hr class="my-4">

                        <div class="text-start mb-4">
                            <h6 class="mb-3">안내 사항</h6>
                            <ul class="text-muted small">
                                <li>모든 개인 데이터가 삭제 예정입니다</li>
                                <li>구독 중인 모든 서비스가 취소되었습니다</li>
                                <li>저장된 데이터는 복구할 수 없습니다</li>
                            </ul>
                        </div>

                        <!-- Actions -->
                        <div class="d-grid gap-2">
                            <a href="{{ route('login') }}" class="btn btn-primary">
                                로그인 페이지로 돌아가기
                            </a>
                            <a href="/" class="btn btn-outline-secondary">
                                홈으로 이동
                            </a>
                        </div>

                        <div class="mt-4 text-muted small">
                            그동안 서비스를 이용해 주셔서 감사합니다.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
