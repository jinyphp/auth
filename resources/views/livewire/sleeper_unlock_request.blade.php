<div>
    {{$message}}

    @if(!$message)
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control form-control-lg"
        type="email" id="email" name="email" placeholder="Enter your email"
        wire:model="forms.email"
        required autofocus>
    </div>

    <div class="mb-3">
        <label class="form-label">Password</label>
        <input class="form-control form-control-lg"
        type="password" id="password" name="password"
        wire:model="forms.password">
    </div>

    <div>

        {{ $error_message }}
    </div>

    <div class="d-grid gap-2 mt-3">
        <button type="submit" class="btn btn-lg btn-primary" wire:click="submit()">
            {{ __('해제요청') }}
        </button>
    </div>
    @endif
</div>
