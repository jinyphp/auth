<div>
    <x-loading-indicator/>

    {{-- <div class="row needs-validation">
        <div class="col-lg-7">

        </div>
    </div> --}}

    {{-- 이메일 변경 --}}
    <div class="mb-3">
        <label for="profile-email" class="form-label">새로운 이메일 주소</label>
        <input type="email" class="form-control"
            id="profile-email"
            placeholder="userid@example.com" required
            wire:model.defer="forms.email"/>
        <div class="invalid-feedback">이메일 주소를 입력해 주세요.</div>
    </div>

    <div class="d-flex justify-content-end align-items-center gap-2">
        <div>
            {{$message}}
        </div>
        <button type="submit" class="btn btn-primary" wire:click="submit">변경하기</button>
    </div>
</div>
