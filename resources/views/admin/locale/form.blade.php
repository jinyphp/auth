<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active">
            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>사용자</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.email")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>국가</x-form-label>
                <x-form-item>
                    <select class="form-select" wire:model="forms.country">
                        @if(!isset($forms['country']))
                            <option value="">
                                국가를 선택해 주세요
                            </option>
                        @endif
                        @foreach(DB::table('user_country')->where('enable',1)->get() as $country)
                            <option value="{{$country->id}}:{{$country->name}}">
                                {{$country->id}}:{{$country->name}}
                            </option>
                        @endforeach
                    </select>
                </x-form-item>
            </x-form-hor>


            <x-form-hor>
                <x-form-label>언어</x-form-label>
                <x-form-item>
                    <select class="form-select" wire:model="forms.language">
                        @if(!isset($forms['language']))
                            <option value="">
                                언어를 선택해 주세요
                            </option>
                        @endif
                        @foreach(DB::table('user_language')->where('enable',1)->get() as $language)
                            <option value="{{$language->id}}:{{$language->name}}">
                                {{$language->id}}:{{$language->name}}
                            </option>
                        @endforeach
                    </select>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

        <x-navtab-item class="">
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">설명</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>내용</x-form-label>
                <x-form-item>
                    {!! xTextarea()
                        ->setWire('model.defer',"forms.description")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

    </x-navtab>
</div>
