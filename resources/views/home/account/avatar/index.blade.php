@extends('jiny-auth::layouts.home')

@section('title', '아바타 관리')

@section('content')
<div class="container mb-4">
    <!-- 헤더 -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-2">
                        <i class="bi bi-person-circle text-primary"></i>
                        아바타 관리
                    </h2>
                    <p class="text-muted mb-0">프로필 사진을 관리할 수 있습니다</p>
                </div>
                <div>
                    <a href="{{ route('home.dashboard') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 대시보드로
                    </a>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>오류 발생:</strong> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>유효성 검사 실패:</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- 현재 아바타 & 업로드 -->
        <div class="col-md-4">
            <!-- 현재 아바타 -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-star-fill text-warning"></i> 현재 아바타
                    </h5>
                </div>
                <div class="card-body text-center">
                    @if($defaultAvatar && $defaultAvatar->image)
                        <img src="{{ $defaultAvatar->image }}"
                             alt="{{ $user->name }}"
                             class="img-fluid rounded-circle mb-3"
                             style="width: 200px; height: 200px; object-fit: cover;" />
                        <p class="text-muted small mb-0">
                            선택일: {{ \Carbon\Carbon::parse($defaultAvatar->selected)->format('Y-m-d H:i') }}
                        </p>
                    @else
                        <div class="rounded-circle bg-primary d-inline-flex align-items-center justify-content-center text-white mb-3"
                             style="width: 200px; height: 200px; font-size: 80px; font-weight: bold;">
                            {{ mb_substr($user->name, 0, 1) }}
                        </div>
                        <p class="text-muted">기본 아바타가 설정되지 않았습니다.</p>
                    @endif
                </div>
            </div>

            <!-- 새 아바타 업로드 -->
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">
                        <i class="bi bi-upload"></i> 새 아바타 업로드
                    </h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('home.account.avatar.store') }}"
                          method="POST"
                          enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="avatar" class="form-label">이미지 선택</label>
                            <input type="file"
                                   class="form-control @error('avatar') is-invalid @enderror"
                                   id="avatar"
                                   name="avatar"
                                   accept="image/jpeg,image/png,image/gif,image/webp"
                                   required>
                            @error('avatar')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">
                                최대 5MB | JPG, PNG, GIF, WEBP
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">설명 (선택)</label>
                            <textarea class="form-control"
                                      id="description"
                                      name="description"
                                      rows="2"
                                      placeholder="아바타에 대한 설명..."></textarea>
                        </div>

                        <div class="form-check mb-3">
                            <input class="form-check-input"
                                   type="checkbox"
                                   id="set_as_default"
                                   name="set_as_default"
                                   value="1"
                                   checked>
                            <label class="form-check-label" for="set_as_default">
                                업로드 후 기본 아바타로 설정
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-upload"></i> 업로드
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 아바타 히스토리 -->
        <div class="col-md-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-clock-history"></i> 아바타 히스토리
                    </h5>
                    <span class="badge bg-primary">총 {{ $avatars->count() }}개</span>
                </div>
                <div class="card-body">
                    @forelse($avatars as $avatar)
                        <div class="card mb-3 {{ $avatar->selected ? 'border-warning' : '' }}">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-3 text-center">
                                        @if($avatar->image)
                                            <img src="{{ $avatar->image }}"
                                                 alt="Avatar"
                                                 class="img-fluid rounded"
                                                 style="max-width: 120px; max-height: 120px; object-fit: cover;" />
                                        @else
                                            <div class="rounded-circle bg-secondary d-inline-flex align-items-center justify-content-center text-white"
                                                 style="width: 80px; height: 80px;">
                                                <i class="bi bi-person fs-3"></i>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="col-md-6">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <h6 class="mb-0">아바타 #{{ $avatar->id }}</h6>
                                            @if($avatar->selected)
                                                <span class="badge bg-warning">
                                                    <i class="bi bi-star-fill"></i> 기본 아바타
                                                </span>
                                            @endif
                                        </div>
                                        @if($avatar->description)
                                            <p class="text-muted small mb-2">{{ $avatar->description }}</p>
                                        @endif
                                        <p class="text-muted small mb-0">
                                            <i class="bi bi-calendar"></i>
                                            등록: {{ \Carbon\Carbon::parse($avatar->created_at)->format('Y-m-d H:i') }}
                                        </p>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="d-grid gap-2">
                                            @if(!$avatar->selected)
                                                <form action="{{ route('home.account.avatar.set-default', $avatar->id) }}"
                                                      method="POST">
                                                    @csrf
                                                    <button type="submit"
                                                            class="btn btn-sm btn-outline-warning w-100"
                                                            title="기본 아바타로 설정">
                                                        <i class="bi bi-star"></i> 기본 설정
                                                    </button>
                                                </form>
                                            @else
                                                <button type="button"
                                                        class="btn btn-sm btn-warning w-100 disabled"
                                                        style="opacity: 0.6;">
                                                    <i class="bi bi-star-fill"></i> 현재 기본
                                                </button>
                                            @endif

                                            <button type="button"
                                                    class="btn btn-sm btn-outline-primary w-100"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal{{ $avatar->id }}"
                                                    title="상세보기">
                                                <i class="bi bi-eye"></i> 상세
                                            </button>

                                            <form action="{{ route('home.account.avatar.delete', $avatar->id) }}"
                                                  method="POST"
                                                  onsubmit="return confirm('이 아바타를 삭제하시겠습니까?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="btn btn-sm btn-outline-danger w-100"
                                                        title="삭제">
                                                    <i class="bi bi-trash"></i> 삭제
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 상세보기 모달 -->
                        <div class="modal fade" id="viewModal{{ $avatar->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">
                                            <i class="bi bi-image-fill text-primary"></i> 아바타 상세정보
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="text-center mb-4">
                                            @if($avatar->image)
                                                <img src="{{ $avatar->image }}"
                                                     alt="Avatar"
                                                     class="img-fluid rounded"
                                                     style="max-width: 300px; max-height: 300px;" />
                                            @endif
                                        </div>
                                        <table class="table table-bordered">
                                            <tr>
                                                <th width="30%">ID</th>
                                                <td>#{{ $avatar->id }}</td>
                                            </tr>
                                            <tr>
                                                <th>기본 아바타</th>
                                                <td>
                                                    @if($avatar->selected)
                                                        <span class="badge bg-warning">예</span>
                                                        <small class="text-muted ms-2">
                                                            {{ \Carbon\Carbon::parse($avatar->selected)->format('Y-m-d H:i:s') }}
                                                        </small>
                                                    @else
                                                        <span class="badge bg-secondary">아니오</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            @if($avatar->description)
                                            <tr>
                                                <th>설명</th>
                                                <td>{{ $avatar->description }}</td>
                                            </tr>
                                            @endif
                                            <tr>
                                                <th>등록일</th>
                                                <td>{{ \Carbon\Carbon::parse($avatar->created_at)->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                            <tr>
                                                <th>수정일</th>
                                                <td>{{ \Carbon\Carbon::parse($avatar->updated_at)->format('Y-m-d H:i:s') }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                            닫기
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-5">
                            <i class="bi bi-inbox fs-1 text-muted"></i>
                            <p class="text-muted mt-3 mb-2">아직 등록된 아바타가 없습니다.</p>
                            <p class="text-muted small">왼쪽에서 새 아바타를 업로드하세요.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 파일 선택 시 미리보기
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];

    if (file && file.type.startsWith('image/')) {
        console.log('Selected file:', file.name, '(' + (file.size / 1024).toFixed(2) + ' KB)');
    }
});

// 폼 제출 시 파일 확인
document.querySelector('form[enctype="multipart/form-data"]')?.addEventListener('submit', function(e) {
    const file = this.querySelector('input[type="file"]').files[0];

    if (!file || file.size === 0) {
        alert('파일을 선택해주세요.');
        e.preventDefault();
        return false;
    }
});
</script>
@endpush
