@extends('jiny-auth::layouts.dashboard')

@section('title', '이머니 지갑')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">이머니 지갑</h2>
                    <p class="text-muted mb-0">전자지갑 잔액 및 거래 내역</p>
                </div>
            </div>

            <!-- 지갑 정보 카드 -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">잔액</h5>
                            <h2 class="mb-0">{{ number_format($wallet->balance ?? 0) }} 원</h2>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">포인트</h5>
                            <h2 class="mb-0">{{ number_format($wallet->points ?? 0) }} P</h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- 최근 거래 내역 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">최근 거래 내역</h5>
                </div>
                <div class="card-body">
                    @if(isset($recentTransactions) && $recentTransactions->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>날짜</th>
                                        <th>거래 유형</th>
                                        <th>금액</th>
                                        <th>잔액</th>
                                        <th>상태</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($recentTransactions as $transaction)
                                        <tr>
                                            <td>{{ $transaction->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ $transaction->type }}</td>
                                            <td>{{ number_format($transaction->amount) }} 원</td>
                                            <td>{{ number_format($transaction->balance) }} 원</td>
                                            <td>
                                                <span class="badge bg-{{ $transaction->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ $transaction->status }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">거래 내역이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
