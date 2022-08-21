<div>
    <x-navtab class="mb-3 nav-bordered">

        <!-- formTab -->
        <x-navtab-item class="show active" >

            <x-navtab-link class="rounded-0 active">
                <span class="d-none d-md-block">구글</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>구글 OAuth</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.google.enable")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Client Id</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.google.clinet_id")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Secret</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.google.client_secret")
                        ->setWidth("standard")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>callback</x-form-label>
                <x-form-item>
                    <p>http://도메인/login/google/callback</p>
                </x-form-item>
            </x-form-hor>



        </x-navtab-item>
        <!-- tab end -->


        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">페이스북</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>페이스북 OAuth</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.facebook.enable")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Client Id</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.facebook.clinet_id")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Secret</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.facebook.client_secret")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>callback</x-form-label>
                <x-form-item>
                    <p>http://도메인/login/facebook/callback</p>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>

        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">네이버</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>네이버 OAuth</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.naver.enable")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Client Id</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.naver.clinet_id")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Secret</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.naver.client_secret")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>callback</x-form-label>
                <x-form-item>
                    <p>http://도메인/login/naver/callback</p>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>


        <!-- tab start -->
        <x-navtab-item >
            <x-navtab-link class="rounded-0">
                <span class="d-none d-md-block">카카오</span>
            </x-navtab-link>

            <x-form-hor>
                <x-form-label>카카오 OAuth</x-form-label>
                <x-form-item>
                    {!! xCheckbox()
                        ->setWire('model.defer',"forms.kakao.enable")
                    !!}
                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Client Id</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.kakao.clinet_id")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>Secret</x-form-label>
                <x-form-item>
                    {!! xInputText()
                        ->setWire('model.defer',"forms.kakao.client_secret")
                        ->setWidth("standard")
                    !!}

                </x-form-item>
            </x-form-hor>

            <x-form-hor>
                <x-form-label>callback</x-form-label>
                <x-form-item>
                    <p>http://도메인/login/kakao/callback</p>
                </x-form-item>
            </x-form-hor>

        </x-navtab-item>




    </x-navtab>
</div>
