<x-app>
    <x-bootstrap>

        <!-- 회원가입 -->
        <div class="container">

            <x-page-center>
                @includeIf('jiny-auth::regist.main',[
                    'setting'=>$setting
                    ])
            </x-page-center>

        </div>

        {{-- 관리자 제어판 --}}
        <x-set-actions></x-set-actions>
        <x-site-setting></x-site-setting>

        {{-- HotKey 단축키 이벤트 --}}
		@livewire('HotKeyEvent')

    </x-bootstrap>
</x-app>
