<section>
    <header>
        <div class="d-flex justify-content-between align-items-end mb-3">
            <h1 class="h2 mb-0">약관동의</h1>

            <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
                로그인
            </a>
        </div>

        <div class="fs-sm mb-4">
            {{ __('회원 가입을 위해서는 먼저 사전 약관에 동의가 필요합니다.') }}

        </div>
    </header>

    @includeIf('jiny-auth::regist.agree.form')


    <!-- Footer -->
    <footer class="mt-4">
        <div class="nav mb-4">
            <a class="nav-link text-decoration-underline p-0" href="/support/help">도움이
                필요하신가요?</a>
        </div>
        <p class="fs-xs mb-0">
            &copy; All rights reserved.
            Made by <span class="animate-underline"><a class="animate-target text-dark-emphasis text-decoration-none"
                    href="https://jinyphp.com/" target="_blank" rel="noreferrer">jinyphp</a>
            </span>
        </p>
    </footer>
    <!-- end of footer -->

</section>
