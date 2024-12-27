<div>
    @if($status)
    <div class="alert alert-success">
        {{ $message }}
    </div>
    @else
    <button class="btn btn-info" wire:click="sendResetLink">
        초기화 메일 발송
    </button>
    @endif
</div>
