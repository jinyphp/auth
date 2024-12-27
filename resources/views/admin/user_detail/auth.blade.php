<div class="card flex-fill">
    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <h5 class="card-title mb-0">회원 승인</h5>


            @if ($auth->auth)
                <div class="d-flex gap-2">
                    <div class="text-muted">{{ $auth->updated_at }}</div>
                    <button class="btn btn-sm btn-danger" wire:click="authCancel">승인취소</button>
                </div>
            @else
                <div class="d-flex gap-2">
                    <div class="text-muted">{{ $auth->updated_at }}</div>
                    <button class="btn btn-sm btn-success" wire:click="authAccept">가입승인</button>
                </div>
            @endif
        </div>

        <h6 class="card-subtitle text-muted">
            회원 접속을 하기 위해서는 승인절차가 필요로 합니다.
        </h6>

        <div class="mt-2">
            {{ $auth->description }}
        </div>
    </div>
</div>
