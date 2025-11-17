@php
    // 파트너 패키지가 설치되어 있는지 확인
    if (!class_exists('\Jiny\Partner\Models\PartnerUser') && !class_exists('\Jiny\Partner\Models\PartnerApplication')) {
        return; // 파트너 패키지가 없으면 아무것도 표시하지 않음
    }

    // 현재 로그인한 사용자의 파트너 정보를 조회
    $user = auth()->user();
    $partnerUser = null;
    $partnerApplication = null;
    $salesStats = null;
    $commissionStats = null;
    $networkInfo = null;
    $recentSales = collect();
    $subPartners = collect();

    if ($user && $user->uuid) {
        // 파트너 사용자 정보 조회
        try {
            $partnerUser = \Jiny\Partner\Models\PartnerUser::with(['partnerType', 'partnerTier'])
                ->where('user_uuid', $user->uuid)
                ->first();
        } catch (\Exception $e) {
            // 테이블이 없거나 오류 발생시 무시
        }

        // 파트너 신청 정보 조회 (가장 최근)
        try {
            $partnerApplication = \Jiny\Partner\Models\PartnerApplication::where('user_uuid', $user->uuid)
                ->latest()
                ->first();
        } catch (\Exception $e) {
            // 테이블이 없거나 오류 발생시 무시
        }

        // 파트너인 경우 추가 데이터 조회
        if ($partnerUser) {
            // 매출 통계 계산
            try {
                $currentMonth = now()->format('Y-m');
                $currentYear = now()->format('Y');

                $salesStats = [
                    'monthly_sales' => $partnerUser->monthly_sales ?? 0,
                    'total_sales' => $partnerUser->total_sales ?? 0,
                    'current_month_sales' => class_exists('\Jiny\Partner\Models\PartnerSales') ?
                        \Jiny\Partner\Models\PartnerSales::where('partner_id', $partnerUser->id)
                            ->where('status', 'confirmed')
                            ->whereRaw("strftime('%Y-%m', sales_date) = ?", [$currentMonth])
                            ->sum('amount') : 0,
                    'current_year_sales' => class_exists('\Jiny\Partner\Models\PartnerSales') ?
                        \Jiny\Partner\Models\PartnerSales::where('partner_id', $partnerUser->id)
                            ->where('status', 'confirmed')
                            ->whereRaw("strftime('%Y', sales_date) = ?", [$currentYear])
                            ->sum('amount') : 0,
                    'total_sales_count' => class_exists('\Jiny\Partner\Models\PartnerSales') ?
                        \Jiny\Partner\Models\PartnerSales::where('partner_id', $partnerUser->id)
                            ->where('status', 'confirmed')
                            ->count() : 0,
                ];
            } catch (\Exception $e) {
                $salesStats = ['monthly_sales' => 0, 'total_sales' => 0, 'current_month_sales' => 0, 'current_year_sales' => 0, 'total_sales_count' => 0];
            }

            // 커미션 통계 계산
            try {
                $commissionStats = [
                    'total_commission' => class_exists('\Jiny\Partner\Models\PartnerCommission') ?
                        \Jiny\Partner\Models\PartnerCommission::where('partner_id', $partnerUser->id)
                            ->where('status', 'paid')
                            ->sum('amount') : 0,
                    'pending_commission' => class_exists('\Jiny\Partner\Models\PartnerCommission') ?
                        \Jiny\Partner\Models\PartnerCommission::where('partner_id', $partnerUser->id)
                            ->where('status', 'pending')
                            ->sum('amount') : 0,
                    'this_month_commission' => class_exists('\Jiny\Partner\Models\PartnerCommission') ?
                        \Jiny\Partner\Models\PartnerCommission::where('partner_id', $partnerUser->id)
                            ->where('status', 'paid')
                            ->whereRaw("strftime('%Y-%m', created_at) = ?", [now()->format('Y-m')])
                            ->sum('amount') : 0,
                    'commission_count' => class_exists('\Jiny\Partner\Models\PartnerCommission') ?
                        \Jiny\Partner\Models\PartnerCommission::where('partner_id', $partnerUser->id)
                            ->count() : 0,
                ];
            } catch (\Exception $e) {
                $commissionStats = ['total_commission' => 0, 'pending_commission' => 0, 'this_month_commission' => 0, 'commission_count' => 0];
            }

            // 네트워크 정보 조회
            try {
                $parentPartner = null;
                if (class_exists('\Jiny\Partner\Models\PartnerNetworkRelationship')) {
                    $parentRelationship = \Jiny\Partner\Models\PartnerNetworkRelationship::where('child_id', $partnerUser->id)->first();
                    if ($parentRelationship) {
                        $parentPartner = \Jiny\Partner\Models\PartnerUser::with(['partnerType', 'partnerTier'])
                            ->find($parentRelationship->parent_id);
                    }
                    $childrenCount = \Jiny\Partner\Models\PartnerNetworkRelationship::where('parent_id', $partnerUser->id)->count();
                } else {
                    $childrenCount = 0;
                }

                $networkInfo = [
                    'parent_partner' => $parentPartner,
                    'children_count' => $childrenCount,
                    'level' => $partnerUser->level ?? 0,
                ];
            } catch (\Exception $e) {
                $networkInfo = ['parent_partner' => null, 'children_count' => 0, 'level' => 0];
            }

            // 최근 매출 기록 조회
            try {
                if (class_exists('\Jiny\Partner\Models\PartnerSales')) {
                    $recentSales = \Jiny\Partner\Models\PartnerSales::where('partner_id', $partnerUser->id)
                        ->orderBy('sales_date', 'desc')
                        ->limit(5)
                        ->get();
                }
            } catch (\Exception $e) {
                $recentSales = collect();
            }

            // 하위 파트너 정보 조회
            try {
                if (class_exists('\Jiny\Partner\Models\PartnerNetworkRelationship')) {
                    $childrenIds = \Jiny\Partner\Models\PartnerNetworkRelationship::where('parent_id', $partnerUser->id)
                        ->where('is_active', true)
                        ->pluck('child_id');

                    $subPartners = \Jiny\Partner\Models\PartnerUser::whereIn('id', $childrenIds)
                        ->with(['partnerType', 'partnerTier'])
                        ->orderBy('created_at', 'desc')
                        ->limit(5)
                        ->get();
                }
            } catch (\Exception $e) {
                $subPartners = collect();
            }
        }
    }
@endphp

<!-- 파트너 정보 섹션 -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" class="bi bi-briefcase me-2" viewBox="0 0 16 16">
                        <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.864 7.85a.5.5 0 0 1-.258 0L1.5 6.116V4.5a.5.5 0 0 1 .5-.5z"/>
                    </svg>
                    파트너 정보
                </h4>
                @if(!$partnerUser && !$partnerApplication)
                    <a href="{{ route('home.partner.intro') }}" class="btn btn-primary btn-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus me-1" viewBox="0 0 16 16">
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        파트너 신청
                    </a>
                @endif
            </div>
            <div class="card-body">
                @if($partnerUser)
                    <!-- 등록된 파트너인 경우 -->
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                <div class="bg-success rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 60px; height: 60px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-check2" viewBox="0 0 16 16">
                                        <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-success">파트너 등록 완료</h5>
                                    <small class="text-muted">
                                        {{ $partnerUser->partner_joined_at ? $partnerUser->partner_joined_at->format('Y년 m월 d일 등록') : '등록일 정보 없음' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="{{ route('home.partner.index') }}" class="btn btn-outline-primary btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-house me-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M2 13.5V7h1v6.5a.5.5 0 0 0 .5.5h9a.5.5 0 0 0 .5-.5V7h1v6.5a1.5 1.5 0 0 1-1.5 1.5h-9A1.5 1.5 0 0 1 2 13.5zM4.5 14h7V7h-7v7z"/>
                                    </svg>
                                    파트너 대시보드
                                </a>
                                <a href="{{ route('home.partner.sales.index') }}" class="btn btn-outline-success btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up me-1" viewBox="0 0 16 16">
                                        <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/>
                                    </svg>
                                    매출 관리
                                </a>
                            </div>
                        </div>
                    </div>

                    <hr>

                    <!-- 파트너 통계 카드들 -->
                    <div class="row g-3 mb-4">
                        <!-- 매출 정보 -->
                        <div class="col-lg-6 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="text-success mb-0">{{ number_format($salesStats['current_month_sales'] ?? 0) }}원</h5>
                                            <p class="mb-1 small">이번 달 매출</p>
                                            <small class="text-muted">총 매출: {{ number_format($salesStats['total_sales'] ?? 0) }}원</small>
                                        </div>
                                        <div class="icon-shape bg-success text-white rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zM8.5 4.5a.5.5 0 0 0-1 0v5.793L5.354 8.146a.5.5 0 1 0-.708.708l3 3a.5.5 0 0 0 .708 0l3-3a.5.5 0 0 0-.708-.708L8.5 10.293V4.5z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 커미션 정보 -->
                        <div class="col-lg-6 col-md-6">
                            <div class="card border-0 bg-light">
                                <div class="card-body">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div>
                                            <h5 class="text-warning mb-0">{{ number_format($commissionStats['this_month_commission'] ?? 0) }}원</h5>
                                            <p class="mb-1 small">이번 달 커미션</p>
                                            <small class="text-muted">대기중: {{ number_format($commissionStats['pending_commission'] ?? 0) }}원</small>
                                        </div>
                                        <div class="icon-shape bg-warning text-white rounded-circle" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 16 16">
                                                <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                            </svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- 네트워크 및 기본 정보 -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-3 col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="h5 text-primary mb-1">{{ $partnerUser->partnerTier->tier_name ?? ($partnerUser->partner_tier->name ?? 'Basic') }}</div>
                                <div class="small text-muted">파트너 등급</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="h5 text-info mb-1">{{ $networkInfo['children_count'] ?? 0 }}</div>
                                <div class="small text-muted">하위 파트너</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="h5 text-warning mb-1">{{ $salesStats['total_sales_count'] ?? 0 }}</div>
                                <div class="small text-muted">총 거래 건수</div>
                            </div>
                        </div>
                        <div class="col-md-3 col-6">
                            <div class="text-center p-3 border rounded">
                                <div class="h5 text-success mb-1">레벨 {{ $networkInfo['level'] ?? 0 }}</div>
                                <div class="small text-muted">네트워크 레벨</div>
                            </div>
                        </div>
                    </div>

                    @if($networkInfo && $networkInfo['parent_partner'])
                    <!-- 상위 파트너 정보 -->
                    <div class="alert alert-info">
                        <div class="d-flex align-items-center">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-person-up me-2" viewBox="0 0 16 16">
                                <path d="M12.5 16a3.5 3.5 0 1 0 0-7 3.5 3.5 0 0 0 0 7Zm.5-5v1h1a.5.5 0 0 1 0 1h-1v1a.5.5 0 0 1-1 0v-1h-1a.5.5 0 0 1 0-1h1v-1a.5.5 0 0 1 1 0Zm-2-6a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Z"/>
                                <path d="M8.256 14a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .507.009.74.025.226-.341.496-.65.804-.918C9.077 9.038 8.564 9 8 9c-5 0-6 3-6 4s1 1 1 1h5.256Z"/>
                            </svg>
                            <div>
                                <strong>상위 파트너:</strong> {{ $networkInfo['parent_partner']->name }}
                                <small class="text-muted">({{ $networkInfo['parent_partner']->partnerTier->tier_name ?? '등급 미지정' }})</small>
                            </div>
                        </div>
                    </div>
                    @endif

                    @if($recentSales && $recentSales->count() > 0)
                    <!-- 최근 매출 기록 -->
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-graph-up me-2" viewBox="0 0 16 16">
                                <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/>
                            </svg>
                            최근 매출 기록
                        </h6>
                        @foreach($recentSales->take(3) as $sale)
                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                            <div>
                                <div class="fw-medium">{{ $sale->title ?? '매출' }}</div>
                                <small class="text-muted">{{ $sale->sales_date ? $sale->sales_date->format('Y-m-d') : $sale->created_at->format('Y-m-d') }}</small>
                            </div>
                            <div class="text-end">
                                <div class="fw-bold text-success">{{ number_format($sale->amount) }}원</div>
                                <span class="badge bg-{{ $sale->status === 'confirmed' ? 'success' : ($sale->status === 'pending' ? 'warning' : 'secondary') }} small">
                                    {{ $sale->status === 'confirmed' ? '확정' : ($sale->status === 'pending' ? '대기' : '기타') }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($subPartners && $subPartners->count() > 0)
                    <!-- 하위 파트너 목록 -->
                    <div class="mb-4">
                        <h6 class="mb-3">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-people me-2" viewBox="0 0 16 16">
                                <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1h8Zm-7.978-1A.261.261 0 0 1 7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002A.274.274 0 0 1 15 13H7.022ZM11 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4Zm3-2a3 3 0 1 1-6 0 3 3 0 0 1 6 0ZM6.936 9.28a5.88 5.88 0 0 0-1.23-.247A7.35 7.35 0 0 0 5 9c-4 0-5 3-5 4 0 .667.333 1 1 1h4.216A2.238 2.238 0 0 1 5 13c0-1.01.377-2.042 1.09-2.904.243-.294.526-.569.846-.816ZM4.92 10A5.493 5.493 0 0 0 4 13H1c0-.26.164-1.03.76-1.724.545-.636 1.492-1.256 3.16-1.275ZM1.5 5.5a3 3 0 1 1 6 0 3 3 0 0 1-6 0Zm3-2a2 2 0 1 0 0 4 2 2 0 0 0 0-4Z"/>
                            </svg>
                            하위 파트너 ({{ $networkInfo['children_count'] }}명)
                        </h6>
                        @foreach($subPartners->take(3) as $subPartner)
                        <div class="d-flex align-items-center mb-2">
                            <div class="avatar bg-primary text-white rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 35px; height: 35px; font-size: 14px;">
                                {{ mb_substr($subPartner->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1">
                                <div class="fw-medium">{{ $subPartner->name }}</div>
                                <small class="text-muted">{{ $subPartner->email }}</small>
                            </div>
                            <div class="text-end">
                                <span class="badge bg-primary small">{{ $subPartner->partnerTier->tier_name ?? '기본' }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    <!-- 빠른 액션 버튼들 -->
                    <div class="row g-2">
                        <div class="col-6 col-md-3">
                            <a href="{{ route('home.partner.sales.index') }}" class="btn btn-outline-success btn-sm w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-graph-up me-1" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M0 0h1v15h15v1H0V0Zm10 3.5a.5.5 0 0 1 .5-.5h4a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-1 0V4.9l-3.613 4.417a.5.5 0 0 1-.74.037L7.06 6.767l-3.656 5.027a.5.5 0 0 1-.808-.588l4-5.5a.5.5 0 0 1 .758-.06l2.609 2.61L13.445 4H10.5a.5.5 0 0 1-.5-.5Z"/>
                                </svg>
                                매출
                            </a>
                        </div>
                        <div class="col-6 col-md-3">
                            <a href="{{ route('home.partner.commission.index') }}" class="btn btn-outline-warning btn-sm w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-currency-dollar me-1" viewBox="0 0 16 16">
                                    <path d="M4 10.781c.148 1.667 1.513 2.85 3.591 3.003V15h1.043v-1.216c2.27-.179 3.678-1.438 3.678-3.3 0-1.59-.947-2.51-2.956-3.028l-.722-.187V3.467c1.122.11 1.879.714 2.07 1.616h1.47c-.166-1.6-1.54-2.748-3.54-2.875V1H7.591v1.233c-1.939.23-3.27 1.472-3.27 3.156 0 1.454.966 2.483 2.661 2.917l.61.162v4.031c-1.149-.17-1.94-.8-2.131-1.718H4zm3.391-3.836c-1.043-.263-1.6-.825-1.6-1.616 0-.944.704-1.641 1.8-1.828v3.495l-.2-.05zm1.591 1.872c1.287.323 1.852.859 1.852 1.769 0 1.097-.826 1.828-2.2 1.939V8.73l.348.086z"/>
                                </svg>
                                커미션
                            </a>
                        </div>
                        @if(Route::has('home.partner.reviews.index'))
                        <div class="col-6 col-md-3">
                            <a href="{{ route('home.partner.reviews.index') }}" class="btn btn-outline-info btn-sm w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-star me-1" viewBox="0 0 16 16">
                                    <path d="M2.866 14.85c-.078.444.36.791.746.593l4.39-2.256 4.389 2.256c.386.198.824-.149.746-.592l-.83-4.73 3.522-3.356c.33-.314.16-.888-.282-.95l-4.898-.696L8.465.792a.513.513 0 0 0-.927 0L5.354 5.12l-4.898.696c-.441.062-.612.636-.283.95l3.523 3.356-.83 4.73zm4.905-2.767-3.686 1.894.694-3.957a.565.565 0 0 0-.163-.505L1.71 6.745l4.052-.576a.525.525 0 0 0 .393-.288L8 2.223l1.847 3.658a.525.525 0 0 0 .393.288l4.052.575-2.906 2.77a.565.565 0 0 0-.163.506l.694 3.957-3.686-1.894a.503.503 0 0 0-.461 0z"/>
                                </svg>
                                리뷰
                            </a>
                        </div>
                        @endif
                        @if(Route::has('home.partner.commission.calculate'))
                        <div class="col-6 col-md-3">
                            <a href="{{ route('home.partner.commission.calculate') }}" class="btn btn-outline-primary btn-sm w-100">
                                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="bi bi-calculator me-1" viewBox="0 0 16 16">
                                    <path d="M12 1a1 1 0 0 1 1 1v12a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h8zM4 0a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V2a2 2 0 0 0-2-2H4z"/>
                                    <path d="M4 2.5a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-.5.5h-7a.5.5 0 0 1-.5-.5v-2zm0 4a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm3-6a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1zm0 3a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v4a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-4z"/>
                                </svg>
                                계산기
                            </a>
                        </div>
                        @endif
                    </div>

                @elseif($partnerApplication)
                    <!-- 신청한 상태인 경우 -->
                    <div class="row align-items-center mb-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-center">
                                @php
                                    $statusConfig = [
                                        'draft' => ['icon' => 'pencil-square', 'color' => 'secondary', 'text' => '임시저장'],
                                        'submitted' => ['icon' => 'clock', 'color' => 'warning', 'text' => '검토 대기'],
                                        'reviewing' => ['icon' => 'eye', 'color' => 'info', 'text' => '검토 중'],
                                        'interview' => ['icon' => 'camera-video', 'color' => 'primary', 'text' => '면접 예정'],
                                        'approved' => ['icon' => 'check2', 'color' => 'success', 'text' => '승인 완료'],
                                        'rejected' => ['icon' => 'x-lg', 'color' => 'danger', 'text' => '반려됨'],
                                        'reapplied' => ['icon' => 'arrow-clockwise', 'color' => 'warning', 'text' => '재신청']
                                    ];
                                    $status = $statusConfig[$partnerApplication->application_status] ?? $statusConfig['submitted'];
                                @endphp
                                <div class="bg-{{ $status['color'] }} rounded-circle d-flex align-items-center justify-content-center me-3"
                                     style="width: 60px; height: 60px;">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="white" class="bi bi-{{ $status['icon'] }}" viewBox="0 0 16 16">
                                        @if($status['icon'] === 'clock')
                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                            <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                                        @elseif($status['icon'] === 'eye')
                                            <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/>
                                            <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                                        @elseif($status['icon'] === 'check2')
                                            <path d="M13.854 3.646a.5.5 0 0 1 0 .708l-7 7a.5.5 0 0 1-.708 0l-3.5-3.5a.5.5 0 1 1 .708-.708L6.5 10.293l6.646-6.647a.5.5 0 0 1 .708 0z"/>
                                        @else
                                            <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                                        @endif
                                    </svg>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-{{ $status['color'] }}">{{ $status['text'] }}</h5>
                                    <small class="text-muted">
                                        신청일: {{ $partnerApplication->created_at ? $partnerApplication->created_at->format('Y-m-d') : '정보 없음' }}
                                    </small>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex gap-2 justify-content-md-end">
                                <a href="{{ route('home.partner.regist.status', $partnerApplication->id) }}" class="btn btn-outline-info btn-sm">
                                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-1" viewBox="0 0 16 16">
                                        <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                        <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                    </svg>
                                    신청 상태 확인
                                </a>
                                @if(in_array($partnerApplication->application_status, ['draft', 'rejected']))
                                    <a href="{{ route('home.partner.regist.edit', $partnerApplication->id) }}" class="btn btn-outline-warning btn-sm">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil me-1" viewBox="0 0 16 16">
                                            <path d="M12.146.146a.5.5 0 0 1 .708 0l3 3a.5.5 0 0 1 0 .708l-10 10a.5.5 0 0 1-.168.11l-5 2a.5.5 0 0 1-.65-.65l2-5a.5.5 0 0 1 .11-.168l10-10zM11.207 2.5 13.5 4.793 14.793 3.5 12.5 1.207 11.207 2.5zm1.586 3L10.5 3.207 4 9.707V10h.5a.5.5 0 0 1 .5.5v.5h.5a.5.5 0 0 1 .5.5v.5h.293l6.5-6.5zm-9.761 5.175-.106.106-1.528 3.821 3.821-1.528.106-.106A.5.5 0 0 1 5 12.5V12h-.5a.5.5 0 0 1-.5-.5V11h-.5a.5.5 0 0 1-.468-.325z"/>
                                        </svg>
                                        수정하기
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>

                    @if($partnerApplication->application_status === 'rejected' && $partnerApplication->rejection_reason)
                        <div class="alert alert-danger">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-exclamation-triangle me-2" viewBox="0 0 16 16">
                                <path d="M7.938 2.016A.13.13 0 0 1 8.002 2a.13.13 0 0 1 .063.016.146.146 0 0 1 .054.057l6.857 11.667c.036.06.035.124.002.183a.163.163 0 0 1-.054.06.116.116 0 0 1-.066.017H1.146a.115.115 0 0 1-.066-.017.163.163 0 0 1-.054-.06.176.176 0 0 1 .002-.183L7.884 2.073a.147.147 0 0 1 .054-.057zm1.044-.45a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566z"/>
                                <path d="M7.002 12a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 5.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995z"/>
                            </svg>
                            <strong>반려 사유:</strong> {{ $partnerApplication->rejection_reason }}
                        </div>
                    @endif

                    @if($partnerApplication->application_status === 'interview' && $partnerApplication->interview_date)
                        <div class="alert alert-info">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-calendar-event me-2" viewBox="0 0 16 16">
                                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                            <strong>면접 일정:</strong> {{ $partnerApplication->interview_date->format('Y년 m월 d일 H:i') }}
                        </div>
                    @endif

                @else
                    <!-- 파트너 신청하지 않은 경우 -->
                    <div class="text-center py-5">
                        <div class="mb-4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-briefcase text-muted" viewBox="0 0 16 16">
                                <path d="M6.5 1A1.5 1.5 0 0 0 5 2.5V3H1.5A1.5 1.5 0 0 0 0 4.5v8A1.5 1.5 0 0 0 1.5 14h13a1.5 1.5 0 0 0 1.5-1.5v-8A1.5 1.5 0 0 0 14.5 3H11v-.5A1.5 1.5 0 0 0 9.5 1h-3zm0 1h3a.5.5 0 0 1 .5.5V3H6v-.5a.5.5 0 0 1 .5-.5zm1.886 6.914L15 7.151V12.5a.5.5 0 0 1-.5.5h-13a.5.5 0 0 1-.5-.5V7.15l6.614 1.764a1.5 1.5 0 0 0 .772 0zM1.5 4h13a.5.5 0 0 1 .5.5v1.616L8.864 7.85a.5.5 0 0 1-.258 0L1.5 6.116V4.5a.5.5 0 0 1 .5-.5z"/>
                            </svg>
                        </div>
                        <h5 class="text-muted mb-3">아직 파트너로 등록되지 않았습니다</h5>
                        <p class="text-muted mb-4">
                            파트너로 등록하시면 다양한 프로젝트에 참여하고<br>
                            수익을 창출할 수 있습니다.
                        </p>
                        <div>
                            <a href="{{ route('home.partner.intro') }}" class="btn btn-outline-primary me-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-info-circle me-1" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                                    <path d="m8.93 6.588-2.29.287-.082.38.45.083c.294.07.352.176.288.469l-.738 3.468c-.194.897.105 1.319.808 1.319.545 0 1.178-.252 1.465-.598l.088-.416c-.2.176-.492.246-.686.246-.275 0-.375-.193-.304-.533L8.93 6.588zM9 4.5a1 1 0 1 1-2 0 1 1 0 0 1 2 0z"/>
                                </svg>
                                파트너 프로그램 알아보기
                            </a>
                            <a href="{{ route('home.partner.regist.create') }}" class="btn btn-primary">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus me-1" viewBox="0 0 16 16">
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                                </svg>
                                지금 신청하기
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>