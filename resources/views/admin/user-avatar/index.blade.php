@extends('jiny-auth::layouts.admin.sidebar')

@section('title', "{$user->name}님의 아바타 관리")

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <!-- 헤더 -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-2">
                            <li class="breadcrumb-item">
                                <a href="{{ route('admin.auth.users.index') }}{{ $shardId ? '?shard_id=' . $shardId : '' }}">
                                    사용자 관리
                                </a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">아바타 관리</li>
                        </ol>
                    </nav>
                    <h2 class="mb-0">
                        <i class="bi bi-person-circle text-primary"></i>
                        {{ $user->name }}님의 아바타 관리
                    </h2>
                    <p class="text-muted mb-0">
                        <code>{{ $user->email }}</code> | UUID: <code class="small">{{ Str::limit($user->uuid, 30) }}</code>
                    </p>
                </div>
                <div>
                    <a href="{{ route('admin.auth.users.index') }}{{ $shardId ? '?shard_id=' . $shardId : '' }}"
                       class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left"></i> 목록으로
                    </a>
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

            {{-- 디버그 정보 --}}
            <div class="alert alert-info">
                <strong>PHP 업로드 설정:</strong>
                <ul class="mb-0 small">
                    <li>upload_max_filesize: {{ ini_get('upload_max_filesize') }}</li>
                    <li>post_max_size: {{ ini_get('post_max_size') }}</li>
                    <li>max_file_uploads: {{ ini_get('max_file_uploads') }}</li>
                    <li>memory_limit: {{ ini_get('memory_limit') }}</li>
                </ul>
            </div>

            <div class="row">
                <!-- 현재 아바타 -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-star-fill text-warning"></i> 현재 아바타
                            </h5>
                        </div>
                        <div class="card-body text-center">
                            @if($defaultAvatar && $defaultAvatar->image)
                                <img src="{{ $defaultAvatar->image }}"
                                     alt="{{ $user->name }}"
                                     class="img-fluid rounded mb-3"
                                     style="max-width: 200px; max-height: 200px; object-fit: cover;" />
                                <p class="text-muted small mb-0">
                                    선택일: {{ \Carbon\Carbon::parse($defaultAvatar->selected)->format('Y-m-d H:i') }}
                                </p>
                            @else
                                <div class="avatar avatar-xxl avatar-primary mb-3">
                                    <span class="avatar-initials rounded-circle fs-1">
                                        {{ mb_substr($user->name, 0, 1) }}
                                    </span>
                                </div>
                                <p class="text-muted">기본 아바타가 설정되지 않았습니다.</p>
                            @endif
                        </div>
                    </div>

                    <!-- 새 아바타 업로드 -->
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="mb-0">
                                <i class="bi bi-upload"></i> 새 아바타 업로드
                            </h5>
                        </div>
                        <div class="card-body">
                            <form action="{{ route('admin.user-avatar.store', $user->id) }}{{ $shardId ? '?shard_id=' . $shardId : '' }}"
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
                                                    <div class="avatar avatar-lg avatar-secondary">
                                                        <span class="avatar-initials rounded-circle">
                                                            <i class="bi bi-person"></i>
                                                        </span>
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
                                                <p class="text-muted small mb-1">
                                                    <i class="bi bi-folder"></i>
                                                    <code class="small">{{ Str::limit($avatar->image, 40) }}</code>
                                                </p>
                                                <p class="text-muted small mb-0">
                                                    <i class="bi bi-calendar"></i>
                                                    등록: {{ \Carbon\Carbon::parse($avatar->created_at)->format('Y-m-d H:i') }}
                                                </p>
                                            </div>
                                            <div class="col-md-3">
                                                <div class="d-grid gap-2">
                                                    @if(!$avatar->selected)
                                                        <form action="{{ route('admin.user-avatar.set-default', [$user->id, $avatar->id]) }}{{ $shardId ? '?shard_id=' . $shardId : '' }}"
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

                                                    <form action="{{ route('admin.user-avatar.delete', [$user->id, $avatar->id]) }}{{ $shardId ? '?shard_id=' . $shardId : '' }}"
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
                                        <div class="modal-content border-0 shadow-lg">
                                            <!-- 헤더 -->
                                            <div class="modal-header border-0 pb-0">
                                                <h5 class="modal-title fw-bold">
                                                    <i class="bi bi-image-fill text-primary"></i> 아바타 상세정보
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <!-- 바디 -->
                                            <div class="modal-body p-4">
                                                <!-- 아바타 이미지 섹션 -->
                                                <div class="text-center mb-4">
                                                    @if($avatar->image)
                                                        <div class="position-relative d-inline-block">
                                                            <img src="{{ $avatar->image }}"
                                                                 alt="Avatar"
                                                                 class="rounded-4 shadow"
                                                                 style="max-width: 200px; max-height: 200px; object-fit: cover; border: 3px solid #e5e7eb;" />
                                                            @if($avatar->selected)
                                                                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-warning text-dark shadow-sm"
                                                                      style="padding: 0.5rem 0.75rem;">
                                                                    <i class="bi bi-star-fill"></i> 기본 아바타
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                <!-- 정보 카드 -->
                                                <div class="row g-3">
                                                    <!-- ID -->
                                                    <div class="col-12">
                                                        <div class="card border-0 shadow-sm">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-center justify-content-between">
                                                                    <div class="d-flex align-items-center">
                                                                        <div class="d-flex align-items-center justify-content-center rounded-circle me-3"
                                                                             style="width: 40px; height: 40px; background-color: #6366f1;">
                                                                            <i class="bi bi-hash text-white"></i>
                                                                        </div>
                                                                        <div>
                                                                            <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">ID</small>
                                                                            <span class="fw-bold">#{{ $avatar->id }}</span>
                                                                        </div>
                                                                    </div>
                                                                    <span class="badge rounded-pill bg-primary" style="padding: 0.5rem 1rem;">
                                                                        아바타 {{ $avatar->id }}
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 기본 아바타 상태 -->
                                                    <div class="col-12">
                                                        <div class="card border-0 shadow-sm">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="d-flex align-items-center justify-content-center rounded-circle me-3"
                                                                         style="width: 40px; height: 40px; background-color: #f59e0b;">
                                                                        <i class="bi bi-star-fill text-white"></i>
                                                                    </div>
                                                                    <div class="flex-grow-1">
                                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">기본 아바타</small>
                                                                        @if($avatar->selected)
                                                                            <span class="badge bg-warning text-dark shadow-sm" style="padding: 0.4rem 0.8rem;">
                                                                                <i class="bi bi-check-circle-fill"></i> 예
                                                                            </span>
                                                                            <small class="text-muted d-block mt-2" style="font-size: 0.7rem;">
                                                                                <i class="bi bi-clock"></i> {{ \Carbon\Carbon::parse($avatar->selected)->format('Y-m-d H:i:s') }}
                                                                            </small>
                                                                        @else
                                                                            <span class="badge bg-secondary">아니오</span>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 이미지 경로 -->
                                                    <div class="col-12">
                                                        <div class="card border-0 shadow-sm">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="d-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0"
                                                                         style="width: 40px; height: 40px; background-color: #06b6d4;">
                                                                        <i class="bi bi-folder2-open text-white"></i>
                                                                    </div>
                                                                    <div class="flex-grow-1 overflow-hidden">
                                                                        <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">이미지 경로</small>
                                                                        <div class="d-flex align-items-start gap-2">
                                                                            <code class="flex-grow-1 p-2 rounded text-break"
                                                                                  style="font-size: 10px; line-height: 1.6; background: #f8f9fa; border: 1px solid #dee2e6;">{{ $avatar->image }}</code>
                                                                            <button type="button"
                                                                                    class="btn btn-sm btn-primary flex-shrink-0 shadow-sm"
                                                                                    onclick="navigator.clipboard.writeText('{{ $avatar->image }}'); this.innerHTML='<i class=\'bi bi-check2\'></i>'; this.classList.remove('btn-primary'); this.classList.add('btn-success'); setTimeout(() => { this.innerHTML='<i class=\'bi bi-clipboard\'></i>'; this.classList.remove('btn-success'); this.classList.add('btn-primary'); }, 2000);"
                                                                                    title="경로 복사">
                                                                                <i class="bi bi-clipboard"></i>
                                                                            </button>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <!-- 설명 -->
                                                    @if($avatar->description)
                                                        <div class="col-12">
                                                            <div class="card border-0 shadow-sm">
                                                                <div class="card-body p-3">
                                                                    <div class="d-flex align-items-start">
                                                                        <div class="d-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0"
                                                                             style="width: 40px; height: 40px; background-color: #10b981;">
                                                                            <i class="bi bi-chat-left-text text-white"></i>
                                                                        </div>
                                                                        <div>
                                                                            <small class="text-muted d-block mb-2" style="font-size: 0.75rem;">설명</small>
                                                                            <p class="mb-0 text-break">{{ $avatar->description }}</p>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endif

                                                    <!-- 등록/수정 정보 -->
                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="d-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0"
                                                                         style="width: 40px; height: 40px; background-color: #ec4899;">
                                                                        <i class="bi bi-calendar-plus text-white"></i>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">등록일</small>
                                                                        <small class="fw-semibold">{{ \Carbon\Carbon::parse($avatar->created_at)->format('Y-m-d H:i:s') }}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="col-md-6">
                                                        <div class="card border-0 shadow-sm h-100">
                                                            <div class="card-body p-3">
                                                                <div class="d-flex align-items-start">
                                                                    <div class="d-flex align-items-center justify-content-center rounded-circle me-3 flex-shrink-0"
                                                                         style="width: 40px; height: 40px; background-color: #8b5cf6;">
                                                                        <i class="bi bi-calendar-check text-white"></i>
                                                                    </div>
                                                                    <div>
                                                                        <small class="text-muted d-block mb-1" style="font-size: 0.75rem;">수정일</small>
                                                                        <small class="fw-semibold">{{ \Carbon\Carbon::parse($avatar->updated_at)->format('Y-m-d H:i:s') }}</small>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- 푸터 -->
                                            <div class="modal-footer border-0 pt-0 pb-4">
                                                <button type="button" class="btn btn-secondary shadow-sm" data-bs-dismiss="modal"
                                                        style="padding: 0.6rem 1.5rem;">
                                                    <i class="bi bi-x-circle"></i> 닫기
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-4">
                                    <i class="bi bi-inbox fs-1 text-muted"></i>
                                    <p class="text-muted mt-2">아직 등록된 아바타가 없습니다.</p>
                                    <p class="text-muted small">왼쪽에서 새 아바타를 업로드하세요.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// 파일 선택 시 상세 정보 출력
document.getElementById('avatar')?.addEventListener('change', function(e) {
    const file = e.target.files[0];

    console.log('=== Avatar File Selected ===');
    console.log('File name:', file?.name);
    console.log('File size:', file?.size, 'bytes', '(' + (file?.size / 1024).toFixed(2) + ' KB)');
    console.log('File type:', file?.type);
    console.log('Last modified:', file?.lastModified ? new Date(file.lastModified) : 'Unknown');

    // PHP 업로드 제한 확인
    const uploadMaxFilesize = '{{ ini_get("upload_max_filesize") }}';
    const postMaxSize = '{{ ini_get("post_max_size") }}';

    console.log('PHP upload_max_filesize:', uploadMaxFilesize);
    console.log('PHP post_max_size:', postMaxSize);

    // 파일 크기 체크
    const maxSizeBytes = 2 * 1024 * 1024; // 2MB (PHP 설정 기준)
    if (file && file.size > maxSizeBytes) {
        console.warn('⚠️ WARNING: File size exceeds PHP upload_max_filesize (2MB)');
        console.warn('File will likely fail to upload!');
        alert('경고: 파일 크기가 PHP 업로드 제한(2MB)을 초과합니다.\n현재 파일 크기: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
    }

    // 이미지 미리보기
    if (file && file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(event) {
            console.log('Image loaded successfully');
        };
        reader.onerror = function() {
            console.error('Failed to read image file');
        };
        reader.readAsDataURL(file);
    }
});

// 폼 제출 시 디버그 정보
document.querySelector('form[enctype="multipart/form-data"]')?.addEventListener('submit', function(e) {
    console.log('=== Form Submitting ===');

    const formData = new FormData(this);
    const file = formData.get('avatar');

    console.log('Form data:');
    for (let [key, value] of formData.entries()) {
        if (value instanceof File) {
            console.log(`  ${key}: [File] ${value.name} (${value.size} bytes)`);
        } else {
            console.log(`  ${key}: ${value}`);
        }
    }

    if (!file || file.size === 0) {
        console.error('❌ ERROR: No file selected or file is empty!');
        alert('파일이 선택되지 않았거나 비어있습니다.');
        e.preventDefault();
        return false;
    }

    console.log('Form will be submitted...');
});

// 페이지 로드 시 에러 정보 출력
document.addEventListener('DOMContentLoaded', function() {
    console.log('=== Avatar Upload Page Loaded ===');
    console.log('Current URL:', window.location.href);

    @if($errors->any())
        console.error('=== Validation Errors ===');
        @foreach($errors->all() as $error)
            console.error('- {{ $error }}');
        @endforeach
    @endif

    @if(session('error'))
        console.error('=== Session Error ===');
        console.error('{{ session("error") }}');
    @endif

    @if(session('success'))
        console.log('=== Success ===');
        console.log('{{ session("success") }}');
    @endif
});
</script>
@endpush
