@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '2단계 인증 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h2 mb-1">2단계 인증 관리</h1>
                        <p class="text-muted mb-0">계정 보안을 강화하기 위해 2단계 인증을 설정하세요.</p>
                    </div>
                    <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="fe fe-arrow-left me-2"></i>
                        대시보드로 돌아가기
                    </a>
                </div>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row">
            <div class="col-xl-4 col-lg-5">
                <!-- 현재 상태 카드 -->
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h4 class="mb-0">현재 상태</h4>
                        @if($status['enabled'])
                            <span class="badge bg-success">활성화</span>
                        @else
                            <span class="badge bg-secondary">비활성</span>
                        @endif
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3 d-flex justify-content-between">
                                <span class="text-muted">방법</span>
                                <span class="fw-semibold text-uppercase">{{ $status['method'] }}</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between">
                                <span class="text-muted">활성화 일시</span>
                                <span>{{ optional($status['confirmed_at'])->format('Y-m-d H:i') ?? '-' }}</span>
                            </li>
                            <li class="mb-3 d-flex justify-content-between">
                                <span class="text-muted">마지막 사용</span>
                                <span>{{ optional($status['last_used_at'])->diffForHumans() ?? '-' }}</span>
                            </li>
                            <li class="d-flex justify-content-between">
                                <span class="text-muted">백업 코드</span>
                                <span>{{ $status['backup_codes_remaining'] }} / {{ $status['backup_codes_total'] }}</span>
                            </li>
                        </ul>
                    </div>
                    <div class="card-footer d-flex gap-2">
                        <form class="flex-fill" method="POST" action="{{ route('home.account.2fa.disable') }}"
                              onsubmit="return confirm('2FA를 비활성화하시겠습니까? 계정 보안이 약화될 수 있습니다.');">
                            @csrf
                            <button class="btn btn-outline-danger w-100" {{ $status['enabled'] ? '' : 'disabled' }}>
                                <i class="fe fe-x-circle me-2"></i>비활성화
                            </button>
                        </form>
                        <form class="flex-fill" method="POST" action="{{ route('home.account.2fa.setup') }}">
                            @csrf
                            <button class="btn btn-primary w-100">
                                <i class="fe fe-refresh-ccw me-2"></i>새 시크릿 생성
                            </button>
                        </form>
                    </div>
                </div>

                <!-- 백업 코드 카드 -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">백업 코드</h4>
                    </div>
                    <div class="card-body">
                        @if(session('generated_backup_codes'))
                            <div class="alert alert-warning" role="alert">
                                <i class="fe fe-alert-triangle me-2"></i>
                                <strong>중요:</strong> 아래 백업 코드를 안전한 곳에 보관하세요. 각 코드는 한 번만 사용할 수 있습니다.
                            </div>
                            <div class="bg-dark rounded p-3 text-white mb-3">
                                <div class="d-flex flex-column gap-2">
                                    @foreach(session('generated_backup_codes') as $code)
                                        <span class="fw-semibold font-monospace">{{ $code }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <button type="button" class="btn btn-outline-light w-100" onclick="copyBackupCodes()">
                                <i class="fe fe-copy me-2"></i>전체 복사
                            </button>
                        @else
                            <p class="text-muted small mb-3">
                                2FA 앱에 접근할 수 없을 때 사용할 수 있는 백업 코드입니다.
                            </p>
                        @endif
                        <form method="POST" action="{{ route('home.account.2fa.backup.regenerate') }}"
                              onsubmit="return confirm('기존 백업 코드는 모두 무효화됩니다. 새로 생성하시겠습니까?');">
                            @csrf
                            <button class="btn btn-outline-primary w-100" {{ $status['enabled'] ? '' : 'disabled' }}>
                                <i class="fe fe-repeat me-2"></i>백업 코드 재생성
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-xl-8 col-lg-7">
                <!-- 2FA 설정 진행 카드 -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h4 class="mb-0">2FA 설정 진행</h4>
                    </div>
                    <div class="card-body">
                        @if($pendingSetup)
                            {{-- QR 및 코드 확인 영역 --}}
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="ratio ratio-1x1 border rounded p-3 bg-light d-flex align-items-center justify-content-center">
                                        <img src="{{ $pendingSetup['qr_url'] }}" alt="2FA QR Code" class="img-fluid">
                                    </div>
                                    <p class="mt-2 text-muted small">
                                        <i class="fe fe-info me-1"></i>
                                        QR 코드를 인증 앱(예: Google Authenticator, Microsoft Authenticator)에 스캔하세요.
                                    </p>
                                </div>
                                <div class="col-md-7">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">시크릿 키</label>
                                        <div class="input-group">
                                            <input type="text" class="form-control font-monospace" 
                                                   value="{{ $pendingSetup['secret'] }}" 
                                                   readonly 
                                                   id="secret-key">
                                            <button type="button" class="btn btn-outline-secondary" 
                                                    onclick="copySecretKey()">
                                                <i class="fe fe-copy"></i> 복사
                                            </button>
                                        </div>
                                        <small class="text-muted">QR 코드를 스캔할 수 없는 경우 수동으로 입력하세요.</small>
                                    </div>
                                    <div>
                                        <label class="form-label fw-semibold">백업 코드</label>
                                        <div class="bg-light rounded p-3">
                                            <div class="d-flex flex-wrap gap-2">
                                                @foreach($pendingSetup['backup_codes'] as $code)
                                                    <span class="badge bg-dark font-monospace">{{ $code }}</span>
                                                @endforeach
                                            </div>
                                        </div>
                                        <small class="text-muted">이 코드들을 안전한 곳에 보관하세요.</small>
                                    </div>
                                </div>
                            </div>
                            <hr>
                            <form method="POST" action="{{ route('home.account.2fa.enable') }}"
                                  class="row g-3 align-items-end">
                                @csrf
                                <div class="col-md-6">
                                    <label class="form-label">앱에서 생성된 6자리 코드</label>
                                    <input type="text" 
                                           name="code" 
                                           class="form-control @error('code') is-invalid @enderror" 
                                           placeholder="123456"
                                           maxlength="6"
                                           pattern="[0-9]{6}"
                                           required>
                                    @error('code')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                    <small class="text-muted">인증 앱에 표시된 6자리 숫자를 입력하세요.</small>
                                </div>
                                <div class="col-md-6">
                                    <button class="btn btn-success w-100">
                                        <i class="fe fe-check-circle me-2"></i>2FA 활성화
                                    </button>
                                </div>
                            </form>
                        @else
                            <div class="text-center py-5">
                                <i class="fe fe-shield-off fs-1 text-muted mb-3 d-block"></i>
                                <p class="text-muted mb-4">
                                    먼저 상단의 "새 시크릿 생성" 버튼을 눌러 QR 코드를 발급하세요.
                                </p>
                                <form method="POST" action="{{ route('home.account.2fa.setup') }}">
                                    @csrf
                                    <button class="btn btn-primary">
                                        <i class="fe fe-plus-circle me-2"></i>2FA 설정 시작
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>

                <!-- 최근 2FA 로그 카드 -->
                <div class="card">
                    <div class="card-header">
                        <h4 class="mb-0">최근 2FA 로그</h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>일시</th>
                                        <th>액션</th>
                                        <th>상태</th>
                                        <th>설명</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td>{{ \Carbon\Carbon::parse($log->created_at)->format('Y-m-d H:i') }}</td>
                                            <td class="text-uppercase">{{ $log->action }}</td>
                                            <td>
                                                <span class="badge {{ $log->status === 'success' ? 'bg-success' : ($log->status === 'failed' ? 'bg-danger' : 'bg-secondary') }}">
                                                    {{ $log->status }}
                                                </span>
                                            </td>
                                            <td>{{ $log->description }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">기록된 로그가 없습니다.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        // 시크릿 키 복사
        function copySecretKey() {
            const secretKey = document.getElementById('secret-key');
            secretKey.select();
            document.execCommand('copy');
            
            // 복사 성공 피드백
            const btn = event.target.closest('button');
            const originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fe fe-check"></i> 복사됨';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }

        // 백업 코드 전체 복사
        function copyBackupCodes() {
            const codes = @json(session('generated_backup_codes', []));
            const text = codes.join('\n');
            
            navigator.clipboard.writeText(text).then(function() {
                alert('백업 코드가 클립보드에 복사되었습니다.');
            }, function(err) {
                alert('복사에 실패했습니다: ' + err);
            });
        }
    </script>
@endpush


