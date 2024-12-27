<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>승인</x-form-label>
                <x-form-item>
                    <input type="checkbox" class="form-check-input"
                        wire:model="forms.auth"
                        {{ isset($forms['auth']) && $forms['auth'] ? 'checked' : '' }}>
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

            {{-- <x-form-hor>
                <x-form-label>사용자</x-form-label>
                <x-form-item>
                    @if(isset($temp['email']))
                    {{$temp['email']}}
                    @endif
                </x-form-item>
            </x-form-hor> --}}

            <x-form-hor>
                <x-form-label>승인일자</x-form-label>
                <x-form-item>
                    {{-- {!! xInputText()
                        ->setWire('model.defer',"forms.auth_date")
                        ->setWidth("standard")
                    !!} --}}

                    <input type="datetime-local"
                        wire:model.defer="forms.auth_date"
                        class="form-control"
                        style="width: 250px;"
                        placeholder="YYYY-MM-DD HH:mm:ss">



                </x-form-item>
            </x-form-hor>

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
