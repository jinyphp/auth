<div>
    <div class="mb-3">
        <label class="form-check">
            <input class="form-check-input" type="checkbox" @if (isset($forms['enable']) && $forms['enable']) checked @endif
                wire:model.defer="forms.enable">
            <span class="form-check-label">
                페스워드 기간을 적용합니다.
            </span>
        </label>
    </div>

    <div class="mb-3">
        <label class="form-label">만료일자</label>
        <input type="date" class="form-control" wire:model.defer="forms.expire">
    </div>

    <div class="d-flex justify-content-end gap-2">
        <button class="btn btn-secondary" wire:click="cancel">취소</button>
        <button class="btn btn-primary" wire:click="saveUpdate">적용</button>
    </div>

</div>
