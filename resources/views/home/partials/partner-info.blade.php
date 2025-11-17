@php
    // 현재 로그인한 사용자의 파트너 정보를 조회
    $user = auth()->user();
    $partnerUser = null;
    $partnerApplication = null;

    if ($user && $user->uuid) {
        // 파트너 사용자 정보 조회
        if (class_exists('\Jiny\Partner\Models\PartnerUser')) {
            $partnerUser = \Jiny\Partner\Models\PartnerUser::where('user_uuid', $user->uuid)->first();
        }

        // 파트너 신청 정보 조회 (가장 최근)
        if (class_exists('\Jiny\Partner\Models\PartnerApplication')) {
            $partnerApplication = \Jiny\Partner\Models\PartnerApplication::where('user_uuid', $user->uuid)
                ->latest()
                ->first();
        }
    }
@endphp

<!-- 파트너 정보 섹션 -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-briefcase me-2"></i>파트너 정보
                </h5>
                @if(!$partnerUser && !$partnerApplication)
                    <a href="{{ route('home.partner.intro') }}" class="btn btn-primary btn-sm">
                        <i class="bi bi-plus me-1"></i>파트너 신청
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($partnerUser)
                    <!-- 등록된 파트너인 경우 -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-check2 text-white fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-success">파트너 등록 완료</h6>
                                    <small class="text-muted">{{ $partnerUser->partner_joined_at ? $partnerUser->partner_joined_at->format('Y년 m월 d일 등록') : '등록일 정보 없음' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('home.partner.index') }}" class="btn btn-outline-primary btn-sm">
                                        <i class="bi bi-house me-1"></i>파트너 대시보드
                                    </a>
                                    <a href="{{ route('home.partner.sales.index') }}" class="btn btn-outline-success btn-sm">
                                        <i class="bi bi-graph-up me-1"></i>매출 관리
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3">

                    <!-- 파트너 상세 정보 -->
                    <div class="row g-3">
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-primary mb-0">{{ $partnerUser->partner_tier->name ?? 'Basic' }}</div>
                                <small class="text-muted">파트너 등급</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-info mb-0">{{ $partnerUser->total_completed_jobs ?? 0 }}</div>
                                <small class="text-muted">완료한 작업</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-warning mb-0">{{ number_format($partnerUser->average_rating ?? 0, 1) }}</div>
                                <small class="text-muted">평균 평점</small>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="text-center">
                                <div class="h4 text-success mb-0">{{ $partnerUser->satisfaction_rate ?? 0 }}%</div>
                                <small class="text-muted">만족도</small>
                            </div>
                        </div>
                    </div>

                @elseif($partnerApplication)
                    <!-- 신청한 상태인 경우 -->
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                @php
                                    $statusConfig = [
                                        'draft' => ['icon' => 'pencil', 'color' => 'secondary', 'text' => '임시저장'],
                                        'submitted' => ['icon' => 'clock', 'color' => 'warning', 'text' => '검토 대기'],
                                        'reviewing' => ['icon' => 'eye', 'color' => 'info', 'text' => '검토 중'],
                                        'interview' => ['icon' => 'camera-video', 'color' => 'primary', 'text' => '면접 예정'],
                                        'approved' => ['icon' => 'check2', 'color' => 'success', 'text' => '승인 완료'],
                                        'rejected' => ['icon' => 'x', 'color' => 'danger', 'text' => '반려됨'],
                                        'reapplied' => ['icon' => 'arrow-clockwise', 'color' => 'warning', 'text' => '재신청']
                                    ];
                                    $status = $statusConfig[$partnerApplication->application_status] ?? $statusConfig['submitted'];
                                @endphp
                                <div class="bg-{{ $status['color'] }} rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 50px; height: 50px;">
                                    <i class="bi bi-{{ $status['icon'] }} text-white fs-5"></i>
                                </div>
                                <div>
                                    <h6 class="mb-0 text-{{ $status['color'] }}">{{ $status['text'] }}</h6>
                                    <small class="text-muted">신청일: {{ $partnerApplication->created_at ? $partnerApplication->created_at->format('Y-m-d') : '정보 없음' }}</small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-md-end">
                                <div class="btn-group" role="group">
                                    <a href="{{ route('home.partner.regist.status', $partnerApplication->id) }}" class="btn btn-outline-info btn-sm">
                                        <i class="bi bi-info-circle me-1"></i>신청 상태 확인
                                    </a>
                                    @if(in_array($partnerApplication->application_status, ['draft', 'rejected']))
                                        <a href="{{ route('home.partner.regist.edit', $partnerApplication->id) }}" class="btn btn-outline-warning btn-sm">
                                            <i class="bi bi-pencil me-1"></i>수정하기
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($partnerApplication->application_status === 'rejected' && $partnerApplication->rejection_reason)
                        <div class="alert alert-danger mt-3">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>반려 사유:</strong> {{ $partnerApplication->rejection_reason }}
                        </div>
                    @endif

                    @if($partnerApplication->application_status === 'interview' && $partnerApplication->interview_date)
                        <div class="alert alert-info mt-3">
                            <i class="bi bi-calendar-event me-2"></i>
                            <strong>면접 일정:</strong> {{ $partnerApplication->interview_date->format('Y년 m월 d일 H:i') }}
                        </div>
                    @endif

                @else
                    <!-- 파트너 신청하지 않은 경우 -->
                    <div class="text-center py-4">
                        <div class="mb-3">
                            <i class="bi bi-briefcase text-muted" style="font-size: 3rem;"></i>
                        </div>
                        <h6 class="text-muted mb-2">아직 파트너로 등록되지 않았습니다</h6>
                        <p class="text-muted mb-4">
                            파트너로 등록하시면 다양한 프로젝트에 참여하고<br>
                            수익을 창출할 수 있습니다.
                        </p>
                        <div>
                            <a href="{{ route('home.partner.intro') }}" class="btn btn-primary me-2">
                                <i class="bi bi-info-circle me-1"></i>파트너 프로그램 알아보기
                            </a>
                            <a href="{{ route('home.partner.regist.create') }}" class="btn btn-outline-primary">
                                <i class="bi bi-plus me-1"></i>지금 신청하기
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>