@extends('jiny-auth::layouts.admin.sidebar')

@section('title', 'OAuth 프로바이더 관리')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">OAuth 프로바이더 관리</h2>
                    <p class="text-muted mb-0">소셜 로그인 프로바이더 목록</p>
                </div>
                <a href="{{ route('admin.auth.oauth.providers.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg"></i> 프로바이더 추가
                </a>
            </div>

            <!-- 프로바이더 목록 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($providers) && $providers->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>이름</th>
                                        <th>프로바이더</th>
                                        <th>연결된 계정</th>
                                        <th>상태</th>
                                        <th>액션</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($providers as $provider)
                                        <tr>
                                            <td>{{ $provider->id }}</td>
                                            <td>{{ $provider->name }}</td>
                                            <td>{{ $provider->provider }}</td>
                                            <td>{{ $provider->oauth_accounts_count ?? 0 }}</td>
                                            <td>
                                                <span class="badge bg-{{ $provider->enable === 'yes' ? 'success' : 'secondary' }}">
                                                    {{ $provider->enable === 'yes' ? '활성' : '비활성' }}
                                                </span>
                                            </td>
                                            <td>
                                                <a href="{{ route('admin.auth.oauth.providers.show', $provider->id) }}"
                                                   class="btn btn-sm btn-info">
                                                    <i class="bi bi-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.auth.oauth.providers.edit', $provider->id) }}"
                                                   class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if(method_exists($providers, 'links'))
                            <div class="mt-3">
                                {{ $providers->links() }}
                            </div>
                        @endif
                    @else
                        <p class="text-muted mb-0">등록된 OAuth 프로바이더가 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
