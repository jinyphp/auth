<div class="mb-3">
    <label for="signupFullnameInput" class="form-label">
        이름
    </label>
    <input type="text" class="form-control" id="signupFullnameInput"
        required name="name"
        placeholder="회원명을 입력해 주세요"
        :value="old('name')" />
    <div class="invalid-feedback">이름을 입력해 주세요</div>
</div>
