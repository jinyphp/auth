@extends('jiny-auth::layouts.site')

@section('title', $term->title . ' - 이용약관')

@section('content')
    <div class="py-4 bg-white border-bottom">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-3">
                            <li class="breadcrumb-item"><a href="/" class="text-decoration-none">홈</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('site.terms.index') }}" class="text-decoration-none">이용약관</a></li>
                            <li class="breadcrumb-item active" aria-current="page">{{ $term->title }}</li>
                        </ol>
                    </nav>
                    <h1 class="h2 fw-bold mb-3">{{ $term->title }}</h1>
                    <div class="d-flex align-items-center gap-3 text-muted small">
                        <div class="d-flex gap-2">
                            @if($term->required)
                                <span class="badge bg-danger">필수</span>
                            @else
                                <span class="badge bg-secondary">선택</span>
                            @endif
                            @if($term->version)
                                <span class="badge bg-info">버전 {{ $term->version }}</span>
                            @endif
                        </div>
                        <span>|</span>
                        <span>최종 수정일: {{ \Carbon\Carbon::parse($term->updated_at)->format('Y년 m월 d일') }}</span>
                        @if($term->valid_from || $term->valid_to)
                            <span>|</span>
                            <span>
                                유효기간:
                                @if($term->valid_from)
                                    {{ \Carbon\Carbon::parse($term->valid_from)->format('Y.m.d') }}
                                @else
                                    -
                                @endif
                                ~
                                @if($term->valid_to)
                                    {{ \Carbon\Carbon::parse($term->valid_to)->format('Y.m.d') }}
                                @else
                                    무기한
                                @endif
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="py-5 bg-light">
        <div class="container">
            <div class="row">
                <div class="col-lg-9 mx-auto">
                    <!-- 약관 내용 -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div class="card-body p-5">
                            @if($term->description)
                                <div class="alert alert-light border mb-4">
                                    <p class="mb-0 text-muted">{{ $term->description }}</p>
                                </div>
                            @endif

                            <article class="terms-content">
                                {{ $term->content }}
                            </article>
                        </div>
                    </div>

                    <!-- 동의 안내 -->
                    @auth
                        <div class="card border-0 bg-primary bg-opacity-10 mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fe fe-check-circle text-primary fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-2">약관 동의 관리</h5>
                                        <p class="mb-3 text-muted">
                                            이 약관에 대한 동의 여부를 확인하고 관리하실 수 있습니다.
                                        </p>
                                        <a href="{{ route('account.terms.index') }}" class="btn btn-primary">
                                            내 약관 관리 페이지로 이동
                                            <i class="fe fe-arrow-right ms-1"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="card border mb-4">
                            <div class="card-body p-4">
                                <div class="d-flex align-items-start">
                                    <div class="flex-shrink-0">
                                        <i class="fe fe-info text-primary fs-2"></i>
                                    </div>
                                    <div class="flex-grow-1 ms-3">
                                        <h5 class="mb-2">약관 동의가 필요하신가요?</h5>
                                        <p class="mb-3 text-muted">
                                            약관에 동의하고 서비스를 이용하시려면 먼저 로그인이 필요합니다.
                                        </p>
                                        <div class="d-flex gap-2">
                                            <a href="/login" class="btn btn-primary">
                                                로그인
                                            </a>
                                            <a href="/register" class="btn btn-outline-primary">
                                                회원가입
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endauth

                    <!-- 목록으로 -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('site.terms.index') }}" class="btn btn-outline-secondary">
                            <i class="fe fe-arrow-left me-2"></i>
                            약관 목록으로
                        </a>
                        <div class="text-muted small">
                            <i class="fe fe-calendar me-1"></i>
                            작성일: {{ \Carbon\Carbon::parse($term->created_at)->format('Y년 m월 d일') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
/* 약관 콘텐츠 스타일 */
.terms-content {
    font-size: 16px;
    line-height: 1.75;
    color: #1d1d1f;
    white-space: pre-wrap;
    word-wrap: break-word;
}

/* 제목 스타일 */
.terms-content h1 {
    font-size: 2rem;
    font-weight: 700;
    margin-top: 3rem;
    margin-bottom: 1.5rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid #e5e5e5;
    color: #1d1d1f;
}

.terms-content h2 {
    font-size: 1.5rem;
    font-weight: 600;
    margin-top: 2.5rem;
    margin-bottom: 1.25rem;
    color: #1d1d1f;
}

.terms-content h3 {
    font-size: 1.25rem;
    font-weight: 600;
    margin-top: 2rem;
    margin-bottom: 1rem;
    color: #1d1d1f;
}

.terms-content h4 {
    font-size: 1.125rem;
    font-weight: 600;
    margin-top: 1.5rem;
    margin-bottom: 0.875rem;
    color: #424245;
}

.terms-content h5,
.terms-content h6 {
    font-size: 1rem;
    font-weight: 600;
    margin-top: 1.25rem;
    margin-bottom: 0.75rem;
    color: #424245;
}

/* 첫 번째 제목 여백 조정 */
.terms-content h1:first-child,
.terms-content h2:first-child,
.terms-content h3:first-child {
    margin-top: 0;
}

/* 단락 스타일 */
.terms-content p {
    margin-bottom: 1.25rem;
    color: #424245;
}

/* 목록 스타일 */
.terms-content ul,
.terms-content ol {
    margin-bottom: 1.25rem;
    padding-left: 2rem;
    color: #424245;
}

.terms-content li {
    margin-bottom: 0.5rem;
    line-height: 1.75;
}

.terms-content ul li {
    list-style-type: disc;
}

.terms-content ol li {
    list-style-type: decimal;
}

/* 중첩 목록 */
.terms-content ul ul,
.terms-content ol ul {
    margin-top: 0.5rem;
    margin-bottom: 0.5rem;
}

/* 인용문 스타일 */
.terms-content blockquote {
    margin: 1.5rem 0;
    padding: 1rem 1.5rem;
    border-left: 4px solid #0071e3;
    background-color: #f5f5f7;
    color: #424245;
}

/* 코드 스타일 */
.terms-content code {
    padding: 0.2rem 0.4rem;
    font-size: 0.9em;
    background-color: #f5f5f7;
    border-radius: 3px;
    font-family: 'SF Mono', Monaco, 'Courier New', monospace;
}

.terms-content pre {
    margin: 1.5rem 0;
    padding: 1rem;
    background-color: #f5f5f7;
    border-radius: 8px;
    overflow-x: auto;
}

.terms-content pre code {
    padding: 0;
    background-color: transparent;
}

/* 구분선 */
.terms-content hr {
    margin: 2rem 0;
    border: 0;
    border-top: 1px solid #d2d2d7;
}

/* 링크 스타일 */
.terms-content a {
    color: #0071e3;
    text-decoration: none;
}

.terms-content a:hover {
    text-decoration: underline;
}

/* 강조 */
.terms-content strong,
.terms-content b {
    font-weight: 600;
    color: #1d1d1f;
}

.terms-content em,
.terms-content i {
    font-style: italic;
}

/* 테이블 스타일 */
.terms-content table {
    width: 100%;
    margin: 1.5rem 0;
    border-collapse: collapse;
}

.terms-content th,
.terms-content td {
    padding: 0.75rem 1rem;
    border: 1px solid #d2d2d7;
    text-align: left;
}

.terms-content th {
    background-color: #f5f5f7;
    font-weight: 600;
    color: #1d1d1f;
}

/* 섹션 번호 */
.terms-content .section-number {
    color: #86868b;
    margin-right: 0.5rem;
}

/* 반응형 */
@media (max-width: 768px) {
    .terms-content {
        font-size: 15px;
    }

    .terms-content h1 {
        font-size: 1.75rem;
    }

    .terms-content h2 {
        font-size: 1.375rem;
    }

    .terms-content h3 {
        font-size: 1.125rem;
    }
}
</style>
@endpush
