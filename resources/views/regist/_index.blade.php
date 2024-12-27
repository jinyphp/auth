<x-app>
    <x-bootstrap>
        <x-page-center>
            <div class="text-center mt-4">
                <h1 class="h2">회원 가입신청</h1>
                <p class="lead"></p>
            </div>

            <div class="card">
                <div class="card-body">

                    @includeIf('jiny-auth::regist.form',[
                        'setting'=>$setting
                        ])

                    <div class="text-center">
                        <a href="/login">로그인</a>
                    </div>

                    @includeIf('jiny-auth::regist.social',[
                        'setting'=>$setting
                        ])

                </div>
            </div>
            <div class="text-center mb-3">
                Copyright all right reserved JinyPHP
            </div>
        </x-page-center>
    </x-bootstrap>
</x-app>

