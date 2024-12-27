<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">회원탈퇴</h5>
    </div>
    <div class="card-body">

        @if ($popupDelete)
        <p>등록된 회원을 삭제하고자 합니다. 실수를 줄이기 위하여 보안코드를 입력해야 합니다.</p>

            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label class="form-label">보안코드</label>
                        <div>{{ $deleteConfirmCode }}</div>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="mb-3">
                        <label for="profile-name" class="form-label">확인코드</label>
                        <input type="text" class="form-control" id="profile-name" required
                            wire:model.defer="password" />
                    </div>
                </div>
            </div>

            @if ($message)
                <div class="alert alert-danger">
                    {{ $message }}
                </div>
            @endif

            <div class="d-flex justify-content-end">
                <button class="btn btn-danger" wire:click="deleteConfirm">탈퇴확인</button>
            </div>
        @else
            <div class="d-flex justify-content-end">
                <button class="btn btn-danger" wire:click="delete">회원탈퇴</button>
            </div>
        @endif
    </div>
</div>
