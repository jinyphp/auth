@extends($layout ?? 'jiny-auth::layouts.home')

@section('title', '탈퇴 신청 완료')

@section('content')
    <div class="container mb-4">
        <section class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">탈퇴 신청 완료</h1>
            </div>
        </section>

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

        <div class="row">
            <div class="col-12">
                <!-- Card -->
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <h3 class="mb-0">탈퇴 신청 정보</h3>
                    </div>

                    <!-- Card body -->
                    <div class="card-body p-4">
                        @if($unregistRequest)
                            <div class="mb-4">
                                <h5>신청 정보</h5>
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <th style="width: 200px;">신청일</th>
                                            <td>{{ $unregistRequest->created_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        <tr>
                                            <th>상태</th>
                                            <td>
                                                @if($unregistRequest->status === 'pending')
                                                    <span class="badge bg-warning">승인 대기 중</span>
                                                @elseif($unregistRequest->status === 'approved')
                                                    <span class="badge bg-success">승인됨</span>
                                                @elseif($unregistRequest->status === 'rejected')
                                                    <span class="badge bg-danger">거부됨</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @if($unregistRequest->reason)
                                        <tr>
                                            <th>탈퇴 사유</th>
                                            <td>{{ $unregistRequest->reason }}</td>
                                        </tr>
                                        @endif
                                        @if($unregistRequest->approved_at)
                                        <tr>
                                            <th>승인일</th>
                                            <td>{{ $unregistRequest->approved_at->format('Y-m-d H:i:s') }}</td>
                                        </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>

                            @if($unregistRequest->status === 'pending')
                                <div class="alert alert-info">
                                    <h5>안내</h5>
                                    <p class="mb-0">
                                        관리자가 탈퇴 신청을 검토 중입니다. 승인이 완료되면 계정이 삭제됩니다.
                                    </p>
                                </div>
                            @elseif($unregistRequest->status === 'approved')
                                <div class="alert alert-success">
                                    <h5>탈퇴 승인 완료</h5>
                                    <p class="mb-0">
                                        탈퇴 신청이 승인되었습니다. 곧 계정이 삭제됩니다.
                                    </p>
                                </div>
                            @elseif($unregistRequest->status === 'rejected')
                                <div class="alert alert-danger">
                                    <h5>탈퇴 신청 거부</h5>
                                    <p class="mb-0">
                                        탈퇴 신청이 거부되었습니다. 자세한 내용은 고객센터로 문의해주세요.
                                    </p>
                                </div>
                            @endif
                        @else
                            <div class="alert alert-warning">
                                <p class="mb-0">탈퇴 신청 내역이 없습니다.</p>
                            </div>
                        @endif

                        <div class="mt-4">
                            <a href="{{ route('home.dashboard') }}" class="btn btn-primary">대시보드로 돌아가기</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 회원 탈퇴 신청 이력 --}}
        @if (isset($unregistHistory) && $unregistHistory->count() > 0)
            <div class="row mt-4">
                <div class="col-12">
                    <!-- Card -->
                    <section class="card">
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
                    </section>
                </div>
            </div>
        @endif
    </div>
@endsection
