<div class="mb-3">
    <label class="form-label">이메일</label>
    <input class="form-control"
        type="email"
        name="email"
        placeholder="이메일을 입력하세요"
        :value="old('email')"
        required
        autofocus>

    {{$slot}}
</div>
