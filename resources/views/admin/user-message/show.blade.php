@extends('jiny-auth::layouts.admin.sidebar')

@section('title', '메시지 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">메시지 상세</h2>
                    <p class="text-muted mb-0">사용자 메시지 정보</p>
                </div>
                <a href="{{ route('admin.user-messages.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 메시지 상세 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($message))
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">ID</th>
                                    <td>{{ $message->id }}</td>
                                </tr>
                                <tr>
                                    <th>보낸 사람</th>
                                    <td>{{ $message->sender->name ?? '시스템' }} ({{ $message->sender->email ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <th>받는 사람</th>
                                    <td>{{ $message->recipient->name ?? '-' }} ({{ $message->recipient->email ?? '-' }})</td>
                                </tr>
                                <tr>
                                    <th>제목</th>
                                    <td>{{ $message->subject }}</td>
                                </tr>
                                <tr>
                                    <th>내용</th>
                                    <td>
                                        <div style="max-height: 300px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; border-radius: 4px;">
                                            {!! nl2br(e($message->content)) !!}
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>상태</th>
                                    <td>
                                        <span class="badge bg-{{ $message->read_at ? 'success' : 'warning' }}">
                                            {{ $message->read_at ? '읽음' : '읽지 않음' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>전송일</th>
                                    <td>{{ $message->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                @if($message->read_at)
                                    <tr>
                                        <th>읽은 날짜</th>
                                        <td>{{ $message->read_at->format('Y-m-d H:i:s') }}</td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">메시지를 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
