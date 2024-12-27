<div class="row">
    <div class="col-12 col-md-6">


        <div class="mb-3">
            <label for="simpleinput" class="form-label">
                이메일
            </label>
            <input type="text" id="simpleinput" class="form-control"
                wire:model.defer="filter.email">
        </div>

        <div class="mb-3">
            <label for="simpleinput" class="form-label">
                이름
            </label>
            <input type="text" id="simpleinput" class="form-control"
                wire:model.defer="filter.name">
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="mb-3">
            <label for="simpleinput" class="form-label">
                <a href="/admin/auth/country">
                    국가
                </a>
            </label>
            <select class="form-control" wire:model.defer="filter.country">
                <option value="">전체</option>
                @foreach (DB::table('user_country')->where('enable', 1)->get() as $country)
                    <option value="{{$country->id}}:{{$country->name}}">{{$country->name}}</option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="simpleinput" class="form-label">가입일자</label>
            <x-flatpickr-date wire:model="filter.created_at"/>
        </div>
    </div>
</div>


