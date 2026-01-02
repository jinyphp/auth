@extends('jiny-auth::layouts.auth')

@section('title', '약관 동의')

@section('content')
    <section class="container d-flex flex-column">
        <div class="row align-items-center justify-content-center g-0 min-vh-100 py-8">
            <div class="col-lg-7 col-md-10 py-8 py-xl-0">
                <!-- Card -->
                <div class="card shadow">
                    <!-- Card body -->
                    <div class="card-body p-6">
                        <div class="mb-4">
                            <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4"
                                    alt="logo-icon" /></a>
                            <h1 class="mb-1 fw-bold">약관 동의</h1>
                            <div class="d-flex justify-content-between align-items-end">
                                <span>회원가입을 위해 약관에 동의해주세요.</span>
                                <span class="small text-muted">
                                    이미 계정이 있으신가요? <a href="{{ route('login') }}"
                                        class="text-primary text-decoration-none ms-1">로그인</a>
                                </span>
                            </div>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <strong>입력값 검증에 실패했습니다:</strong>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        @endif

                        <form action="{{ route('signup.terms.accept') }}" method="POST">
                            @csrf

                            <!-- Terms Agreement -->
                            <!-- 약관 목록은 JavaScript에서 API를 호출하여 동적으로 로드합니다 -->
                            <div class="mb-4" id="terms-container">
                                <!-- 로딩 상태 표시 -->
                                <div id="terms-loading" class="text-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">약관 목록을 불러오는 중...</span>
                                    </div>
                                    <p class="mt-2 text-muted">약관 목록을 불러오는 중입니다...</p>
                                </div>

                                <!-- 약관 목록이 없을 때 표시할 기본 메시지 (로딩 후 숨김) -->
                                <div id="terms-empty" class="alert alert-info d-none">
                                    <p class="mb-0">등록된 약관이 없습니다.</p>
                                </div>

                                <!-- 필수 약관 섹션 (JavaScript에서 동적으로 생성) -->
                                <div id="mandatory-terms-section" class="d-none">
                                    <h5 class="mb-3">필수 약관</h5>
                                    <div id="mandatory-terms-list"></div>
                                </div>

                                <!-- 선택 약관 섹션 (JavaScript에서 동적으로 생성) -->
                                <div id="optional-terms-section" class="d-none">
                                    <h5 class="mb-3 mt-4">선택 약관</h5>
                                    <div id="optional-terms-list"></div>
                                </div>

                                <!-- 전체 동의 / 필수 약관 동의 체크박스 (JavaScript에서 동적으로 생성) -->
                                <div id="agreement-controls" class="d-none">
                                    <hr class="my-4">
                                    <div class="d-flex gap-3 mb-3">
                                        <div class="form-check">
                                            <input type="checkbox" class="form-check-input" id="agreeAll">
                                            <label class="form-check-label fw-bold" for="agreeAll">
                                                전체 동의
                                            </label>
                                        </div>
                                        <div class="form-check" id="agree-mandatory-wrapper">
                                            <input type="checkbox" class="form-check-input" id="agreeMandatory">
                                            <label class="form-check-label" for="agreeMandatory">
                                                필수 약관 동의
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Buttons -->
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary" id="submit-terms-btn">
                                    <span class="btn-text">다음 단계</span>
                                    <span class="btn-spinner spinner-border spinner-border-sm d-none" role="status" aria-hidden="true"></span>
                                </button>
                                <a href="{{ route('login') }}" class="btn btn-outline-secondary">취소</a>
                            </div>
                        </form>
                    </div>
                </div>

                {{-- copyright --}}
                <div class="mt-6 text-sm text-gray-400 text-center">
                    <p class="mt-1">© 2025 JinyCMS. All rights reserved.</p>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            /**
             * 약관 동의 페이지 JavaScript
             * 
             * 주요 기능:
             * 1. 페이지 로드 시 약관 목록을 API에서 가져와 동적으로 렌더링
             * 2. 페이지 로드 시 버튼 상태 초기화 (뒤로가기 대응)
             * 3. 전체 동의 / 필수 약관 동의 체크박스 연동
             * 4. 개별 약관 체크박스 상태에 따른 상위 체크박스 자동 업데이트
             * 5. 필수 약관 동의 검증
             * 6. AJAX를 통한 약관 동의 제출 및 리다이렉트
             */
            document.addEventListener('DOMContentLoaded', function() {
                /**
                 * [0] 약관 목록을 API에서 가져와 동적으로 렌더링
                 * 
                 * 페이지 로드 시 약관 목록 API를 호출하여 약관 데이터를 가져온 후
                 * 동적으로 HTML을 생성하여 화면에 표시합니다.
                 */
                loadTermsFromAPI();

                /**
                 * [1] 페이지 로드 시 버튼 상태 초기화
                 * 
                 * 뒤로가기로 돌아왔을 때 버튼이 "처리 중..." 상태로 남아있는 것을 방지하기 위해
                 * 페이지 로드 시 항상 버튼을 초기 상태로 복원합니다.
                 */
                const submitBtn = document.getElementById('submit-terms-btn');
                const btnText = submitBtn?.querySelector('.btn-text');
                const btnSpinner = submitBtn?.querySelector('.btn-spinner');
                
                if (submitBtn && btnText) {
                    submitBtn.disabled = false;
                    btnText.textContent = '다음 단계';
                    btnText.style.display = 'inline';
                    if (btnSpinner) {
                        btnSpinner.classList.add('d-none');
                    }
                }

                /**
                 * 약관 목록을 API에서 가져오는 함수
                 * 
                 * GET /api/auth/v1/terms 엔드포인트를 호출하여 약관 목록을 가져옵니다.
                 * 성공 시 약관 목록을 동적으로 렌더링하고, 실패 시 에러 메시지를 표시합니다.
                 */
                async function loadTermsFromAPI() {
                    const loadingEl = document.getElementById('terms-loading');
                    const emptyEl = document.getElementById('terms-empty');
                    const mandatorySection = document.getElementById('mandatory-terms-section');
                    const optionalSection = document.getElementById('optional-terms-section');
                    const agreementControls = document.getElementById('agreement-controls');
                    const mandatoryList = document.getElementById('mandatory-terms-list');
                    const optionalList = document.getElementById('optional-terms-list');

                    try {
                        // API 호출
                        const response = await fetch('{{ route("api.auth.v1.terms") }}', {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                        }

                        const result = await response.json();

                        if (!result.success) {
                            throw new Error(result.message || '약관 목록을 불러오는데 실패했습니다.');
                        }

                        const { mandatory, optional, settings } = result.data;

                        // 약관 기능이 비활성화되어 있거나 약관이 없는 경우
                        if (!settings.enable || (mandatory.length === 0 && optional.length === 0)) {
                            loadingEl.classList.add('d-none');
                            emptyEl.classList.remove('d-none');
                            return;
                        }

                        // 로딩 상태 숨김
                        loadingEl.classList.add('d-none');

                        // 필수 약관 렌더링
                        if (mandatory.length > 0) {
                            mandatorySection.classList.remove('d-none');
                            mandatoryList.innerHTML = mandatory.map(term => renderTermCard(term, true)).join('');
                        }

                        // 선택 약관 렌더링
                        if (optional.length > 0) {
                            optionalSection.classList.remove('d-none');
                            optionalList.innerHTML = optional.map(term => renderTermCard(term, false)).join('');
                        }

                        // 전체 동의 / 필수 약관 동의 체크박스 표시
                        if (mandatory.length > 0 || optional.length > 0) {
                            agreementControls.classList.remove('d-none');
                            
                            // 필수 약관이 없으면 필수 약관 동의 체크박스 숨김
                            if (mandatory.length === 0) {
                                const agreeMandatoryWrapper = document.getElementById('agree-mandatory-wrapper');
                                if (agreeMandatoryWrapper) {
                                    agreeMandatoryWrapper.classList.add('d-none');
                                }
                            }
                        }

                        // 약관 목록 렌더링 후 체크박스 이벤트 핸들러 초기화
                        initializeCheckboxHandlers();

                    } catch (error) {
                        console.error('약관 목록 로드 실패:', error);
                        loadingEl.classList.add('d-none');
                        emptyEl.classList.remove('d-none');
                        emptyEl.innerHTML = `
                            <div class="alert alert-danger">
                                <strong>오류:</strong> 약관 목록을 불러오는 중 오류가 발생했습니다.
                                <br><small>${error.message}</small>
                                <br><button class="btn btn-sm btn-outline-primary mt-2" onclick="location.reload()">새로고침</button>
                            </div>
                        `;
                    }
                }

                /**
                 * 약관 카드 HTML 생성 함수
                 * 
                 * @param {Object} term 약관 데이터 객체
                 * @param {boolean} isMandatory 필수 약관 여부
                 * @returns {string} 약관 카드 HTML 문자열
                 */
                function renderTermCard(term, isMandatory) {
                    const badgeClass = isMandatory ? 'bg-danger' : 'bg-secondary';
                    const badgeText = isMandatory ? '필수' : '선택';
                    const requiredAttr = isMandatory ? 'required' : '';
                    const termClass = isMandatory ? 'mandatory-term' : 'optional-term';
                    
                    // 이전에 동의한 약관인지 확인
                    const agreedTerms = @json(session('agreed_terms', []));
                    const isChecked = agreedTerms.includes(term.id) ? 'checked' : '';
                    
                    // 약관 상세 페이지 링크 생성
                    const termUrl = '{{ route("terms.show", ":route_key") }}'.replace(':route_key', term.route_key || term.id);
                    
                    return `
                        <div class="card mb-3">
                            <div class="card-body">
                                <div class="form-check">
                                    <input type="checkbox"
                                        class="form-check-input term-checkbox ${termClass}"
                                        id="terms_${term.id}"
                                        name="terms[]"
                                        value="${term.id}"
                                        ${requiredAttr}
                                        ${isChecked}>
                                    <label class="form-check-label" for="terms_${term.id}">
                                        <div class="d-flex align-items-start">
                                            <span class="badge ${badgeClass} me-2">${badgeText}</span>
                                            <div>
                                                <div class="fw-bold">
                                                    <a href="${termUrl}"
                                                        class="text-decoration-none text-dark"
                                                        target="_blank">
                                                        ${term.title || '약관'}
                                                    </a>
                                                    ${term.version ? `<small class="text-muted ms-1">(v${term.version})</small>` : ''}
                                                </div>
                                                ${term.description ? `<div class="text-muted small mt-1">${term.description}</div>` : ''}
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                        </div>
                    `;
                }

                /**
                 * 체크박스 이벤트 핸들러 초기화 함수
                 * 
                 * 약관 목록이 동적으로 로드된 후 체크박스 이벤트 핸들러를 설정합니다.
                 */
                function initializeCheckboxHandlers() {
                    /**
                     * [2] DOM 요소 참조
                     * 
                     * 약관 동의 체크박스 및 제어 버튼들을 참조합니다.
                     */
                    const agreeAllBtn = document.getElementById('agreeAll'); // 전체 동의 체크박스
                    const agreeMandatoryBtn = document.getElementById('agreeMandatory'); // 필수 약관 동의 체크박스
                    const termCheckboxes = document.querySelectorAll('.term-checkbox'); // 모든 약관 체크박스
                    const mandatoryTerms = document.querySelectorAll('.mandatory-term'); // 필수 약관 체크박스만
                    const optionalTerms = document.querySelectorAll('.optional-term'); // 선택 약관 체크박스만

                    /**
                     * [3] 전체 동의 체크박스 이벤트 핸들러
                     * 
                     * "전체 동의" 체크박스를 클릭하면 모든 약관(필수 + 선택)에 동의/해제합니다.
                     * 필수 약관 동의 체크박스도 함께 업데이트합니다.
                     */
                    agreeAllBtn?.addEventListener('change', function() {
                        // 모든 약관 체크박스를 전체 동의 상태와 동기화
                        termCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });

                        // 필수 약관 동의 체크박스도 함께 업데이트
                        if (agreeMandatoryBtn) {
                            agreeMandatoryBtn.checked = this.checked;
                        }
                    });

                    /**
                     * [4] 필수 약관 동의 체크박스 이벤트 핸들러
                     * 
                     * "필수 약관 동의" 체크박스를 클릭하면 필수 약관만 일괄 동의/해제합니다.
                     * 전체 동의 체크박스 상태도 업데이트합니다.
                     */
                    agreeMandatoryBtn?.addEventListener('change', function() {
                        // 필수 약관 체크박스만 일괄 동의/해제
                        mandatoryTerms.forEach(checkbox => {
                            checkbox.checked = this.checked;
                        });

                        // 전체 동의 체크박스 상태 업데이트
                        updateAgreeAllState();
                    });

                    /**
                     * [5] 개별 약관 체크박스 변경 이벤트 핸들러
                     * 
                     * 개별 약관 체크박스를 클릭하면 상위 체크박스(전체 동의, 필수 약관 동의)의
                     * 상태를 자동으로 업데이트합니다.
                     */
                    termCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            updateAgreeAllState();
                            updateAgreeMandatoryState();
                        });
                    });

                    /**
                     * [6] 전체 동의 체크박스 상태 업데이트 함수
                     * 
                     * 모든 약관이 체크되어 있으면 "전체 동의" 체크박스를 체크하고,
                     * 하나라도 해제되어 있으면 체크를 해제합니다.
                     */
                    function updateAgreeAllState() {
                        if (agreeAllBtn) {
                            const allChecked = Array.from(termCheckboxes).every(checkbox => checkbox.checked);
                            agreeAllBtn.checked = allChecked;
                        }
                    }

                    /**
                     * [7] 필수 약관 동의 체크박스 상태 업데이트 함수
                     * 
                     * 모든 필수 약관이 체크되어 있으면 "필수 약관 동의" 체크박스를 체크하고,
                     * 하나라도 해제되어 있으면 체크를 해제합니다.
                     */
                    function updateAgreeMandatoryState() {
                        if (agreeMandatoryBtn) {
                            const allMandatoryChecked = Array.from(mandatoryTerms).every(checkbox => checkbox.checked);
                            agreeMandatoryBtn.checked = allMandatoryChecked;
                        }
                    }

                    /**
                     * [8] 초기 로드 시 체크박스 상태 동기화
                     * 
                     * 페이지 로드 시 이미 체크된 약관이 있으면(뒤로가기 등),
                     * 전체 동의 및 필수 약관 동의 체크박스 상태를 업데이트합니다.
                     */
                    updateAgreeAllState();
                    updateAgreeMandatoryState();
                }

                /**
                 * [9] 폼 제출 이벤트 핸들러
                 * 
                 * 약관 동의 폼을 제출할 때:
                 * 1. 필수 약관 동의 여부 검증
                 * 2. CSRF 토큰 확인
                 * 3. 버튼을 "처리 중..." 상태로 변경
                 * 4. AJAX로 약관 동의 정보를 서버에 전송
                 * 5. 성공 시 회원가입 폼으로 리다이렉트
                 * 6. 실패 시 에러 메시지 표시 및 버튼 상태 복원
                 */
                const form = document.querySelector('form');
                form?.addEventListener('submit', function(e) {
                    // 기본 폼 제출 동작 방지 (AJAX로 처리하기 위해)
                    e.preventDefault();

                    /**
                     * [9-1] 필수 약관 동의 검증
                     * 
                     * 필수 약관이 하나라도 체크되지 않았으면 제출을 중단하고
                     * 사용자에게 알림을 표시합니다.
                     * 약관 목록이 동적으로 로드되므로 매번 DOM에서 다시 조회합니다.
                     */
                    const mandatoryCheckboxes = document.querySelectorAll('.mandatory-term');
                    const mandatoryChecked = Array.from(mandatoryCheckboxes).every(checkbox => checkbox.checked);

                    if (mandatoryCheckboxes.length > 0 && !mandatoryChecked) {
                        alert('필수 약관에 모두 동의해주세요.');
                        return false;
                    }

                    /**
                     * [9-2] CSRF 토큰 가져오기
                     * 
                     * AJAX 요청에 필요한 CSRF 토큰을 meta 태그 또는 hidden input에서 가져옵니다.
                     * 토큰이 없으면 요청을 중단합니다.
                     */
                    const formData = new FormData(this);
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ||
                                      document.querySelector('input[name="_token"]')?.value;
                    
                    if (!csrfToken) {
                        alert('CSRF 토큰을 찾을 수 없습니다. 페이지를 새로고침해주세요.');
                        return false;
                    }

                    /**
                     * [9-3] 버튼 상태 변경 (로딩 표시)
                     * 
                     * 제출 버튼을 비활성화하고 "처리 중..." 스피너를 표시합니다.
                     * 이렇게 하면 사용자가 중복 제출하는 것을 방지할 수 있습니다.
                     */
                    const submitBtn = document.getElementById('submit-terms-btn');
                    const btnText = submitBtn?.querySelector('.btn-text');
                    const btnSpinner = submitBtn?.querySelector('.btn-spinner');
                    
                    if (submitBtn && btnText) {
                        submitBtn.disabled = true; // 버튼 비활성화
                        btnText.style.display = 'none'; // 텍스트 숨김
                        if (btnSpinner) {
                            btnSpinner.classList.remove('d-none'); // 스피너 표시
                        }
                    }

                    /**
                     * [9-4] AJAX 요청 전송
                     * 
                     * 약관 동의 정보를 서버로 전송합니다.
                     * - method: POST
                     * - headers: AJAX 요청임을 표시하고 CSRF 토큰 포함
                     * - credentials: 쿠키를 포함하여 세션 유지
                     * - body: FormData (약관 ID 배열 포함)
                     */
                    fetch(this.action, {
                            method: 'POST',
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest', // AJAX 요청임을 표시
                                'Accept': 'application/json', // JSON 응답 요청
                                'X-CSRF-TOKEN': csrfToken // CSRF 토큰
                            },
                            credentials: 'same-origin', // 쿠키 포함 (세션 유지)
                            body: formData // 약관 동의 데이터
                        })
                        .then(response => {
                            /**
                             * [9-5] 응답 형식 검증
                             * 
                             * 서버 응답이 JSON 형식인지 확인합니다.
                             * JSON이 아니면 일반 리다이렉트로 처리하거나 에러를 발생시킵니다.
                             */
                            const contentType = response.headers.get('content-type');
                            if (!contentType || !contentType.includes('application/json')) {
                                // JSON이 아니면 일반 리다이렉트로 처리
                                if (response.redirected) {
                                    window.location.href = response.url;
                                    return;
                                }
                                throw new Error('서버 응답 형식이 올바르지 않습니다.');
                            }
                            
                            // HTTP 상태 코드가 200-299가 아니면 에러 처리
                            if (!response.ok) {
                                return response.json().then(data => {
                                    throw new Error(data.message || '서버 오류가 발생했습니다.');
                                });
                            }
                            return response.json();
                        })
                        .then(data => {
                            /**
                             * [9-6] 성공 응답 처리
                             * 
                             * 서버에서 성공 응답을 받으면:
                             * 1. 버튼 상태를 원래대로 복원 (뒤로가기 대응)
                             * 2. 100ms 지연 후 회원가입 폼으로 리다이렉트
                             * 
                             * 버튼 상태를 먼저 복원하는 이유:
                             * - 리다이렉트가 완료되기 전에 사용자가 뒤로가기를 누를 수 있음
                             * - 뒤로가기로 돌아왔을 때 버튼이 "처리 중..." 상태로 남아있는 것을 방지
                             */
                            if (data.success) {
                                // 버튼 상태 복원
                                if (submitBtn && btnText) {
                                    submitBtn.disabled = false;
                                    btnText.style.display = 'inline';
                                    btnText.textContent = '다음 단계';
                                    if (btnSpinner) {
                                        btnSpinner.classList.add('d-none');
                                    }
                                }
                                
                                // 약간의 지연 후 리다이렉트 (버튼 상태 복원이 보이도록)
                                setTimeout(() => {
                                    window.location.href = data.redirect || '{{ route("signup.index") }}';
                                }, 100);
                            } else {
                                /**
                                 * [9-7] 실패 응답 처리
                                 * 
                                 * 서버에서 실패 응답을 받으면 에러 메시지를 표시하고
                                 * 버튼 상태를 복원하여 사용자가 다시 시도할 수 있도록 합니다.
                                 */
                                alert(data.message || '오류가 발생했습니다.');
                                // 버튼 상태 복원
                                if (submitBtn && btnText) {
                                    submitBtn.disabled = false;
                                    btnText.style.display = 'inline';
                                    btnText.textContent = '다음 단계';
                                    if (btnSpinner) {
                                        btnSpinner.classList.add('d-none');
                                    }
                                }
                            }
                        })
                        .catch(error => {
                            /**
                             * [9-8] 네트워크 오류 또는 예외 처리
                             * 
                             * 네트워크 오류, 타임아웃, 또는 기타 예외가 발생하면:
                             * 1. 콘솔에 에러 로그 출력 (디버깅용)
                             * 2. 사용자에게 에러 메시지 표시
                             * 3. 버튼 상태를 복원하여 다시 시도할 수 있도록 함
                             */
                            console.error('Error:', error);
                            alert(error.message || '서버 통신 중 오류가 발생했습니다.');
                            // 버튼 상태 복원
                            if (submitBtn && btnText) {
                                submitBtn.disabled = false;
                                btnText.style.display = 'inline';
                                btnText.textContent = '다음 단계';
                                if (btnSpinner) {
                                    btnSpinner.classList.add('d-none');
                                }
                            }
                        });
                });
            });
        </script>
    @endpush

@endsection
