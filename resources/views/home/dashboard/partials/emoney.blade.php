<div class="row">
    <!-- 이머니 잔액 -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">이머니 잔액</h6>
                        <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                            ₩{{ number_format($emoney->balance ?? 0) }}
                        </h2>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Current balance</p>
                    </div>
                    <div>
                        @if(($emoney->total_earned ?? 0) > 0)
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #22c55e; color: white; font-size: 0.75rem; font-weight: 600;">
                                +{{ number_format($emoney->total_earned ?? 0) }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e5e7eb; color: #6b7280; font-size: 0.75rem; font-weight: 600;">
                                0
                            </span>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('home.emoney.index') }}" class="text-decoration-none" style="color: #22c55e; font-size: 0.875rem; font-weight: 500;">
                        이머니 관리 →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 포인트 잔액 -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">포인트 잔액</h6>
                        <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                            {{ number_format($point->balance ?? 0) }}P
                        </h2>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Available points</p>
                    </div>
                    <div>
                        @if(($point->total_earned ?? 0) > 0)
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #3b82f6; color: white; font-size: 0.75rem; font-weight: 600;">
                                +{{ number_format($point->total_earned ?? 0) }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e5e7eb; color: #6b7280; font-size: 0.75rem; font-weight: 600;">
                                0
                            </span>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('home.emoney.point.index') }}" class="text-decoration-none" style="color: #3b82f6; font-size: 0.875rem; font-weight: 500;">
                        포인트 관리 →
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- 총 사용액 -->
    <div class="col-lg-4 col-md-12 mb-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <h6 class="text-muted text-uppercase mb-2" style="font-size: 0.875rem; font-weight: 600;">총 사용액</h6>
                        <h2 class="mb-1" style="font-size: 2rem; font-weight: 700; color: #1a1a1a;">
                            ₩{{ number_format(($emoney->total_used ?? 0) + ($point->total_used ?? 0)) }}
                        </h2>
                        <p class="text-muted mb-0" style="font-size: 0.875rem;">Total spending</p>
                    </div>
                    <div>
                        @if((($emoney->total_used ?? 0) + ($point->total_used ?? 0)) > 0)
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #f59e0b; color: white; font-size: 0.75rem; font-weight: 600;">
                                {{ number_format(($emoney->total_used ?? 0) + ($point->total_used ?? 0)) }}
                            </span>
                        @else
                            <span class="badge rounded-pill px-3 py-2" style="background-color: #e5e7eb; color: #6b7280; font-size: 0.75rem; font-weight: 600;">
                                0
                            </span>
                        @endif
                    </div>
                </div>
                <div class="mt-3">
                    <a href="{{ route('home.emoney.log') }}" class="text-decoration-none" style="color: #f59e0b; font-size: 0.875rem; font-weight: 500;">
                        사용 내역 →
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
