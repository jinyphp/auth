<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- form start -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>



            <x-form-hor>
                <x-form-label>이름</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.name")
                        ->setWidth("standard")
                    !!}
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

            {{--
            <x-form-hor>
                <x-form-label>패스워드</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.password")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>
            --}}


            @if (isset($actions['id']) && isset($roles))
            Role 권한
            <div>
                @foreach ($roles as $i => $role)
                <x-form-hor>
                    <x-form-label>{{$role['name']}}</x-form-label>
                    <x-form-item>
                        {!! xCheckbox()
                            ->setWire('model.defer',"roles.".$i.".checked")
                            ->setValue($i)
                        !!}
                    </x-form-item>
                </x-form-hor>
                @endforeach
            </div>
            @endif

        </x-navtab-item>
        <!-- form end -->
    </x-navtab>




</div>
