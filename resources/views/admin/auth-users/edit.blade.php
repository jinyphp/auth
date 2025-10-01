@extends('jiny-auth::layouts.dashboard')

@section('title', '사용자 편집')

@section('content')
    <section class="container-fluid p-4">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-12">
                <!-- Page Header -->
                <div class="border-bottom pb-3 mb-3">
                    <div class="d-flex flex-column gap-1">
                        <h1 class="mb-0 h2 fw-bold">사용자 편집</h1>
                        <!-- Breadcrumb  -->
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item">
                                    <a href="/admin/auth">Dashboard</a>
                                </li>
                                <li class="breadcrumb-item">
                                    <a href="{{ route('admin.auth.users.index') }}">사용자 관리</a>
                                </li>
                                <li class="breadcrumb-item active" aria-current="page">편집</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8 col-md-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ route('admin.auth.users.update', $user->id) }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Basic Information -->
                            <h5 class="mb-3">기본 정보</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="name" class="form-label">이름 <span class="text-danger">*</span></label>
                                    <input type="text"
                                           class="form-control @error('name') is-invalid @enderror"
                                           id="name"
                                           name="name"
                                           value="{{ old('name', $user->name) }}"
                                           required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="username" class="form-label">사용자명 <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">@</span>
                                        <input type="text"
                                               class="form-control @error('username') is-invalid @enderror"
                                               id="username"
                                               name="username"
                                               value="{{ old('username', $user->username) }}"
                                               required>
                                    </div>
                                    @error('username')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="email" class="form-label">이메일 <span class="text-danger">*</span></label>
                                    <input type="email"
                                           class="form-control @error('email') is-invalid @enderror"
                                           id="email"
                                           name="email"
                                           value="{{ old('email', $user->email) }}"
                                           required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="phone" class="form-label">전화번호</label>
                                    <input type="text"
                                           class="form-control @error('phone') is-invalid @enderror"
                                           id="phone"
                                           name="phone"
                                           value="{{ old('phone', $user->phone) }}"
                                           placeholder="010-0000-0000">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="address" class="form-label">주소</label>
                                <textarea class="form-control @error('address') is-invalid @enderror"
                                          id="address"
                                          name="address"
                                          rows="2">{{ old('address', $user->address) }}</textarea>
                                @error('address')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <hr class="my-4">

                            <!-- Security -->
                            <h5 class="mb-3">비밀번호 변경</h5>
                            <p class="text-muted mb-3">비밀번호를 변경하려면 아래 필드를 입력하세요. 변경하지 않으려면 비워두세요.</p>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="password" class="form-label">새 비밀번호</label>
                                    <input type="password"
                                           class="form-control @error('password') is-invalid @enderror"
                                           id="password"
                                           name="password">
                                    <small class="text-muted">최소 8자 이상</small>
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="password_confirmation" class="form-label">비밀번호 확인</label>
                                    <input type="password"
                                           class="form-control"
                                           id="password_confirmation"
                                           name="password_confirmation">
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Role & Status -->
                            <h5 class="mb-3">역할 및 상태</h5>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="role" class="form-label">역할 <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror"
                                            id="role"
                                            name="role"
                                            required>
                                        <option value="admin" {{ old('role', $user->role) == 'admin' ? 'selected' : '' }}>관리자</option>
                                        <option value="editor" {{ old('role', $user->role) == 'editor' ? 'selected' : '' }}>편집자</option>
                                        <option value="user" {{ old('role', $user->role) == 'user' ? 'selected' : '' }}>사용자</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label for="status" class="form-label">상태 <span class="text-danger">*</span></label>
                                    <select class="form-select @error('status') is-invalid @enderror"
                                            id="status"
                                            name="status"
                                            required>
                                        <option value="active" {{ old('status', $user->status) == 'active' ? 'selected' : '' }}>활성</option>
                                        <option value="inactive" {{ old('status', $user->status) == 'inactive' ? 'selected' : '' }}>비활성</option>
                                        <option value="suspended" {{ old('status', $user->status) == 'suspended' ? 'selected' : '' }}>정지</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <hr class="my-4">

                            <!-- Profile -->
                            <h5 class="mb-3">프로필</h5>

                            @if($user->avatar)
                                <div class="mb-3">
                                    <label class="form-label">현재 프로필 이미지</label>
                                    <div>
                                        <img src="{{ Storage::url($user->avatar) }}"
                                             alt="Current avatar"
                                             class="rounded-circle"
                                             style="width: 100px; height: 100px; object-fit: cover;">
                                    </div>
                                </div>
                            @endif

                            <div class="mb-3">
                                <label for="avatar" class="form-label">새 프로필 이미지</label>
                                <input type="file"
                                       class="form-control @error('avatar') is-invalid @enderror"
                                       id="avatar"
                                       name="avatar"
                                       accept="image/*">
                                <small class="text-muted">JPG, PNG, GIF 형식 (최대 2MB)</small>
                                @error('avatar')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="d-flex justify-content-end gap-2">
                                <a href="{{ route('admin.auth.users.show', $user->id) }}" class="btn btn-secondary">취소</a>
                                <button type="submit" class="btn btn-primary">변경사항 저장</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <!-- User Info Card -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">사용자 정보</h5>
                    </div>
                    <div class="card-body">
                        <div class="text-center mb-3">
                            @if($user->avatar)
                                <img src="{{ Storage::url($user->avatar) }}"
                                     alt="{{ $user->name }}"
                                     class="rounded-circle avatar-xl" />
                            @else
                                <div class="avatar avatar-xl avatar-primary">
                                    <span class="avatar-initials rounded-circle">
                                        {{ substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>
                        <dl class="row">
                            <dt class="col-sm-5">ID:</dt>
                            <dd class="col-sm-7">#{{ $user->id }}</dd>

                            <dt class="col-sm-5">가입일:</dt>
                            <dd class="col-sm-7">{{ $user->created_at->format('Y-m-d') }}</dd>

                            <dt class="col-sm-5">마지막 로그인:</dt>
                            <dd class="col-sm-7">
                                @if($user->last_login_at)
                                    {{ $user->last_login_at->diffForHumans() }}
                                @else
                                    -
                                @endif
                            </dd>

                            <dt class="col-sm-5">이메일 인증:</dt>
                            <dd class="col-sm-7">
                                @if($user->email_verified_at)
                                    <span class="badge bg-success">인증됨</span>
                                @else
                                    <span class="badge bg-warning">미인증</span>
                                @endif
                            </dd>
                        </dl>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">도움말</h5>
                    </div>
                    <div class="card-body">
                        <h6 class="mb-2">역할 설명</h6>
                        <ul class="list-unstyled mb-3">
                            <li class="mb-2">
                                <span class="badge bg-danger">관리자</span>
                                <small class="d-block text-muted mt-1">모든 권한을 가진 최고 관리자</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-primary">편집자</span>
                                <small class="d-block text-muted mt-1">콘텐츠 편집 및 관리 권한</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-secondary">사용자</span>
                                <small class="d-block text-muted mt-1">일반 사용자 권한</small>
                            </li>
                        </ul>

                        <hr>

                        <h6 class="mb-2">상태 설명</h6>
                        <ul class="list-unstyled">
                            <li class="mb-2">
                                <span class="badge bg-success">활성</span>
                                <small class="d-block text-muted mt-1">정상적으로 로그인 가능</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-warning">비활성</span>
                                <small class="d-block text-muted mt-1">로그인 불가, 활성화 필요</small>
                            </li>
                            <li class="mb-2">
                                <span class="badge bg-danger">정지</span>
                                <small class="d-block text-muted mt-1">계정 정지 상태</small>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection