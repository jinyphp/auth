<div>
    <div class="mb-3">
        <label class="form-label">Password</label>
        <input type="password" class="form-control"
            placeholder="Password"
            wire:model="password">
    </div>

    <div class="d-flex justify-content-between">
        <div>
            @if($message)
            <div class="text-{{ $message['type'] }}">
                {{ $message['message'] }}
            </div>
            @endif
        </div>

        <button class="btn btn-primary" wire:click="update">변경</button>
    </div>

</div>
