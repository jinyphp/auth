@extends('jiny-auth::layouts.dashboard')

@section('title', '이머니 출금')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">이머니 출금</h2>
                    <p class="text-muted mb-0">지갑에서 금액을 출금합니다</p>
                </div>
                <a href="{{ route('account.wallet.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 지갑으로 돌아가기
                </a>
            </div>

            <!-- 출금 내역 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">출금 내역</h5>
                </div>
                <div class="card-body">
                    @if(isset($withdrawals) && $withdrawals->count() > 0)
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>날짜</th>
                                        <th>금액</th>
                                        <th>상태</th>
                                        <th>메모</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($withdrawals as $withdrawal)
                                        <tr>
                                            <td>{{ $withdrawal->created_at->format('Y-m-d H:i') }}</td>
                                            <td>{{ number_format($withdrawal->amount) }} 원</td>
                                            <td>
                                                <span class="badge bg-{{ $withdrawal->status === 'completed' ? 'success' : 'warning' }}">
                                                    {{ $withdrawal->status }}
                                                </span>
                                            </td>
                                            <td>{{ $withdrawal->memo ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <p class="text-muted">출금 내역이 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
