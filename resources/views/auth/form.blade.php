@extends('layouts.app')

@section('title', '회원가입')

@section('content')
<div class="min-h-screen flex items-center justify-center bg-gray-50 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-2xl w-full space-y-8">
        {{-- 헤더 --}}
        <div>
            <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900">
                회원가입
            </h2>
            <p class="mt-2 text-center text-sm text-gray-600">
                이미 계정이 있으신가요?
                <a href="{{ route('login') }}" class="font-medium text-indigo-600 hover:text-indigo-500">
                    로그인하기
                </a>
            </p>
        </div>

        {{-- 소셜 로그인 --}}
        @if(!empty($social_providers))
        <div class="mt-6">
            <div class="relative">
                <div class="absolute inset-0 flex items-center">
                    <div class="w-full border-t border-gray-300"></div>
                </div>
                <div class="relative flex justify-center text-sm">
                    <span class="px-2 bg-gray-50 text-gray-500">또는 소셜 계정으로</span>
                </div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-3">
                @foreach($social_providers as $provider => $config)
                <a href="{{ route('register.social', $provider) }}"
                   class="w-full inline-flex justify-center py-2 px-4 border border-gray-300 rounded-md shadow-sm bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                    {{ ucfirst($provider) }}
                </a>
                @endforeach
            </div>
        </div>
        @endif

        {{-- 회원가입 폼 --}}
        <form class="mt-8 space-y-6" action="{{ route('register.submit') }}" method="POST">
            @csrf

            {{-- 에러 메시지 --}}
            @if(session('error'))
            <div class="rounded-md bg-red-50 p-4">
                <div class="text-sm text-red-700">{{ session('error') }}</div>
            </div>
            @endif

            {{-- 약관 동의 섹션 --}}
            <div class="bg-white shadow rounded-lg p-6">
                <h3 class="text-lg font-medium text-gray-900 mb-4">약관 동의</h3>

                {{-- 전체 동의 --}}
                <div class="mb-4">
                    <label class="flex items-center">
                        <input type="checkbox" id="agree-all" class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                        <span class="ml-2 text-sm font-semibold text-gray-900">전체 동의</span>
                    </label>
                </div>

                <div class="border-t border-gray-200 pt-4">
                    {{-- 필수 약관 --}}
                    @foreach($terms['mandatory'] as $term)
                    <div class="mb-3">
                        <label class="flex items-start">
                            <input type="checkbox" name="terms[]" value="{{ $term->id }}"
                                   class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 term-checkbox mandatory-term"
                                   required>
                            <span class="ml-2 text-sm text-gray-700">
                                <span class="text-red-600 font-semibold">[필수]</span>
                                {{ $term->title }}
                                <a href="#" class="text-indigo-600 hover:text-indigo-500 ml-2"
                                   onclick="showTermsModal({{ $term->id }}); return false;">
                                    보기
                                </a>
                            </span>
                        </label>
                    </div>
                    @endforeach

                    {{-- 선택 약관 --}}
                    @foreach($terms['optional'] as $term)
                    <div class="mb-3">
                        <label class="flex items-start">
                            <input type="checkbox" name="terms[]" value="{{ $term->id }}"
                                   class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500 term-checkbox">
                            <span class="ml-2 text-sm text-gray-700">
                                <span class="text-gray-500">[선택]</span>
                                {{ $term->title }}
                                <a href="#" class="text-indigo-600 hover:text-indigo-500 ml-2"
                                   onclick="showTermsModal({{ $term->id }}); return false;">
                                    보기
                                </a>
                            </span>
                        </label>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- 기본 정보 입력 --}}
            <div class="bg-white shadow rounded-lg p-6 space-y-4">
                <h3 class="text-lg font-medium text-gray-900 mb-4">기본 정보</h3>

                {{-- 이름 --}}
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">
                        이름 <span class="text-red-600">*</span>
                    </label>
                    <input id="name" name="name" type="text" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('name') }}">
                    @error('name')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 이메일 --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700">
                        이메일 <span class="text-red-600">*</span>
                    </label>
                    <input id="email" name="email" type="email" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('email') }}">
                    @error('email')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 비밀번호 --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700">
                        비밀번호 <span class="text-red-600">*</span>
                    </label>
                    <input id="password" name="password" type="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <p class="mt-1 text-xs text-gray-500">
                        최소 {{ $password_rules['min_length'] }}자 이상,
                        @if($password_rules['require_uppercase']) 대문자, @endif
                        @if($password_rules['require_lowercase']) 소문자, @endif
                        @if($password_rules['require_numbers']) 숫자, @endif
                        @if($password_rules['require_symbols']) 특수문자 @endif
                        포함
                    </p>
                    @error('password')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- 비밀번호 확인 --}}
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700">
                        비밀번호 확인 <span class="text-red-600">*</span>
                    </label>
                    <input id="password_confirmation" name="password_confirmation" type="password" required
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                </div>

                {{-- 전화번호 (선택적) --}}
                @if($form_config['show_phone_field'])
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700">
                        전화번호
                    </label>
                    <input id="phone" name="phone" type="tel"
                           placeholder="010-1234-5678"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('phone') }}">
                    @error('phone')
                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                @endif

                {{-- 생년월일 (선택적) --}}
                @if($form_config['show_birth_date_field'])
                <div>
                    <label for="birth_date" class="block text-sm font-medium text-gray-700">
                        생년월일
                    </label>
                    <input id="birth_date" name="birth_date" type="date"
                           class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                           value="{{ old('birth_date') }}">
                </div>
                @endif

                {{-- 성별 (선택적) --}}
                @if($form_config['show_gender_field'])
                <div>
                    <label class="block text-sm font-medium text-gray-700">성별</label>
                    <div class="mt-2 space-x-4">
                        <label class="inline-flex items-center">
                            <input type="radio" name="gender" value="M" class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2">남성</span>
                        </label>
                        <label class="inline-flex items-center">
                            <input type="radio" name="gender" value="F" class="rounded-full border-gray-300 text-indigo-600 focus:ring-indigo-500">
                            <span class="ml-2">여성</span>
                        </label>
                    </div>
                </div>
                @endif
            </div>

            {{-- reCAPTCHA --}}
            @if($form_config['recaptcha_enabled'])
            <div class="g-recaptcha" data-sitekey="{{ $form_config['recaptcha_site_key'] }}"></div>
            @endif

            {{-- 제출 버튼 --}}
            <div>
                <button type="submit"
                        class="group relative w-full flex justify-center py-2 px-4 border border-transparent text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                    회원가입
                </button>
            </div>
        </form>
    </div>
</div>

{{-- 약관 모달 --}}
<div id="terms-modal" class="hidden fixed inset-0 bg-gray-500 bg-opacity-75 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-2xl w-full max-h-screen overflow-y-auto">
        <div class="flex justify-between items-center mb-4">
            <h3 id="modal-title" class="text-lg font-medium"></h3>
            <button onclick="closeTermsModal()" class="text-gray-400 hover:text-gray-500">
                <span class="sr-only">닫기</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
        <div id="modal-content" class="prose max-w-none"></div>
    </div>
</div>

@push('scripts')
<script>
// 전체 동의 체크박스
document.getElementById('agree-all')?.addEventListener('change', function(e) {
    const checkboxes = document.querySelectorAll('.term-checkbox');
    checkboxes.forEach(cb => cb.checked = e.target.checked);
});

// 약관 모달 표시
function showTermsModal(termId) {
    const terms = @json($terms['all']);
    const term = terms.find(t => t.id === termId);

    if (term) {
        document.getElementById('modal-title').textContent = term.title;
        document.getElementById('modal-content').innerHTML = term.content;
        document.getElementById('terms-modal').classList.remove('hidden');
    }
}

// 약관 모달 닫기
function closeTermsModal() {
    document.getElementById('terms-modal').classList.add('hidden');
}

// 폼 제출 시 필수 약관 확인
document.querySelector('form').addEventListener('submit', function(e) {
    const mandatoryTerms = document.querySelectorAll('.mandatory-term');
    const allChecked = Array.from(mandatoryTerms).every(cb => cb.checked);

    if (!allChecked) {
        e.preventDefault();
        alert('필수 약관에 모두 동의해야 합니다.');
        return false;
    }
});

@if($form_config['recaptcha_enabled'] && $form_config['recaptcha_version'] === 'v3')
// reCAPTCHA v3
grecaptcha.ready(function() {
    grecaptcha.execute('{{ $form_config['recaptcha_site_key'] }}', {action: 'register'}).then(function(token) {
        const form = document.querySelector('form');
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'g-recaptcha-response';
        input.value = token;
        form.appendChild(input);
    });
});
@endif
</script>

@if($form_config['recaptcha_enabled'])
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
@endif
@endpush
@endsection