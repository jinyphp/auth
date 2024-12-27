<div>
    {{$message}}

    @if(!$message)
    <div class="mb-3">
        <label class="form-label">이메일</label>
        <input class="form-control"
        type="email" id="email" name="email" placeholder="이메일을 입력하세요"
        wire:model="forms.email"
        required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label">페스워드</label>
        <input class="form-control"
        type="password" id="password" name="password"
        wire:model="forms.password">
    </div>

    {{-- <div>
        {{ $error_message }}
    </div> --}}

    <div class="d-grid gap-2 mt-3">
        <button type="submit" class="btn btn-lg btn-primary" wire:click="submit()">
            {{ __('해제요청') }}
        </button>
    </div>
    @endif
</div>
