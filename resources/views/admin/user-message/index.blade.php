@extends('jiny-auth::layouts.dashboard')

@section('title', '메시지 관리')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3 d-flex justify-content-between align-items-center">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">
                            메시지 관리
                            <span class="fs-5">(총 {{ $messages->total() }}개)</span>
                        </h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="/admin/auth">Dashboard</a></li>
                                <li class="breadcrumb-item">사용자</li>
                                <li class="breadcrumb-item active">메시지</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <form method="GET">
                            <div class="row">
                                <div class="col-md-5">
                                    <input type="search" name="search" class="form-control"
                                           placeholder="제목, 내용, 이메일 검색..."
                                           value="{{ request('search') }}">
                                </div>
                                <div class="col-md-2">
                                    <select name="status" class="form-select">
                                        <option value="">모든 상태</option>
                                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>읽음</option>
                                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>안읽음</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-check mt-2">
                                        <input class="form-check-input" type="checkbox" name="notice" value="1"
                                               id="noticeCheck" {{ request('notice') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="noticeCheck">
                                            공지사항만
                                        </label>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-secondary">
                                        <i class="fe fe-search"></i> 검색
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="table-responsive">
                        <table class="table mb-0 text-nowrap table-hover">
                            <thead class="table-light">
                                <tr>
                                    <th width="50">상태</th>
                                    <th>받는사람</th>
                                    <th>보낸사람</th>
                                    <th>제목</th>
                                    <th width="100">공지</th>
                                    <th width="150">일시</th>
                                    <th width="100">작업</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($messages as $message)
                                <tr>
                                    <td>
                                        @if($message->readed_at)
                                            <span class="badge bg-secondary">읽음</span>
                                        @else
                                            <span class="badge bg-primary">안읽음</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $message->name ?: '-' }}</strong><br>
                                            <small class="text-muted">{{ $message->email }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <div>
                                            <strong>{{ $message->from_name ?: '-' }}</strong><br>
                                            <small class="text-muted">{{ $message->from_email ?: 'System' }}</small>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.auth.message.show', $message->id) }}">
                                            {{ Str::limit($message->subject, 50) }}
                                        </a>
                                        @if($message->label)
                                            <span class="badge bg-info ms-1">{{ $message->label }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($message->notice)
                                            <span class="badge bg-warning">공지</span>
                                        @endif
                                    </td>
                                    <td>
                                        {{ $message->created_at->format('Y-m-d H:i') }}
                                        @if($message->readed_at)
                                            <br><small class="text-muted">읽음: {{ $message->readed_at->format('m-d H:i') }}</small>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="hstack gap-2">
                                            <a href="{{ route('admin.auth.message.show', $message->id) }}"
                                               class="btn btn-sm btn-light">
                                                <i class="fe fe-eye"></i>
                                            </a>
                                            <button class="btn btn-sm btn-light text-danger">
                                                <i class="fe fe-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">메시지가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($messages->hasPages())
                    <div class="card-footer">
                        {{ $messages->links('pagination::bootstrap-5') }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection