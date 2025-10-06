@extends('jiny-auth::layouts.auth')

@section('title', '약관 동의')

@section('content')
<section class="container d-flex flex-column">
    <div class="row align-items-center justify-content-center g-0 min-vh-100 py-8">
        <div class="col-lg-7 col-md-10 py-8 py-xl-0">
            <!-- Card -->
            <div class="card shadow">
                <!-- Card body -->
                <div class="card-body p-6">
                    <div class="mb-4">
                        <a href="/"><img src="{{ asset('assets/images/brand/logo/logo-icon.svg') }}" class="mb-4" alt="logo-icon" /></a>
                        <h1 class="mb-1 fw-bold">약관 동의</h1>
                        <span>회원가입을 위해 약관에 동의해주세요.</span>
                    </div>

                    <form action="{{ route('register.store') }}" method="POST">
                        @csrf

                        <!-- Terms Agreement -->
                        @if(isset($terms['all']) && count($terms['all']) > 0)
                        <div class="mb-4">
                            @foreach($terms['all'] as $term)
                            <div class="card mb-3">
                                <div class="card-header">
                                    <div class="form-check">
                                        <input type="checkbox" class="form-check-input term-checkbox" 
                                               id="terms_{{ $term->id }}" name="terms[{{ $term->id }}]"
                                               {{ $term->is_mandatory ? 'required' : '' }}>
                                        <label class="form-check-label" for="terms_{{ $term->id }}">
                                            @if($term->is_mandatory)
                                                <span class="badge bg-danger me-1">필수</span>
                                            @else
                                                <span class="badge bg-secondary me-1">선택</span>
                                            @endif
                                            <strong>{{ $term->title }}</strong>
                                        </label>
                                    </div>
                                </div>
                                <div class="card-body" style="max-height: 200px; overflow-y: auto;">
                                    <div class="small text-muted">
                                        {!! $term->content !!}
                                    </div>
                                </div>
                            </div>
                            @endforeach

                            <div class="form-check mb-3">
                                <input type="checkbox" class="form-check-input" id="agreeAll">
                                <label class="form-check-label fw-bold" for="agreeAll">
                                    전체 동의
                                </label>
                            </div>
                        </div>
                        @else
                        <!-- Default Terms -->
                        <div class="mb-4">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="terms_default" name="terms[0]" required>
                                <label class="form-check-label" for="terms_default">
                                    <a href="#" target="_blank">이용약관</a> 및 <a href="#" target="_blank">개인정보처리방침</a>에 동의합니다.
                                </label>
                                <div class="invalid-feedback">약관에 동의해주세요.</div>
                            </div>
                        </div>
                        @endif

                        <!-- Buttons -->
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">다음 단계</button>
                            <a href="{{ route('login') }}" class="btn btn-outline-secondary">취소</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

@push('scripts')
<script>
    // 전체 동의 체크박스
    document.getElementById('agreeAll')?.addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.term-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.checked = this.checked;
        });
    });
</script>
@endpush

@endsection
