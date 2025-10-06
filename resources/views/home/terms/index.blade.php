@extends('_layouts.home')

@section('title', '약관 동의 관리')

@push('scripts')
    <script src="{{ asset('assets/js/vendors/navbar-nav.js') }}"></script>
@endpush

@section('content')
    <div class="container mb-4">
        <div class="row mb-5">
            <div class="col-12">
                <h1 class="h2 mb-0">약관 동의 관리</h1>
                <p class="text-muted">서비스 이용약관에 대한 동의 현황을 확인하고 관리할 수 있습니다.</p>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if($requiredUnagreed->count() > 0)
            <div class="row mb-4">
                <div class="col-12">
                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <i class="fe fe-alert-triangle me-2 fs-4"></i>
                        <div>
                            <strong>필수 약관 동의 필요</strong><br>
                            서비스를 계속 이용하시려면 {{ $requiredUnagreed->count() }}개의 필수 약관에 동의해주세요.
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="row">
            <div class="col-12">
                @foreach($terms as $term)
                    <!-- Card -->
                    <div class="card mb-3 {{ $term->required && !$term->is_agreed ? 'border-warning' : '' }}">
                        <!-- Card header -->
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <div>
                                <h3 class="mb-0">
                                    {{ $term->title }}
                                    @if($term->required)
                                        <span class="badge bg-danger ms-2">필수</span>
                                    @else
                                        <span class="badge bg-secondary ms-2">선택</span>
                                    @endif
                                    @if($term->version)
                                        <span class="badge bg-info ms-1">v{{ $term->version }}</span>
                                    @endif
                                </h3>
                                @if($term->description)
                                    <p class="mb-0 text-muted">{{ $term->description }}</p>
                                @endif
                            </div>
                            <div>
                                @if($term->is_agreed)
                                    <span class="badge bg-success fs-6">
                                        <i class="fe fe-check-circle me-1"></i>
                                        동의 완료
                                    </span>
                                @else
                                    <span class="badge bg-secondary fs-6">
                                        <i class="fe fe-circle me-1"></i>
                                        미동의
                                    </span>
                                @endif
                            </div>
                        </div>
                        <!-- Card body -->
                        <div class="card-body">
                            @if($term->is_agreed)
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="d-flex align-items-start">
                                            <i class="fe fe-check-circle text-success me-2 mt-1 fs-5"></i>
                                            <div>
                                                <p class="mb-1">
                                                    <strong>동의 일시:</strong>
                                                    {{ \Carbon\Carbon::parse($term->agreed_at)->format('Y년 m월 d일 H:i') }}
                                                </p>
                                                @if($term->valid_from || $term->valid_to)
                                                    <p class="mb-0 text-muted small">
                                                        <strong>유효기간:</strong>
                                                        @if($term->valid_from)
                                                            {{ \Carbon\Carbon::parse($term->valid_from)->format('Y-m-d') }}
                                                        @else
                                                            -
                                                        @endif
                                                        ~
                                                        @if($term->valid_to)
                                                            {{ \Carbon\Carbon::parse($term->valid_to)->format('Y-m-d') }}
                                                        @else
                                                            무기한
                                                        @endif
                                                    </p>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 text-md-end">
                                        <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#termModal{{ $term->id }}">
                                            <i class="fe fe-eye me-1"></i>
                                            약관 내용 보기
                                        </button>
                                    </div>
                                </div>
                            @else
                                <div class="row">
                                    <div class="col-md-8">
                                        @if($term->required)
                                            <div class="alert alert-warning mb-3 py-2">
                                                <i class="fe fe-alert-triangle me-1"></i>
                                                이 약관은 필수 동의 항목입니다.
                                            </div>
                                        @endif
                                        <p class="text-muted mb-2">
                                            이 약관에 아직 동의하지 않으셨습니다.
                                        </p>
                                        @if($term->valid_from || $term->valid_to)
                                            <p class="mb-0 text-muted small">
                                                <strong>유효기간:</strong>
                                                @if($term->valid_from)
                                                    {{ \Carbon\Carbon::parse($term->valid_from)->format('Y-m-d') }}
                                                @else
                                                    -
                                                @endif
                                                ~
                                                @if($term->valid_to)
                                                    {{ \Carbon\Carbon::parse($term->valid_to)->format('Y-m-d') }}
                                                @else
                                                    무기한
                                                @endif
                                            </p>
                                        @endif
                                    </div>
                                    <div class="col-md-4 text-md-end mt-3 mt-md-0">
                                        <button type="button" class="btn btn-outline-secondary btn-sm me-2" data-bs-toggle="modal" data-bs-target="#termModal{{ $term->id }}">
                                            <i class="fe fe-eye me-1"></i>
                                            약관 내용 보기
                                        </button>
                                        <form action="{{ route('account.terms.agree') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="term_id" value="{{ $term->id }}">
                                            <button type="submit" class="btn btn-primary btn-sm">
                                                <i class="fe fe-check me-1"></i>
                                                동의하기
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Modal for term content -->
                    <div class="modal fade" id="termModal{{ $term->id }}" tabindex="-1" aria-labelledby="termModalLabel{{ $term->id }}" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-scrollable">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="termModalLabel{{ $term->id }}">
                                        {{ $term->title }}
                                        @if($term->version)
                                            <span class="badge bg-info ms-2">v{{ $term->version }}</span>
                                        @endif
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    @if($term->description)
                                        <div class="alert alert-light mb-3">
                                            {{ $term->description }}
                                        </div>
                                    @endif
                                    <div style="white-space: pre-wrap;">{{ $term->content }}</div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">닫기</button>
                                    @if(!$term->is_agreed)
                                        <form action="{{ route('account.terms.agree') }}" method="POST" class="d-inline">
                                            @csrf
                                            <input type="hidden" name="term_id" value="{{ $term->id }}">
                                            <button type="submit" class="btn btn-primary">
                                                <i class="fe fe-check me-1"></i>
                                                동의하기
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($terms->count() === 0)
                    <div class="card">
                        <div class="card-body text-center py-5">
                            <i class="fe fe-file-text fs-1 text-muted mb-3"></i>
                            <p class="text-muted mb-0">현재 활성화된 약관이 없습니다.</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Summary Card -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card bg-light">
                    <div class="card-body">
                        <div class="row text-center">
                            <div class="col-md-4">
                                <h3 class="mb-0">{{ $terms->count() }}</h3>
                                <p class="mb-0 text-muted">전체 약관</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="mb-0 text-success">{{ $terms->where('is_agreed', true)->count() }}</h3>
                                <p class="mb-0 text-muted">동의 완료</p>
                            </div>
                            <div class="col-md-4">
                                <h3 class="mb-0 text-{{ $requiredUnagreed->count() > 0 ? 'danger' : 'secondary' }}">
                                    {{ $terms->where('is_agreed', false)->count() }}
                                </h3>
                                <p class="mb-0 text-muted">미동의</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
