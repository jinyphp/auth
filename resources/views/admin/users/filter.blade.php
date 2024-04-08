<div class="row">
    <div class="col-6">
        <div class="mb-3">
            <label for="simpleinput" class="form-label">국가</label>
            <input type="text" id="simpleinput" class="form-control" wire:model.defer="filter.country">
        </div>

        <div class="mb-3">
            <label for="simpleinput" class="form-label">이메일</label>
            <input type="text" id="simpleinput" class="form-control" wire:model.defer="filter.email">
        </div>
    </div>
    <div class="col-6">
        <input type="text" wire:model="filter.starts_at"/>

    </div>
</div>


