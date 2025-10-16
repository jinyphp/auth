@extends('jiny-auth::layouts.home')

@section('title', '탈퇴 신청 완료')

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">탈퇴 신청 완료</h1>
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
    </div>
@endsection
