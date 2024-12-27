<div class="card flex-fill">


    <div class="card-body">
        <div class="d-flex justify-content-between mb-2">
            <h5 class="card-title mb-0">이메일 검증</h5>

            @if ($row->email_verified_at)
                <button class="btn btn-sm btn-danger" wire:click="verifyCancel">검증취소</button>
            @else
                <button class="btn btn-sm btn-success" wire:click="verifyAccept">검증승인</button>
            @endif
        </div>

        <h6 class="card-subtitle text-muted">
            회원 가입을 완료하기 위해서는 이메일 검증을 통하여 확인을 해야 합니다.
        </h6>




        <div class="d-flex align-items-center gap-2 mt-3">
            <button class="btn btn-primary" wire:click="verifySend">검증 메일 재발송</button>
            <div class="text-muted">{{ $row->email_verified_at }}</div>
        </div>
    </div>
</div>
