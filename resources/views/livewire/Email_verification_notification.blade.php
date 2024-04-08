<div>
    <div class="mb-3 row">
        <label class="col-form-label col-sm-2 text-sm-end">Email</label>
        <div class="col-sm-5">
            <input type="email" class="form-control" wire:model.defer="email">
        </div>
        <div class="col-sm-5">
            <button class="btn btn-primary" wire:click="resend()">
                {{ __('Resend Verification Email') }}
            </button>
        </div>
    </div>
    <div>{{$message}}</div>
</div>
