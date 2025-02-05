<div class="row">
    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="simpleinput" class="form-label">
                국가명
            </label>
            <input type="text" id="simpleinput" class="form-control"
                wire:model.defer="filter.name">
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="simpleinput" class="form-label">
                코드
            </label>
            <input type="text" id="simpleinput" class="form-control"
                wire:model.defer="filter.code">
        </div>
    </div>
</div>


