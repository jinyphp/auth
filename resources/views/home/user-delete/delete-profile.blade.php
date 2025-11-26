@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '회원 탈퇴')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">회원 탈퇴</h1>
            </div>
        </div>

        {{-- 알림 메시지 영역 --}}
        <div id="alert-area"></div>

        @if (session('success'))
            <div class="row mb-3">
                <div class="col-12">
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <h3 class="mb-0">계정 삭제</h3>
                        <p class="mb-0">계정을 영구적으로 삭제하거나 닫습니다.</p>
                    </div>

                    <!-- Card body -->
                    <div class="card-body p-4">
                        @if ($existingRequest)
                            {{--
                                Step 1: 기존 탈퇴 신청 내역이 있는 경우 상태 표시
                                - 승인됨(approved): 탈퇴 완료 안내
                                - 대기중(pending): 진행 상황 및 취소 버튼 표시
                            --}}
                            @if ($existingRequest->status === 'approved')
                                <div class="alert alert-success">
                                    <h5><i class="fe fe-check-circle me-2"></i>탈퇴 승인 완료</h5>
                                    <hr>
                                    <table class="table table-sm table-borderless mb-3">
                                        <tbody>
                                            <tr>
                                                <th style="width: 150px;">신청일</th>
                                                <td>{{ $existingRequest->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>승인일</th>
                                                <td>{{ $existingRequest->approved_at ? $existingRequest->approved_at->format('Y-m-d H:i') : '-' }}
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>상태</th>
                                                <td><span class="badge bg-success">승인됨</span></td>
                                            </tr>
                                            @if ($existingRequest->reason)
                                                <tr>
                                                    <th>탈퇴 사유</th>
                                                    <td>{{ $existingRequest->reason }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>
                                    <p class="mb-0">
                                        <strong>탈퇴 승인이 완료되었습니다.</strong><br>
                                        계정이 곧 삭제되며, 더 이상 로그인할 수 없게 됩니다.<br>
                                        그동안 이용해 주셔서 감사합니다.
                                    </p>
                                </div>
                            @else
                                <div class="alert alert-info">
                                    <h5><i class="fe fe-info me-2"></i>탈퇴 신청 진행 중</h5>
                                    <hr>
                                    <table class="table table-sm table-borderless mb-3">
                                        <tbody>
                                            <tr>
                                                <th style="width: 150px;">신청일</th>
                                                <td>{{ $existingRequest->created_at->format('Y-m-d H:i') }}</td>
                                            </tr>
                                            <tr>
                                                <th>상태</th>
                                                <td>
                                                    @if ($existingRequest->status === 'pending')
                                                        @if ($config['require_approval'])
                                                            <span class="badge bg-warning">관리자 승인 대기 중</span>
                                                        @else
                                                            <span class="badge bg-info">처리 대기 중</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                            @if ($existingRequest->reason)
                                                <tr>
                                                    <th>탈퇴 사유</th>
                                                    <td>{{ $existingRequest->reason }}</td>
                                                </tr>
                                            @endif
                                        </tbody>
                                    </table>

                                    <div class="d-flex gap-2">
                                        <a href="{{ route('account.deletion.requested') }}" class="btn btn-primary">
                                            <i class="fe fe-eye me-2"></i>상세 내역 보기
                                        </a>
                                        @if ($existingRequest->status === 'pending')
                                            {{-- Step 2: 탈퇴 신청 취소 버튼 (AJAX 처리) --}}
                                            <button type="button" class="btn btn-outline-danger"
                                                onclick="cancelDeletionRequest()" id="btn-cancel">
                                                <i class="fe fe-x me-2"></i>신청 취소
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            {{--
                                Step 3: 탈퇴 신청 폼
                                - 탈퇴 사유 입력
                                - 비밀번호 확인 (설정에 따라)
                                - 동의 체크박스
                            --}}
                            <span class="text-danger h4">경고</span>
                            <p class="mt-3">계정을 닫으면 다음과 같은 결과가 발생합니다:</p>
                            <ul>
                                <li>모든 코스 구독이 취소됩니다</li>
                                <li>저장된 모든 데이터에 접근할 수 없게 됩니다</li>
                                <li>계정 복구가 불가능합니다</li>
                            </ul>

                            <form id="deletionForm" onsubmit="submitDeletionRequest(event)">
                                @csrf

                                <!-- 탈퇴 사유 -->
                                <div class="mb-4">
                                    <label for="reason" class="form-label">탈퇴 사유 (선택사항)</label>
                                    <textarea class="form-control" id="reason" name="reason" rows="4"
                                        placeholder="탈퇴하시는 이유를 알려주시면 서비스 개선에 큰 도움이 됩니다.">{{ old('reason') }}</textarea>
                                </div>

                                @if ($config['require_password_confirm'])
                                    <!-- 비밀번호 확인 -->
                                    <div class="mb-4">
                                        <label for="password" class="form-label">비밀번호 확인 <span
                                                class="text-danger">*</span></label>
                                        <input type="password" class="form-control" id="password" name="password" required
                                            placeholder="현재 비밀번호를 입력하세요">
                                        <div class="invalid-feedback" id="password-error"></div>
                                    </div>
                                @endif

                                <!-- 확인 체크박스 -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="confirm" name="confirm"
                                            required>
                                        <label class="form-check-label" for="confirm">
                                            위 내용을 확인했으며, 계정 삭제에 동의합니다.
                                        </label>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-danger" id="btn-submit">
                                    계정 삭제하기
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 회원 탈퇴 신청 이력 --}}
        @if (isset($unregistHistory) && $unregistHistory->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <!-- Card -->
                    <div class="card">
                        <!-- Card header -->
                        <div class="card-header">
                            <h3 class="mb-0">회원 탈퇴 신청 이력</h3>
                            <p class="mb-0 text-muted small">최근 10개의 탈퇴 신청 기록을 표시합니다.</p>
                        </div>

                        <!-- Card body -->
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th style="width: 15%">신청일</th>
                                            <th style="width: 15%">상태</th>
                                            <th style="width: 15%">승인일</th>
                                            <th style="width: 15%">거부일</th>
                                            <th style="width: 40%">탈퇴 사유</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($unregistHistory as $history)
                                            <tr>
                                                <td>
                                                    @if ($history['created_at'])
                                                        {{ $history['created_at']->format('Y-m-d H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @php
                                                        $statusBadges = [
                                                            'pending' => 'bg-warning',
                                                            'approved' => 'bg-success',
                                                            'rejected' => 'bg-danger',
                                                            'deleted' => 'bg-info',
                                                        ];
                                                        $badgeClass = $statusBadges[$history['status']] ?? 'bg-secondary';
                                                    @endphp
                                                    <span class="badge {{ $badgeClass }}">{{ $history['status_label'] }}</span>
                                                </td>
                                                <td>
                                                    @if ($history['approved_at'])
                                                        {{ $history['approved_at']->format('Y-m-d H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($history['rejected_at'])
                                                        {{ $history['rejected_at']->format('Y-m-d H:i') }}
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    <small class="text-muted">
                                                        {{ $history['reason'] ? Str::limit($history['reason'], 50) : '-' }}
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
        <script>
            /**
             * Step 4: 탈퇴 신청 제출 처리 (AJAX)
             */
            async function submitDeletionRequest(e) {
                e.preventDefault();

                if (!confirm('정말로 계정을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')) {
                    return;
                }

                const form = document.getElementById('deletionForm');
                const btn = document.getElementById('btn-submit');
                const originalBtnText = btn.innerHTML;

                // 버튼 로딩 상태로 변경
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>처리 중...';

                // 에러 메시지 초기화
                document.getElementById('password')?.classList.remove('is-invalid');
                document.getElementById('alert-area').innerHTML = '';

                try {
                    const formData = new FormData(form);

                    const response = await fetch('{{ route('account.deletion.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await response.json();

                    if (response.ok) {
                        // 성공 시 메시지 표시 후 리다이렉트
                        showAlert('success', data.message);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        // 유효성 검사 실패 등 에러 처리
                        if (data.errors) {
                            if (data.errors.password) {
                                const pwdInput = document.getElementById('password');
                                const pwdError = document.getElementById('password-error');
                                if (pwdInput && pwdError) {
                                    pwdInput.classList.add('is-invalid');
                                    pwdError.textContent = data.errors.password[0];
                                }
                            }
                            if (data.errors.error) {
                                showAlert('danger', data.errors.error);
                            }
                        } else {
                            showAlert('danger', data.message || '오류가 발생했습니다.');
                        }

                        // 버튼 상태 복구
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('danger', '서버 통신 중 오류가 발생했습니다.');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                }
            }

            /**
             * Step 5: 탈퇴 신청 취소 처리 (AJAX)
             */
            async function cancelDeletionRequest() {
                if (!confirm('탈퇴 신청을 취소하시겠습니까?')) {
                    return;
                }

                const btn = document.getElementById('btn-cancel');
                const originalBtnText = btn.innerHTML;

                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>취소 중...';

                try {
                    const response = await fetch('{{ route('account.deletion.cancel') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        },
                        body: new FormData() // Empty body for POST
                    });

                    const data = await response.json();

                    if (response.ok) {
                        showAlert('success', data.message);
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                    } else {
                        showAlert('danger', data.message || '취소 처리에 실패했습니다.');
                        btn.disabled = false;
                        btn.innerHTML = originalBtnText;
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showAlert('danger', '서버 통신 중 오류가 발생했습니다.');
                    btn.disabled = false;
                    btn.innerHTML = originalBtnText;
                }
            }

            /**
             * Helper: 알림 메시지 표시
             */
            function showAlert(type, message) {
                const alertArea = document.getElementById('alert-area');
                alertArea.innerHTML = `
                <div class="row mb-3">
                    <div class="col-12">
                        <div class="alert alert-${type} alert-dismissible fade show" role="alert">
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    </div>
                </div>
            `;
            }
        </script>
    @endpush
@endsection
