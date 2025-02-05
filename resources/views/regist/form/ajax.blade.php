<form method="POST" action="{{ route('regist.create') }}"
    class="needs-validation mb-6"
    novalidate>
    @csrf

    {{-- 회원명 --}}
    <div class="mb-3">
        <label for="signupFullnameInput" class="form-label">
            이름
        </label>
        <input type="text" required name="name" class="form-control" id="signupFullnameInput"
            placeholder="회원명을 입력해 주세요" :value="old('name')" />
        <div class="invalid-feedback">이름을 입력해 주세요</div>
    </div>

    {{-- 이메일 --}}
    <div class="mb-3">
        <label class="form-label">이메일</label>
        <input type="email" name="email" class="form-control" placeholder="이메일을 입력하세요" :value="old('email')"
            required autofocus>
        <div class="invalid-feedback">이메일을 입력해 주세요</div>
    </div>

    <div class="mb-3">
        <label class="form-label">비밀번호</label>
        <input class="form-control" type="password" name="password" placeholder="비밀번호를 입력하세요" required>

        <div class="invalid-feedback">비밀번호를 입력해 주세요</div>
    </div>

    <div class="mb-3">
        <label class="form-label">비밀번호 확인</label>
        <input class="form-control" type="password" name="password_confirmation" placeholder="비밀번호를 입력하세요" required>

        <div class="invalid-feedback">확인 비밀번호를 입력해주세요</div>
    </div>

    <div class="d-grid">
        <button type="submit" class="btn btn-primary btn-lg w-100">
            {{ __('가입신청') }}
        </button>
    </div>

</form>
