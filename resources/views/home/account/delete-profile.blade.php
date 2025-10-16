@extends('jiny-auth::layouts.home')

@section('title', '회원 탈퇴')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">회원 탈퇴</h1>
            </div>
        </div>

        @if(session('success'))
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
        @endif

        @if($errors->any())
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
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
                        @if($existingRequest)
                            @if($existingRequest->status === 'approved')
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
                                                <td>{{ $existingRequest->approved_at ? $existingRequest->approved_at->format('Y-m-d H:i') : '-' }}</td>
                                            </tr>
                                            <tr>
                                                <th>상태</th>
                                                <td><span class="badge bg-success">승인됨</span></td>
                                            </tr>
                                            @if($existingRequest->reason)
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
                                                    @if($existingRequest->status === 'pending')
                                                        @if($config['require_approval'])
                                                            <span class="badge bg-warning">관리자 승인 대기 중</span>
                                                        @else
                                                            <span class="badge bg-info">처리 대기 중</span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($existingRequest->reason)
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
                                        @if($existingRequest->status === 'pending')
                                        <form action="{{ route('account.deletion.cancel') }}" method="POST" style="display: inline;">
                                            @csrf
                                            <button
                                                type="submit"
                                                class="btn btn-outline-danger"
                                                onclick="return confirm('탈퇴 신청을 취소하시겠습니까?')"
                                            >
                                                <i class="fe fe-x me-2"></i>신청 취소
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        @else
                            <span class="text-danger h4">경고</span>
                            <p class="mt-3">계정을 닫으면 다음과 같은 결과가 발생합니다:</p>
                            <ul>
                                <li>모든 코스 구독이 취소됩니다</li>
                                <li>저장된 모든 데이터에 접근할 수 없게 됩니다</li>
                                <li>계정 복구가 불가능합니다</li>
                            </ul>

                            <form action="{{ route('account.deletion.store') }}" method="POST" id="deletionForm">
                                @csrf

                                <!-- 탈퇴 사유 -->
                                <div class="mb-4">
                                    <label for="reason" class="form-label">탈퇴 사유 (선택사항)</label>
                                    <textarea 
                                        class="form-control" 
                                        id="reason" 
                                        name="reason" 
                                        rows="4"
                                        placeholder="탈퇴하시는 이유를 알려주시면 서비스 개선에 큰 도움이 됩니다."
                                    >{{ old('reason') }}</textarea>
                                </div>

                                @if($config['require_password_confirm'])
                                <!-- 비밀번호 확인 -->
                                <div class="mb-4">
                                    <label for="password" class="form-label">비밀번호 확인 <span class="text-danger">*</span></label>
                                    <input 
                                        type="password" 
                                        class="form-control" 
                                        id="password" 
                                        name="password" 
                                        required
                                        placeholder="현재 비밀번호를 입력하세요"
                                    >
                                </div>
                                @endif

                                <!-- 확인 체크박스 -->
                                <div class="mb-4">
                                    <div class="form-check">
                                        <input 
                                            class="form-check-input" 
                                            type="checkbox" 
                                            id="confirm" 
                                            name="confirm"
                                            required
                                        >
                                        <label class="form-check-label" for="confirm">
                                            위 내용을 확인했으며, 계정 삭제에 동의합니다.
                                        </label>
                                    </div>
                                </div>

                                <button 
                                    type="submit" 
                                    class="btn btn-danger"
                                    onclick="return confirm('정말로 계정을 삭제하시겠습니까? 이 작업은 되돌릴 수 없습니다.')"
                                >
                                    계정 삭제하기
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
