@extends('jiny-auth::layouts.admin.sidebar')

@section('title', 'OAuth 프로바이더 수정')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="mb-0">OAuth 프로바이더 수정</h2>
                    <p class="text-muted mb-0">OAuth 프로바이더 정보를 수정합니다</p>
                </div>
                <a href="{{ route('admin.auth.oauth.providers.index') }}" class="btn btn-secondary">
                    <i class="bi bi-arrow-left"></i> 목록으로
                </a>
            </div>

            <!-- 수정 폼 -->
            <div class="card">
                <div class="card-body">
                    @if(isset($provider))
                        <form action="{{ route('admin.auth.oauth.providers.update', $provider->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">이름</label>
                                <input type="text" class="form-control @error('name') is-invalid @enderror"
                                       id="name" name="name" value="{{ old('name', $provider->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="provider" class="form-label">프로바이더</label>
                                <input type="text" class="form-control" id="provider" name="provider"
                                       value="{{ $provider->provider }}" readonly>
                            </div>

                            <div class="mb-3">
                                <label for="client_id" class="form-label">Client ID</label>
                                <input type="text" class="form-control @error('client_id') is-invalid @enderror"
                                       id="client_id" name="client_id" value="{{ old('client_id', $provider->client_id) }}" required>
                                @error('client_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="client_secret" class="form-label">Client Secret</label>
                                <input type="text" class="form-control @error('client_secret') is-invalid @enderror"
                                       id="client_secret" name="client_secret" value="{{ old('client_secret', $provider->client_secret) }}" required>
                                @error('client_secret')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="callback_url" class="form-label">Callback URL</label>
                                <input type="url" class="form-control @error('callback_url') is-invalid @enderror"
                                       id="callback_url" name="callback_url" value="{{ old('callback_url', $provider->callback_url) }}">
                                @error('callback_url')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="enable" class="form-label">활성화</label>
                                <select class="form-select @error('enable') is-invalid @enderror" id="enable" name="enable">
                                    <option value="yes" {{ old('enable', $provider->enable) === 'yes' ? 'selected' : '' }}>활성</option>
                                    <option value="no" {{ old('enable', $provider->enable) === 'no' ? 'selected' : '' }}>비활성</option>
                                </select>
                                @error('enable')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.oauth.providers.index') }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">저장</button>
                            </div>
                        </form>
                    @else
                        <p class="text-muted">프로바이더를 찾을 수 없습니다.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
