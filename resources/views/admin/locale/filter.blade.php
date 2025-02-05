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
                국가
            </label>
            <select class="form-select" wire:model="filter.country">

                <option value="">
                    국가를 선택해 주세요
                </option>

                @foreach(DB::table('user_country')->where('enable',1)->get() as $country)
                    <option value="{{$country->id}}:{{$country->name}}">
                        {{$country->id}}:{{$country->name}}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="mb-3">
            <label for="simpleinput" class="form-label">
                언어
            </label>
            <select class="form-select" wire:model="filter.language">

                <option value="">
                    언어를 선택해 주세요
                </option>

                @foreach(DB::table('user_language')->where('enable',1)->get() as $language)
                    <option value="{{$language->id}}:{{$language->name}}">
                        {{$language->id}}:{{$language->name}}
                    </option>
                @endforeach
            </select>
        </div>


    </div>
</div>


