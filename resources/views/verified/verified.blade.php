<x-app>
    <x-bootstrap>
        <div class="d-flex justify-content-center align-items-center flex-column"
            style="height: 100vh;">

            <div class="text-center mt-4">
                <h1 class="h2">이메일 검증</h1>
                <p class="lead"></p>
            </div>

            <div class="card">

                <div class="card-body">

                    <div class="text-center">

                        <div class="mb-4 text-sm text-gray-600">
                            {{ __('계속하기 전에 방금 이메일로 보내드린 링크를 클릭하여 이메일 주소를 확인해 주시겠습니까?') }}
                            <br>
                            {{__('이메일을 받지 못하신 경우, 다른 이메일을 받을 수 있습니다.')}}
                        </div>

                        @if (session('status') == 'verification-link-sent')
                            <div class="mb-4 font-medium text-sm text-green-600">
                                {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
                            </div>
                        @endif

                        @livewire('EmailVerificationNotification')

                    </div>
                </div>



            </div>

            <div>
                <a href="/login">로그인</a>
            </div>


            <div class="text-center mb-3">
                Copyright all right reserved JinyPHP
            </div>

        </div>
    </x-bootstrap>
</x-app>

