<div>
    <div class="mb-3">
        <label class="form-label">성</label>
        <input type="text" class="form-control" wire:model="forms.firstname">
    </div>

    <div class="mb-3">
        <label class="form-label">이름</label>
        <input type="text" class="form-control"
            wire:model="forms.lastname">
    </div>

    <div class="mb-3">
        <label class="form-label">스킬</label>
        <textarea name="textarea" rows="5" class="form-control"
            wire:model="forms.skill">

        </textarea>
    </div>

    <div class="mb-3">
        <label class="form-label">프로필 소개</label>
        <textarea name="textarea" rows="5" class="form-control"
            wire:model="forms.description">

        </textarea>
    </div>

    <div class="d-flex justify-content-between">
        <div class="text-success">
            {{ $message }}
        </div>
        <button class="btn btn-primary" wire:click="update">변경</button>
    </div>

</div>
