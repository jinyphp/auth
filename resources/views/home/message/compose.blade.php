@extends('jiny-auth::layouts.home')

@section('title', '메시지 작성')

@section('content')
<div class="container mb-4">
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="h2 mb-0">메시지 작성</h1>
                <a href="{{ route('home.message.index') }}" class="btn btn-secondary">
                    <i class="fe fe-arrow-left me-2"></i>목록으로
                </a>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('home.message.send') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="to_email" class="form-label">받는사람</label>
                            <input type="email"
                                   class="form-control @error('to_email') is-invalid @enderror"
                                   id="to_email"
                                   name="to_email"
                                   value="{{ old('to_email', $toEmail ?? '') }}"
                                   placeholder="받는 사람의 이메일 주소를 입력하세요"
                                   list="userSuggestions"
                                   required>
                            <datalist id="userSuggestions">
                            </datalist>
                            @error('to_email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">수신자의 이메일 주소를 입력하세요 (자동완성 지원)</small>
                        </div>

                        <div class="mb-3">
                            <label for="subject" class="form-label">제목</label>
                            <input type="text" class="form-control @error('subject') is-invalid @enderror"
                                   id="subject" name="subject"
                                   value="{{ old('subject') }}" required>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">내용</label>
                            <textarea class="form-control @error('message') is-invalid @enderror"
                                      id="message" name="message" rows="10" required>{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('home.message.index') }}" class="btn btn-secondary">취소</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fe fe-send me-2"></i>전송
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const emailInput = document.getElementById('to_email');
    const datalist = document.getElementById('userSuggestions');
    let debounceTimer;

    emailInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();

        if (query.length < 2) {
            datalist.innerHTML = '';
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`/api/users/search?q=${encodeURIComponent(query)}`)
                .then(response => response.json())
                .then(data => {
                    datalist.innerHTML = '';
                    data.users.forEach(user => {
                        const option = document.createElement('option');
                        option.value = user.email;
                        option.textContent = `${user.name} (${user.email})`;
                        datalist.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('사용자 검색 실패:', error);
                });
        }, 300);
    });
});
</script>
@endpush
@endsection
