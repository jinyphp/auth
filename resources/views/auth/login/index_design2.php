<?php
    /**
     * 로그인 디자인 v2 (Bootstrap 미니멀 카드 레이아웃)
     *
     * 목표
     * - 불필요한 장식과 커스텀 스타일을 줄이고, Bootstrap 기본 컴포넌트로
     *   시멘틱하고 균형 잡힌 로그인 화면을 구성합니다.
     *
     * 특징
     * - 기존 기능(플래시 메시지, $errors, CSRF, 소셜 로그인, 개발 배지, 비밀번호 토글) 보존
     * - 중앙 정렬된 단일 카드, 가독성 높은 폼 구성
     */
?>

<!-- 레이아웃: 수직 중앙 정렬 + 최대 폭 제한 -->
<section class="container min-vh-100 d-flex align-items-center py-5">
    <div class="row justify-content-center w-100">
        <div class="col-12 col-md-10 col-lg-7 col-xl-6">
            <div class="card shadow-sm position-relative">
                <?php if (!empty($dev_info)): ?>
                    <!-- 개발 환경 배지: 우상단 고정 -->
                    <div style="position: absolute; top: 12px; right: 12px; z-index: 10; display: flex; gap: 8px;">
                        <span class="badge bg-primary"><?= strtoupper($dev_info['auth_method'] ?? 'N/A') ?></span>
                        <span class="badge <?= !empty($dev_info['sharding_enabled']) ? 'bg-success' : 'bg-secondary' ?>">
                            Sharding: <?= !empty($dev_info['sharding_enabled']) ? 'ON' : 'OFF' ?>
                        </span>
                    </div>
                <?php endif; ?>

                <div class="card-body p-4 p-lg-5">
                    <!-- 헤더: 로고 + 제목 -->
                    <div class="text-center mb-4">
                        <a href="/">
                            <img src="<?= asset('assets/images/brand/logo/logo-icon.svg') ?>" alt="logo-icon" width="36" height="36" class="mb-2" />
                        </a>
                        <h1 class="h3 fw-bold mb-1">로그인</h1>
                        <p class="text-muted mb-0">
                            계정이 없으신가요?
                            <a href="<?= route('register') ?>" class="link-primary">회원가입</a>
                        </p>
                    </div>

                    <!-- 플래시 메시지 영역 -->
                    <?php if (session('success')): ?>
                        <div class="alert alert-success" role="alert">
                            <?= e(session('success')) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session('error')): ?>
                        <div class="alert alert-danger" role="alert">
                            <?= e(session('error')) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (session('info')): ?>
                        <div class="alert alert-info" role="alert">
                            <?= e(session('info')) ?>
                        </div>
                    <?php endif; ?>

                    <!-- 로그인 폼 -->
                    <form class="needs-validation" action="<?= route('login.submit') ?>" method="POST" novalidate autocomplete="on">
                        <?= csrf_field() ?>

                        <!-- 이메일 -->
                        <div class="mb-3">
                            <label for="email" class="form-label">이메일</label>
                            <input
                                type="email"
                                id="email"
                                name="email"
                                class="form-control <?= ($errors?->has('email') ? 'is-invalid' : '') ?>"
                                placeholder="이메일 주소를 입력하세요"
                                value="<?= e(old('email')) ?>"
                                required
                                inputmode="email"
                                autocomplete="email"
                            />
                            <?php if ($errors?->has('email')): ?>
                                <div class="invalid-feedback d-block"><?= e($errors->first('email')) ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">유효한 이메일을 입력해주세요.</div>
                            <?php endif; ?>
                        </div>

                        <!-- 비밀번호 + 토글 -->
                        <div class="mb-3">
                            <label for="password" class="form-label">비밀번호</label>
                            <div class="input-group">
                                <input
                                    type="password"
                                    id="password"
                                    name="password"
                                    class="form-control <?= ($errors?->has('password') ? 'is-invalid' : '') ?>"
                                    placeholder="**************"
                                    required
                                    minlength="6"
                                    autocomplete="current-password"
                                />
                                <button class="btn btn-outline-secondary" type="button" id="togglePasswordBtn" aria-label="비밀번호 표시 전환">보기</button>
                            </div>
                            <?php if ($errors?->has('password')): ?>
                                <div class="invalid-feedback d-block"><?= e($errors->first('password')) ?></div>
                            <?php else: ?>
                                <div class="invalid-feedback">유효한 비밀번호를 입력해주세요.</div>
                            <?php endif; ?>
                        </div>

                        <!-- 옵션 -->
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    class="form-check-input"
                                    id="remember"
                                    name="remember"
                                    <?= old('remember') ? 'checked' : '' ?>
                                />
                                <label class="form-check-label" for="remember">로그인 상태 유지</label>
                            </div>
                            <a href="<?= route('password.request') ?>" class="link-secondary">비밀번호를 잊으셨나요?</a>
                        </div>

                        <!-- 제출 -->
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">로그인</button>
                        </div>

                        <!-- 구분선 -->
                        <div class="text-center my-4">
                            <span class="text-muted small">또는</span>
                        </div>

                        <!-- 소셜 로그인: 활성 제공자만 버튼 렌더 -->
                        <?php if (class_exists('Jiny\Social\Models\UserOAuthProvider')): ?>
                            <?php $socialProviders = \Jiny\Social\Models\UserOAuthProvider::getEnabled(); ?>
                            <?php if ($socialProviders && $socialProviders->count() > 0): ?>
                                <div class="row g-2">
                                    <?php foreach ($socialProviders as $provider): ?>
                                        <div class="col-12 col-sm-6">
                                            <a href="<?= route('social.login', $provider->provider) ?>"
                                               class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center"
                                               title="<?= e($provider->name) ?> 로그인">
                                                <?php if (!empty($provider->icon) && !str_starts_with($provider->icon, 'bi ')): ?>
                                                    <img src="<?= $provider->icon ?>" alt="<?= e($provider->name) ?>"
                                                         class="me-2" style="width: 18px; height: 18px;">
                                                <?php endif; ?>
                                                <?php if ($provider->provider === 'google'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-google me-2" viewBox="0 0 16 16">
                                                        <path d="M15.545 6.558a9.42 9.42 0 0 1 .139 1.626c0 2.434-.87 4.492-2.384 5.885h.002C11.978 15.292 10.158 16 8 16A8 8 0 1 1 8 0a7.689 7.689 0 0 1 5.352 2.082l-2.284 2.284A4.347 4.347 0 0 0 8 3.166c-2.087 0-3.86 1.408-4.492 3.304a4.792 4.792 0 0 0 0 3.063h.003c.635 1.893 2.405 3.301 4.492 3.301 1.078 0 2.004-.276 2.722-.764h-.003a3.702 3.702 0 0 0 1.599-2.431H8v-3.08h7.545z" />
                                                    </svg>
                                                <?php elseif ($provider->provider === 'kakao'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-chat-fill me-2" viewBox="0 0 16 16">
                                                        <path d="M8 15c4.418 0 8-3.134 8-7s-3.582-7-8-7-8 3.134-8 7c0 1.76.743 3.37 1.97 4.6-.097 1.016-.417 2.13-.771 2.966-.079.186.074.394.273.362 2.256-.37 3.597-.938 4.18-1.234A9.06 9.06 0 0 0 8 15z" />
                                                    </svg>
                                                <?php elseif ($provider->provider === 'naver'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-n-square me-2" viewBox="0 0 16 16">
                                                        <path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V2Zm8.93 4.588-2.29 4.004V5.5H5.5v5h1.14l2.29-4.004V11.5H10v-5H8.93Z" />
                                                    </svg>
                                                <?php elseif ($provider->provider === 'github'): ?>
                                                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-github me-2" viewBox="0 0 16 16">
                                                        <path d="M8 0C3.58 0 0 3.58 0 8c0 3.54 2.29 6.53 5.47 7.59.4.07.55-.17.55-.38 0-.19-.01-.82-.01-1.49-2.01.37-2.53-.49-2.69-.94-.09-.23-.48-.94-.82-1.13-.28-.15-.68-.52-.01-.53.63-.01 1.08.58 1.23.82.72 1.21 1.87.87 2.33.66.07-.52.28-.87.51-1.07-1.78-.2-3.64-.89-3.64-3.95 0-.87.31-1.59.82-2.15-.08-.2-.36-1.02.08-2.12 0 0 .67-.21 2.2.82.64-.18 1.32-.27 2-.27.68 0 1.36.09 2 .27 1.53-1.04 2.2-.82 2.2-.82.44 1.1.16 1.92.08 2.12.51.56.82 1.27.82 2.15 0 3.07-1.87 3.75-3.65 3.95.29.25.54.73.54 1.48 0 1.07-.01 1.93-.01 2.2 0 .21.15.46.55.38A8.012 8.012 0 0 0 16 8c0-4.42-3.58-8-8-8z" />
                                                    </svg>
                                                <?php elseif (!empty($provider->icon)): ?>
                                                    <i class="<?= $provider->icon ?> me-2"></i>
                                                <?php endif; ?>
                                                <?= e($provider->name) ?>
                                            </a>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </form>

                    <!-- 풋터: 저작권 -->
                    <div class="mt-4 text-center text-muted small">
                        © <?= date('Y') ?> JinyCMS. All rights reserved.
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- 종속 스크립트: 레이아웃에서 관리 중이면 중복 제거 가능 -->
<script src="<?= asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') ?>"></script>
<script src="<?= asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') ?>"></script>
<script src="<?= asset('assets/libs/simplebar/dist/simplebar.min.js') ?>"></script>
<script src="<?= asset('assets/js/vendors/validation.js') ?>"></script>

<!-- 비밀번호 표시/숨김 토글 -->
<script>
    (function () {
        var toggleBtn = document.getElementById('togglePasswordBtn');
        var input = document.getElementById('password');
        if (toggleBtn && input) {
            toggleBtn.addEventListener('click', function () {
                var isPassword = input.getAttribute('type') === 'password';
                input.setAttribute('type', isPassword ? 'text' : 'password');
                toggleBtn.textContent = isPassword ? '숨기기' : '보기';
            });
        }
    })();
</script>

