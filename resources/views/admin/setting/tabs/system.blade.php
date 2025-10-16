<div class="row">
    <div class="col-lg-8">
        <!-- 전역 설정 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-globe me-2 text-primary"></i>전역 설정
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">시스템 활성화</label>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="system_enable" id="system_enable"
                                {{ ($settings['enable'] ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label" for="system_enable">
                                인증 시스템 전체 활성화
                            </label>
                        </div>
                        <div class="form-text">비활성화 시 모든 인증 기능이 중단됩니다.</div>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label for="system_method" class="form-label">인증 방법</label>
                        <select class="form-select" name="system_method" id="system_method">
                            <option value="session" {{ ($settings['method'] ?? 'jwt') === 'session' ? 'selected' : '' }}>
                                세션 기반 인증
                            </option>
                            <option value="jwt" {{ ($settings['method'] ?? 'jwt') === 'jwt' ? 'selected' : '' }}>
                                JWT 토큰 인증
                            </option>
                        </select>
                        <div class="form-text">기본 인증 방식을 선택합니다.</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 유지보수 모드 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="mb-0">
                    <i class="fe fe-tool me-2 text-warning"></i>유지보수 모드
                </h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="maintenance_mode" id="maintenance_mode"
                            {{ ($settings['maintenance_mode'] ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label" for="maintenance_mode">
                            유지보수 모드 활성화
                        </label>
                    </div>
                    <div class="form-text">활성화 시 일반 사용자의 접근이 제한됩니다.</div>
                </div>

                <div class="mb-3">
                    <label for="maintenance_message" class="form-label">유지보수 메시지</label>
                    <textarea class="form-control" name="maintenance_message" id="maintenance_message" rows="3"
                        placeholder="사용자에게 표시할 유지보수 메시지를 입력하세요">{{ $settings['maintenance_message'] ?? '시스템 유지보수 중입니다.' }}</textarea>
                </div>

                <div class="mb-3">
                    <label for="maintenance_exclude_ips" class="form-label">제외 IP 주소</label>
                    <textarea class="form-control" name="maintenance_exclude_ips" id="maintenance_exclude_ips" rows="2"
                        placeholder="127.0.0.1, 192.168.1.100 (쉼표로 구분)">{{ is_array($settings['maintenance_exclude_ips'] ?? []) ? implode(', ', $settings['maintenance_exclude_ips']) : '' }}</textarea>
                    <div class="form-text">유지보수 모드에서도 접근을 허용할 IP 주소들을 입력하세요.</div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <!-- 시스템 정보 -->
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h6 class="mb-0">
                    <i class="fe fe-info me-2 text-info"></i>시스템 정보
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">PHP 버전:</span>
                    <span class="fw-medium">{{ PHP_VERSION }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Laravel 버전:</span>
                    <span class="fw-medium">{{ app()->version() }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Jiny-Auth 버전:</span>
                    <span class="fw-medium">1.0.0</span>
                </div>
                <div class="d-flex justify-content-between">
                    <span class="text-muted">환경:</span>
                    <span class="badge bg-{{ app()->environment() === 'production' ? 'success' : 'warning' }}">
                        {{ app()->environment() }}
                    </span>
                </div>
            </div>
        </div>

        <!-- 도움말 -->
        <div class="card">
            <div class="card-header bg-primary text-white">
                <h6 class="mb-0">
                    <i class="fe fe-help-circle me-2"></i>도움말
                </h6>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <h6 class="text-dark">시스템 활성화</h6>
                    <p class="small text-muted mb-0">
                        인증 시스템 전체를 활성화/비활성화합니다. 비활성화 시 모든 로그인, 회원가입 등이 중단됩니다.
                    </p>
                </div>
                <div class="mb-3">
                    <h6 class="text-dark">인증 방법</h6>
                    <p class="small text-muted mb-0">
                        <strong>세션:</strong> 전통적인 세션 기반 인증<br>
                        <strong>JWT:</strong> 토큰 기반 인증 (API 친화적)
                    </p>
                </div>
                <div>
                    <h6 class="text-dark">유지보수 모드</h6>
                    <p class="small text-muted mb-0">
                        시스템 업데이트나 점검 시 일반 사용자의 접근을 제한합니다. 관리자나 지정된 IP는 접근 가능합니다.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>