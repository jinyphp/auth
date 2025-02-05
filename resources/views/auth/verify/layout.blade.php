<x-www-app>
    {{-- page-center --}}
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">


                        <section>
                            <header>
                                <div class="d-flex justify-content-between align-items-end mb-3">
                                    <h1 class="h2 mb-0">이메일 검증</h1>

                                    <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
                                        로그인
                                    </a>
                                </div>

                                <div class="fs-sm mb-4">
                                    {{ __('방금 이메일로 보내드린 링크를 클릭하여 이메일 주소를 확인해 주시겠습니까?') }}

                                </div>
                            </header>




                            @if (session('status') == 'verification-link-sent')
                                <div class="mb-4 font-medium text-sm text-green-600">
                                    {{ __('A new verification link has been sent to the email address you provided in your profile settings.') }}
                                </div>
                            @endif

                            @livewire('auth-email-verification_notification',[
                                'viewFile'=>"jiny-auth::auth.verify.verification"
                            ])

                            <!-- Footer -->
                            <footer class="mt-4">
                                <div class="nav mb-4">
                                    <a class="nav-link text-decoration-underline p-0" href="/support/help">도움이
                                        필요하신가요?</a>
                                </div>
                                <p class="fs-xs mb-0">
                                    &copy; All rights reserved.
                                    Made by <span class="animate-underline"><a
                                            class="animate-target text-dark-emphasis text-decoration-none"
                                            href="https://jinyphp.com/" target="_blank" rel="noreferrer">jinyphp</a>
                                    </span>
                                </p>
                            </footer>
                        </section>

                        <!-- -->
                    </div>
                </div>
            </div>
        </div>
    </main>
</x-www-app>
