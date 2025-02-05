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
                <x-form-label>언어</x-form-label>
                <x-form-item>
                    <select class="form-select" wire:model="forms.name">
                        <option value="">선택</option>
                        @foreach(DB::table('language')->get() as $language)
                            <option value="{{$language->name}}">{{$language->name}}</option>
                        @endforeach
                    </select>
                </x-form-item>
            </x-form-hor>



            <x-form-hor>
                <x-form-label>코드</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.code")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>



        </x-navtab-item>


        <x-navtab-item class="">
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">사용자</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>사용자수</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.users")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>



    </x-navtab>
</div>
