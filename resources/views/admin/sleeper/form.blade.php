<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>사용자</x-form-label>
                <x-form-item>
                    {{$temp['email']}}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>만기일자</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.expire_date")
                        ->setWidth("standard")
                    !!}

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
