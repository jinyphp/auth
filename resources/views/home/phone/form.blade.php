<div class="row g-3">
    <div class="col-lg-3">
        <label for="profileCountryInput" class="form-label">유형</label>
        <select class="form-select" id="profileTypeInput" required
            wire:model="form.type">
            <option selected value="">Choose...</option>
            <option value="mobile">mobile</option>
            <option value="tel">tel</option>
            <option value="fax">fax</option>
        </select>
    </div>

    <div class="col-lg-3">
        <label for="profileCountryInput" class="form-label">국가번호</label>
        {{-- <select class="form-select" id="profileCountryInput" required
            wire:model.defer="form.country">
            <option selected disabled value="">Choose...</option>
            <option selected value="+82">+82 Korea</option>
        </select> --}}
        <input type="text" class="form-control" id="profilePhoneInput" required
            wire:model.defer="form.country"/>
        <div class="invalid-feedback">국가번호를 지정합니다..</div>
    </div>

    <div class="col-lg-3">
        <label for="profilePhoneInput" class="form-label">연락처</label>
        <input type="text" class="form-control" id="profilePhoneInput" required
        wire:model.defer="form.number"/>
        <div class="invalid-feedback">전화번호를 입력해 주세요</div>
    </div>

    {{--
    <div class="col-lg-6">
        <label for="profilePhoneInput" class="form-label">설명</label>
        <input type="text" class="form-control" id="profilePhoneInput" required
        wire:model.defer="form.description"/>
    </div>
    --}}

</div>
