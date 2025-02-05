<x-www-app>
    {{-- page-center --}}
    <main class="d-flex w-100 h-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">


                        <section>
                            <header class="d-flex justify-content-between align-items-end mb-3">
                                <h1 class="h2 mb-0">회원가입</h1>
                                <div class="nav fs-sm">
                                    회원이신가요?
                                    <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
                                        로그인
                                    </a>
                                </div>
                            </header>

                            <!-- Session Status -->
                            @if (session('status'))
                                <div class="mb-4 font-medium text-sm text-green-600">
                                    {{ session('status') }}
                                </div>
                            @endif

                            @if (session('error'))
                                <div class="alert alert-warning alert-dismissible" role="alert">
                                    <div class="alert-message">
                                        {{ session('error') }}
                                    </div>
                                </div>
                            @endif

                            {{-- Ajax 형식의 회원 가입 --}}
                            @includeIf("jiny-auth::regist.form.ajax")

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


                    </div>
                </div>
            </div>
        </div>
    </main>
</x-www-app>
