<div class="card">
    <div class="card-header d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-0">비밀번호 변경</h4>
            <p class="text-muted mb-0 small">보안을 위해 정기적으로 변경해 주세요.</p>
        </div>
        <i class="bi bi-shield-lock-fill text-danger fs-4"></i>
    </div>
    <div class="card-body">
        <div id="passwordAlert"></div>
        <form id="passwordForm" action="{{ route('home.account.update') }}" method="POST">
            @csrf
            @method('PUT')
            <input type="hidden" name="form_type" value="password">

            <div class="mb-4">
                <label for="password" class="form-label fw-semibold">
                    새 비밀번호 <span class="text-danger">*</span>
                </label>
                <input type="password"
                       class="form-control"
                       id="password"
                       name="password"
                       autocomplete="new-password"
                       required>
                <div class="form-text">최소 8자 이상, 공백 없이 입력하세요.</div>
            </div>

            <div class="mb-4">
                <label for="password_confirmation" class="form-label fw-semibold">
                    비밀번호 확인 <span class="text-danger">*</span>
                </label>
                <input type="password"
                       class="form-control"
                       id="password_confirmation"
                       name="password_confirmation"
                       autocomplete="new-password"
                       required>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <button type="submit" class="btn btn-outline-primary" data-loading-text="변경 중...">
                    <i class="bi bi-lock-fill me-2"></i>비밀번호 변경
                </button>
            </div>
        </form>
    </div>
</div>
