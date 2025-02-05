<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>


            <x-form-hor>
                <x-form-label>활성화</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                        wire:model="forms.enable"
                        {{ isset($forms['enable']) && $forms['enable'] ? 'checked' : '' }}>
                </x-form-item>
            </x-form-hor>


            <x-form-hor>
                <x-form-label>이메일</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.email")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>
                    <a href="/admin/auth/country">
                        국가
                    </a>
                </x-form-label>
                <x-form-item>
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
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>State</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.state")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Region</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.region")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>주소1</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.address1")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>주소2</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.address2")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>우편번호</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.zipcode")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>




        </x-navtab-item>




        <!-- Tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">메모</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>메모</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"forms.description")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>
        <!-- Tab end -->

    </x-navtab>
</div>
