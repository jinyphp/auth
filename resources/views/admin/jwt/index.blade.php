@extends('jiny-auth::layouts.admin.sidebar')

@section('title', 'JWT 설정')

@push('styles')
    <style>
        .status-indicator {
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 6px;
        }

        .status-enabled {
            background-color: #28a745;
        }

        .status-disabled {
            background-color: #dc3545;
        }

        .jwt-card {
            border: 1px solid #e3e6f0;
            border-radius: 0.35rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
        }

        .config-section {
            background: #f8f9fc;
            border-radius: 0.35rem;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #5a5c69;
            margin-bottom: 0.5rem;
        }

        .badge-duration {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid">

        <!-- Page Header -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <div>
                <h1 class="h3 mb-1 text-gray-800">JWT 로그인 설정</h1>
                <p class="mb-0 text-muted">JWT 인증 시스템의 토큰 유효시간, 보안 설정 및 동작 방식을 관리합니다.</p>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-warning" onclick="resetConfig()">
                    <i class="fas fa-undo fa-sm text-white-50"></i> 초기화
                </button>
                <button type="button" class="btn btn-success" onclick="saveConfig()">
                    <i class="fas fa-save fa-sm text-white-50"></i> 저장
                </button>
            </div>
        </div>

        <!-- Success Message Alert -->
        <div id="success-alert" class="alert alert-success alert-dismissible fade" role="alert" style="display: none;">
            <i class="fas fa-check-circle"></i>
            <span id="success-message"></span>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>

        <!-- Status Cards -->
        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">JWT 상태</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span
                                        class="status-indicator {{ $jwtStatus['jwt_enabled'] ? 'status-enabled' : 'status-disabled' }}"></span>
                                    {{ $jwtStatus['jwt_enabled'] ? '활성화' : '비활성화' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-key fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">인증 방식</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    {{ strtoupper($jwtStatus['auth_method']) }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shield-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Remember 기능</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span
                                        class="status-indicator {{ $jwtStatus['remember_enabled'] ? 'status-enabled' : 'status-disabled' }}"></span>
                                    {{ $jwtStatus['remember_enabled'] ? '활성화' : '비활성화' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">자동 새로고침</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">
                                    <span
                                        class="status-indicator {{ $jwtStatus['auto_refresh_enabled'] ? 'status-enabled' : 'status-disabled' }}"></span>
                                    {{ $jwtStatus['auto_refresh_enabled'] ? '활성화' : '비활성화' }}
                                </div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-sync-alt fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-content>
            <x-content-main>
                <!-- Configuration Form -->
                <form id="jwtConfigForm">
                    @csrf

                    <x-tab-card :tabs="[
                        ['id' => 'basic-settings', 'title' => '기본 설정', 'icon' => 'fas fa-cog'],
                        ['id' => 'remember-settings', 'title' => '로그인 상태 유지', 'icon' => 'fas fa-clock'],
                        ['id' => 'token-settings', 'title' => '토큰 설정', 'icon' => 'fas fa-key'],
                        ['id' => 'auto-refresh-settings', 'title' => '자동 새로고침', 'icon' => 'fas fa-sync-alt'],
                        ['id' => 'cookie-settings', 'title' => '쿠키 설정', 'icon' => 'fas fa-cookie-bite'],
                        ['id' => 'logging-settings', 'title' => '로깅 설정', 'icon' => 'fas fa-file-alt']
                    ]">

                        <!-- Basic Settings Tab -->
                        <x-tab-pane id="basic-settings" :active="true" ariaLabelledby="basic-settings-tab">
                            <div class="config-section">
                                <h5 class="mb-3"><i class="fas fa-cog text-primary"></i> 기본 설정</h5>
                                <p class="text-muted mb-4">
                                    <i class="fas fa-info-circle"></i>
                                    JWT 인증 시스템의 기본 동작을 설정합니다. JWT는 무상태(stateless) 인증 방식으로 서버 세션에 의존하지 않고 토큰 자체에 사용자 정보를 포함합니다.
                                </p>

                                <div class="form-group">
                                    <x-switch name="enable" :checked="$jwtConfig['enable']">JWT 인증 활성화</x-switch>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-lightbulb text-warning"></i>
                                        JWT 인증 시스템을 사용할지 결정합니다. 비활성화하면 기존 세션 기반 인증을 사용합니다.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <label for="algorithm">JWT 알고리즘</label>
                                    <select class="form-control" id="algorithm" name="algorithm">
                                        <option value="HS256"
                                            {{ ($jwtConfig['algorithm'] ?? 'HS256') === 'HS256' ? 'selected' : '' }}>HS256</option>
                                        <option value="HS384"
                                            {{ ($jwtConfig['algorithm'] ?? 'HS256') === 'HS384' ? 'selected' : '' }}>HS384</option>
                                        <option value="HS512"
                                            {{ ($jwtConfig['algorithm'] ?? 'HS256') === 'HS512' ? 'selected' : '' }}>HS512</option>
                                    </select>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-shield-alt text-info"></i>
                                        JWT 토큰 서명에 사용할 해시 알고리즘입니다. HS256(권장), HS384, HS512 중 선택하세요. 높은 숫자일수록 보안성이 높지만 처리 시간이 증가합니다.
                                    </small>
                                </div>

                                <div class="form-group">
                                    <x-input-text type="password" name="secret" label="JWT Secret" :value="$jwtConfig['secret'] ?? ''" placeholder="비워두면 APP_KEY 사용">
                                        <i class="fas fa-key text-danger"></i>
                                        JWT 토큰 서명에 사용할 비밀키입니다. 최소 32자 이상 권장하며, 비워두면 Laravel의 APP_KEY를 사용합니다. 이 키가 노출되면 보안이 크게 위험해집니다.
                                    </x-input-text>
                                </div>
                            </div>
                        </x-tab-pane>

                        <!-- Remember Settings Tab -->
                        <x-tab-pane id="remember-settings" ariaLabelledby="remember-settings-tab">
                            <div class="config-section">
                        <h5 class="mb-3"><i class="fas fa-clock text-success"></i> 로그인 상태 유지 설정</h5>
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle"></i>
                            사용자가 "로그인 상태 유지"를 체크했을 때의 동작을 설정합니다. 이 기능을 활성화하면 토큰의 유효시간이 자동으로 연장됩니다.
                        </p>

                        <div class="form-group">
                            <x-switch name="remember[enable]" :checked="$jwtConfig['remember']['enable'] ?? true">Remember 기능 활성화</x-switch>
                            <small class="form-text text-muted">
                                <i class="fas fa-toggle-on text-success"></i>
                                로그인 폼의 "로그인 상태 유지" 체크박스 기능을 활성화합니다. 비활성화하면 항상 기본 유효시간을 사용합니다.
                            </small>
                        </div>

                        <div class="form-group">
                            <x-switch name="remember[extend_access_token]" :checked="$jwtConfig['remember']['extend_access_token'] ?? true">Access Token 연장</x-switch>
                            <small class="form-text text-muted">
                                <i class="fas fa-clock text-primary"></i>
                                Remember 체크 시 Access Token의 유효시간을 "Remember 시 유효시간"으로 연장합니다.
                            </small>
                        </div>

                        <div class="form-group">
                            <x-switch name="remember[extend_refresh_token]" :checked="$jwtConfig['remember']['extend_refresh_token'] ?? true">Refresh Token 연장</x-switch>
                            <small class="form-text text-muted">
                                <i class="fas fa-sync-alt text-info"></i>
                                Remember 체크 시 Refresh Token의 유효시간을 "Remember 시 유효시간"으로 연장합니다.
                            </small>
                        </div>

                        <div class="form-group">
                            <x-input-text name="remember[cookie_name]" label="Remember 쿠키명" :value="$jwtConfig['remember']['cookie_name'] ?? 'remember_token'">
                                <i class="fas fa-cookie text-secondary"></i>
                                브라우저에 저장되는 Remember 기능용 쿠키의 이름입니다.
                            </x-input-text>
                        </div>

                        <div class="form-group">
                            <x-input-number name="remember[cookie_lifetime]" label="Remember 쿠키 수명 (분)" :value="$jwtConfig['remember']['cookie_lifetime'] ?? 43200">
                                <small class="form-text text-muted">
                                    <i class="fas fa-calendar-alt text-info"></i>
                                    Remember 쿠키가 브라우저에 보관되는 시간입니다. 기본값: 43200분 (30일)
                                </small>
                            </x-input-number>
                        </div>
                            </div>
                        </x-tab-pane>

                        <!-- Token Settings Tab -->
                        <x-tab-pane id="token-settings" ariaLabelledby="token-settings-tab">
                            <div class="config-section">
                        <h5 class="mb-3"><i class="fas fa-key text-warning"></i> 토큰 유효시간 설정</h5>
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle"></i>
                            JWT 토큰의 유효시간을 설정합니다. Access Token은 API 접근용, Refresh Token은 Access Token 갱신용입니다. 짧을수록 보안성이 높지만 사용자 편의성은 떨어집니다.
                        </p>

                        <h6 class="text-muted">Access Token</h6>
                        <x-input-number name="access_token[default_expiry]" label="기본 유효시간 (초)" :value="$jwtConfig['access_token']['default_expiry'] ?? 3600">
                            <small class="form-text text-muted">기본값: 3600초 (1시간)</small>
                        </x-input-number>

                        <x-input-number name="access_token[remember_expiry]" label="Remember 시 유효시간 (초)" :value="$jwtConfig['access_token']['remember_expiry'] ?? 86400">
                            <small class="form-text text-muted">기본값: 86400초 (24시간)</small>
                        </x-input-number>

                        <x-input-number name="access_token[max_expiry]" label="최대 유효시간 (초)" :value="$jwtConfig['access_token']['max_expiry'] ?? 604800">
                            <small class="form-text text-muted">기본값: 604800초 (7일)</small>
                        </x-input-number>

                        <hr class="my-4">

                        <h6 class="text-muted">Refresh Token</h6>
                        <x-input-number name="refresh_token[default_expiry]" label="기본 유효시간 (초)" :value="$jwtConfig['refresh_token']['default_expiry'] ?? 2592000">
                            <small class="form-text text-muted">기본값: 2592000초 (30일)</small>
                        </x-input-number>

                        <x-input-number name="refresh_token[remember_expiry]" label="Remember 시 유효시간 (초)" :value="$jwtConfig['refresh_token']['remember_expiry'] ?? 7776000">
                            <small class="form-text text-muted">기본값: 7776000초 (90일)</small>
                        </x-input-number>

                        <x-input-number name="refresh_token[max_expiry]" label="최대 유효시간 (초)" :value="$jwtConfig['refresh_token']['max_expiry'] ?? 15552000">
                            <small class="form-text text-muted">기본값: 15552000초 (180일)</small>
                        </x-input-number>
                            </div>
                        </x-tab-pane>

                        <!-- Auto Refresh Settings Tab -->
                        <x-tab-pane id="auto-refresh-settings" ariaLabelledby="auto-refresh-settings-tab">
                            <div class="config-section">
                        <h5 class="mb-3"><i class="fas fa-sync-alt text-info"></i> 자동 새로고침 설정</h5>
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle"></i>
                            Access Token이 만료되기 전에 자동으로 새로운 토큰을 발급받는 기능입니다. 사용자가 중단 없이 서비스를 이용할 수 있게 도와줍니다.
                        </p>

                        <div class="form-group">
                            <x-switch name="auto_refresh[enable]" :checked="$jwtConfig['auto_refresh']['enable'] ?? true">자동 토큰 새로고침 활성화</x-switch>
                            <small class="form-text text-muted">
                                <i class="fas fa-magic text-info"></i>
                                토큰 만료 전 자동으로 새로운 토큰을 발급받아 세션 유지를 돕습니다.
                            </small>
                        </div>

                        <x-input-number name="auto_refresh[threshold]" label="새로고침 임계값 (초)" :value="$jwtConfig['auto_refresh']['threshold'] ?? 300">
                            <small class="form-text text-muted">토큰 만료 전 몇 초에 새로고침할지 설정 (기본: 300초)</small>
                        </x-input-number>

                        <x-input-number name="auto_refresh[grace_period]" label="유예 시간 (초)" :value="$jwtConfig['auto_refresh']['grace_period'] ?? 60">
                            <small class="form-text text-muted">새로고침 실패 시 유예 시간 (기본: 60초)</small>
                        </x-input-number>
                            </div>
                        </x-tab-pane>

                        <!-- Cookie Settings Tab -->
                        <x-tab-pane id="cookie-settings" ariaLabelledby="cookie-settings-tab">
                            <div class="config-section">
                        <h5 class="mb-3"><i class="fas fa-cookie-bite text-secondary"></i> 쿠키 설정</h5>
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle"></i>
                            웹 브라우저에 저장되는 JWT 토큰 쿠키의 설정입니다. HttpOnly와 Secure 옵션으로 보안을 강화할 수 있습니다.
                        </p>
                        <div class="row">
                            <div class="col-md-6">
                                <h6 class="text-muted">Access Token 쿠키</h6>
                                <x-input-text name="cookies[access_token][name]" label="쿠키명" :value="$jwtConfig['cookies']['access_token']['name'] ?? 'access_token'"></x-input-text>

                                <x-input-number name="cookies[access_token][lifetime]" label="수명 (분)" :value="$jwtConfig['cookies']['access_token']['lifetime'] ?? 60"></x-input-number>

                                <div class="form-group">
                                    <x-switch name="cookies[access_token][httponly]" :checked="$jwtConfig['cookies']['access_token']['httponly'] ?? false">HttpOnly</x-switch>
                                </div>
                                <div class="form-group">
                                    <x-switch name="cookies[access_token][secure]" :checked="$jwtConfig['cookies']['access_token']['secure'] ?? false">Secure</x-switch>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <h6 class="text-muted">Refresh Token 쿠키</h6>
                                <x-input-text name="cookies[refresh_token][name]" label="쿠키명" :value="$jwtConfig['cookies']['refresh_token']['name'] ?? 'refresh_token'"></x-input-text>

                                <x-input-number name="cookies[refresh_token][lifetime]" label="수명 (분)" :value="$jwtConfig['cookies']['refresh_token']['lifetime'] ?? 43200"></x-input-number>

                                <div class="form-group">
                                    <x-switch name="cookies[refresh_token][httponly]" :checked="$jwtConfig['cookies']['refresh_token']['httponly'] ?? true">HttpOnly</x-switch>
                                </div>
                                <div class="form-group">
                                    <x-switch name="cookies[refresh_token][secure]" :checked="$jwtConfig['cookies']['refresh_token']['secure'] ?? false">Secure</x-switch>
                                </div>
                            </div>
                        </div>
                            </div>
                        </x-tab-pane>

                        <!-- Logging Settings Tab -->
                        <x-tab-pane id="logging-settings" ariaLabelledby="logging-settings-tab">
                            <div class="config-section">
                        <h5 class="mb-3"><i class="fas fa-file-alt text-dark"></i> 로깅 설정</h5>
                        <p class="text-muted mb-4">
                            <i class="fas fa-info-circle"></i>
                            JWT 인증 관련 활동을 로그로 기록하는 설정입니다. 보안 감사와 문제 해결에 도움이 됩니다.
                        </p>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-switch name="logging[enable]" :checked="$jwtConfig['logging']['enable'] ?? true">로깅 활성화</x-switch>
                                    <small class="form-text text-muted">전체 로깅 기능 활성화</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-switch name="logging[log_successful_auth]" :checked="$jwtConfig['logging']['log_successful_auth'] ?? true">성공 인증 로그</x-switch>
                                    <small class="form-text text-muted">성공한 로그인 기록</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-switch name="logging[log_failed_auth]" :checked="$jwtConfig['logging']['log_failed_auth'] ?? true">실패 인증 로그</x-switch>
                                    <small class="form-text text-muted">실패한 로그인 시도 기록</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <x-switch name="logging[log_token_refresh]" :checked="$jwtConfig['logging']['log_token_refresh'] ?? true">토큰 새로고침 로그</x-switch>
                                    <small class="form-text text-muted">토큰 갱신 활동 기록</small>
                                </div>
                            </div>
                        </div>
                            </div>
                        </x-tab-pane>

                    </x-tab-card>
                </form>

            </x-content-main>
            <x-content-side>
                <div class="card shadow mb-3">
                    <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                        <h6 class="m-0 font-weight-bold text-primary">현재 토큰 유효시간</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                <h6 class="text-muted">Access Token</h6>
                                <p class="mb-0">기본: <span
                                        class="badge badge-secondary badge-duration">{{ $jwtStatus['token_info']['access_default'] }}</span>
                                </p>
                                <p class="mb-0">Remember: <span
                                        class="badge badge-info badge-duration">{{ $jwtStatus['token_info']['access_remember'] }}</span>
                                </p>
                            </div>
                            <div class="col-md-3">
                                <h6 class="text-muted">Refresh Token</h6>
                                <p class="mb-0">기본: <span
                                        class="badge badge-secondary badge-duration">{{ $jwtStatus['token_info']['refresh_default'] }}</span>
                                </p>
                                <p class="mb-0">Remember: <span
                                        class="badge badge-info badge-duration">{{ $jwtStatus['token_info']['refresh_remember'] }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card shadow">
                    <div class="card-header py-3">
                        <h6 class="m-0 font-weight-bold text-primary">시스템 정보</h6>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4">
                                <p><strong>설정 파일:</strong>
                                    <span
                                        class="badge {{ $jwtStatus['system']['config_exists'] ? 'badge-success' : 'badge-danger' }}">
                                        {{ $jwtStatus['system']['config_exists'] ? '존재함' : '없음' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>쓰기 권한:</strong>
                                    <span
                                        class="badge {{ $jwtStatus['system']['config_writable'] ? 'badge-success' : 'badge-danger' }}">
                                        {{ $jwtStatus['system']['config_writable'] ? '가능' : '불가능' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <p><strong>JWT Secret:</strong>
                                    <span
                                        class="badge {{ $jwtStatus['system']['jwt_secret_set'] ? 'badge-success' : 'badge-warning' }}">
                                        {{ $jwtStatus['system']['jwt_secret_set'] ? '설정됨' : '미설정' }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <p class="mb-0 text-muted"><small>설정 파일 경로: {{ $configPath }}</small></p>
                    </div>
                </div>
            </x-content-side>
        </x-content>






    </div>

@endsection

@push('scripts')
    <script>
        function showSuccessMessage(message) {
            const alertDiv = document.getElementById('success-alert');
            const messageSpan = document.getElementById('success-message');

            messageSpan.textContent = message;
            alertDiv.style.display = 'block';
            alertDiv.classList.add('show');

            // 5초 후 자동으로 숨기기
            setTimeout(function() {
                alertDiv.classList.remove('show');
                setTimeout(function() {
                    alertDiv.style.display = 'none';
                }, 150);
            }, 5000);
        }

        function saveConfig() {
            const form = document.getElementById('jwtConfigForm');
            const formData = new FormData(form);

            // FormData를 JSON으로 변환
            const data = {};
            for (let [key, value] of formData.entries()) {
                const keys = key.split(/[\[\]]+/).filter(k => k);
                let current = data;

                for (let i = 0; i < keys.length - 1; i++) {
                    if (!current[keys[i]]) {
                        current[keys[i]] = {};
                    }
                    current = current[keys[i]];
                }

                // 체크박스와 숫자 처리
                if (value === 'on') {
                    value = true;
                } else if (value === 'off' || value === '') {
                    value = false;
                } else if (!isNaN(value) && value !== '') {
                    value = parseInt(value);
                }

                current[keys[keys.length - 1]] = value;
            }

            // 체크되지 않은 체크박스들을 false로 설정
            const checkboxes = form.querySelectorAll('input[type="checkbox"]');
            checkboxes.forEach(checkbox => {
                if (!checkbox.checked) {
                    const keys = checkbox.name.split(/[\[\]]+/).filter(k => k);
                    let current = data;

                    for (let i = 0; i < keys.length - 1; i++) {
                        if (!current[keys[i]]) {
                            current[keys[i]] = {};
                        }
                        current = current[keys[i]];
                    }
                    current[keys[keys.length - 1]] = false;
                }
            });

            fetch('{{ route('admin.auth.jwt.update') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content') ||
                            formData.get('_token')
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // 성공 알림 표시
                        showSuccessMessage(data.message);

                        // SweetAlert도 함께 표시
                        Swal.fire({
                            icon: 'success',
                            title: '저장 완료',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end'
                        }).then(() => {
                            // 페이지 새로고침 대신 폼 데이터만 업데이트
                            setTimeout(() => {
                                location.reload();
                            }, 1000);
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '저장 실패',
                            text: data.message,
                            footer: data.errors ? Object.values(data.errors).flat().join('<br>') : ''
                        });
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: '오류 발생',
                        text: '설정 저장 중 오류가 발생했습니다.'
                    });
                });
        }

        function resetConfig() {
            Swal.fire({
                title: '설정 초기화',
                text: '정말로 JWT 설정을 기본값으로 초기화하시겠습니까?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: '초기화',
                cancelButtonText: '취소'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch('{{ route('admin.auth.jwt.reset') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                    'content')
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // 성공 알림 표시
                                showSuccessMessage(data.message);

                                // SweetAlert도 함께 표시
                                Swal.fire({
                                    icon: 'success',
                                    title: '초기화 완료',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false,
                                    toast: true,
                                    position: 'top-end'
                                }).then(() => {
                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);
                                });
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: '초기화 실패',
                                    text: data.message
                                });
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire({
                                icon: 'error',
                                title: '오류 발생',
                                text: '설정 초기화 중 오류가 발생했습니다.'
                            });
                        });
                }
            });
        }

        // 페이지 로드 시 SweetAlert2 로드 확인
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof Swal === 'undefined') {
                console.warn('SweetAlert2가 로드되지 않았습니다.');
            }
        });
    </script>
@endpush
