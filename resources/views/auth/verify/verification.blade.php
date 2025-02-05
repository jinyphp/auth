<div>
    <x-loading-indicator/>

    <div class="mb-3">
        <label class="form-label">이메일</label>
        <input class="form-control" type="email" name="email"
            placeholder="이메일을 입력하세요"
            wire:model.defer="email" required
            autofocus>
    </div>

    <div class="d-flex justify-content-between align-items-center mb-3">
        <span class="fs-sm">
            {{ __('이메일을 받지 못하신 경우, 다른 이메일을 받을 수 있습니다.') }}
        </span>

        <button class="btn btn-primary" wire:click="resend()">
            {{ __('인증 이메일 재전송') }}
        </button>
    </div>

    <div class="mb-3">
        {{ $message }}
    </div>
</div>
