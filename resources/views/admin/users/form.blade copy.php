<div>
    @if (session()->has('error'))
        <div class="alert alert-danger">{{session('error')}}</div>
    @endif

    <x-navtab class="mb-3 nav-bordered">

        <!-- form start -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">기본정보</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>국가</x-form-label>
                <x-form-item>
                    {!! xSelect()
                        ->table('user_country','name')
                        ->setWire('model.defer',"forms.country")
                        ->setWidth("medium")
                    !!}

                    {{-- {!! xInputText()
                        ->setWire('model.defer',"forms.country")
                        ->setWidth("standard")
                    !!} --}}
                </x-form-item>
            </x-form-hor>

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


            <x-form-hor>
                <x-form-label>패스워드</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.password")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>



        </x-navtab-item>
        <!-- form end -->

        @if (isset($actions['id']))
        <x-navtab-item>
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">권환설정</span>
            </x-navtab-link>

            
            @if (isset($roles) && count($roles)>0)
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
            @else
            <div>
                역할관리가 등록되어 있지 않습니다.
            </div>
            @endif

        </x-navtab-item>
        @endif

        <x-navtab-item>
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">접속설정</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>회원승인</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.auth")
                    !!}
                    <p>로그인 접속을 승인합니다.</p>
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>만료일자</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.expire")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>휴면계정</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.sleeper")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

        </x-navtab>
    </x-navtab>




</div>
