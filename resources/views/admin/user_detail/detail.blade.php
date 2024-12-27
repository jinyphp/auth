<div>
    <x-loading-indicator/>

    <div class="row needs-validation">
        <div class="col-lg-6">
            <div class="mb-3">
                <label for="profile-name" class="form-label">이름</label>
                <input type="text" class="form-control"
                    id="profile-name" required
                    wire:model.defer="forms.name"/>
            </div>

            <div class="mb-3">
                <label for="profile-email" class="form-label">새로운 이메일 주소</label>
                <input type="email" class="form-control"
                    id="profile-email"
                    placeholder="userid@example.com" required
                    wire:model.defer="forms.email"/>
                <div class="invalid-feedback">이메일 주소를 입력해 주세요.</div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="mb-3">
                <label for="profile-name" class="form-label">
                    <a href="/admin/auth/country">
                        국가
                    </a>
                </label>
                <select class="form-select"
                    wire:model.defer="forms.country">
                    @if(!isset($forms['country']))
                        <option value="">국가 선택</option>
                    @endif
                    @foreach(DB::table('user_country')
                        ->where('enable',1)
                        ->orderBy('name')->get() as $country)
                        <option value="{{$country->id}}:{{$country->name}}">
                            {{ $country->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-3">
                <label for="profile-name" class="form-label">
                    <a href="/admin/auth/language">
                    언어
                    </a>
                </label>
                <select class="form-select"
                    wire:model.defer="forms.language">
                    @if(!isset($forms['language']))
                        <option value="">언어 선택</option>
                    @endif
                    @foreach(DB::table('user_language')
                        ->where('enable',1)
                        ->orderBy('name')->get() as $language)
                        <option value="{{$language->id}}:{{$language->name}}">
                            {{ $language->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
    </div>

    <div class="mb-3">
        <label for="profile-name" class="form-label">
            <a href="/admin/auth/grade">
                회원등급
            </a>
        </label>
        {{-- <input type="text" class="form-control" wire:model.defer="forms.grade"/> --}}
        <select class="form-select" wire:model.defer="forms.grade">
            @if(!isset($forms['grade']))
                <option value="">회원 등급을 선택해 주세요</option>
            @endif
            @foreach(DB::table('user_grade')
                ->where('enable',1)
                ->orderBy('name')->get() as $grade)
                <option value="{{$grade->id}}:{{$grade->name}}">
                    {{ $grade->name }}
                </option>
            @endforeach
        </select>
    </div>


    <div class="d-flex justify-content-end">
        <button type="submit" class="btn btn-primary" wire:click="submit">변경하기</button>
    </div>
</div>
