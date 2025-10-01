@extends('jiny-auth::layouts.admin.sidebar')

@section('title', 'OAuth 프로바이더 상세')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">OAuth 프로바이더 상세</h2>
                    <p class="text-muted mb-0">OAuth 프로바이더 정보</p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.oauth.providers.edit', $provider->id) }}" class="btn btn-primary">
                        <i class="bi bi-pencil"></i> 수정
                    </a>
                    <a href="{{ route('admin.auth.oauth.providers.index') }}" class="btn btn-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
                </div>
            </div>

            <!-- 프로바이더 정보 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($provider))
                        <table class="table">
                            <tbody>
                                <tr>
                                    <th style="width: 200px;">ID</th>
                                    <td>{{ $provider->id }}</td>
                                </tr>
                                <tr>
                                    <th>이름</th>
                                    <td>{{ $provider->name }}</td>
                                </tr>
                                <tr>
                                    <th>프로바이더</th>
                                    <td>{{ $provider->provider }}</td>
                                </tr>
                                <tr>
                                    <th>Client ID</th>
                                    <td><code>{{ $provider->client_id }}</code></td>
                                </tr>
                                <tr>
                                    <th>Client Secret</th>
                                    <td><code>{{ str_repeat('*', strlen($provider->client_secret) - 4) . substr($provider->client_secret, -4) }}</code></td>
                                </tr>
                                <tr>
                                    <th>Callback URL</th>
                                    <td>{{ $provider->callback_url ?? '-' }}</td>
                                </tr>
                                <tr>
                                    <th>연결된 계정</th>
                                    <td>{{ $provider->oauth_accounts_count ?? 0 }}개</td>
                                </tr>
                                <tr>
                                    <th>상태</th>
                                    <td>
                                        <span class="badge bg-{{ $provider->enable === 'yes' ? 'success' : 'secondary' }}">
                                            {{ $provider->enable === 'yes' ? '활성' : '비활성' }}
                                        </span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>생성일</th>
                                    <td>{{ $provider->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>수정일</th>
                                    <td>{{ $provider->updated_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    @else
                        <p class="text-muted">프로바이더를 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
