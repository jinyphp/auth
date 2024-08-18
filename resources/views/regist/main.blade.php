<h1 class="h2 mt-auto">회원가입</h1>
<div class="nav fs-sm mb-3 mb-lg-4">
    가입된 회원이시라면
    <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
        로그인
    </a>
</div>

{{-- <div class="nav fs-sm mb-4 d-lg-none">
    <span class="me-2">Uncertain about creating an account?</span>
    <a class="nav-link text-decoration-underline p-0" href="#benefits" data-bs-toggle="offcanvas"
        aria-controls="benefits">Explore the Benefits</a>
</div> --}}


<x-card>
    <x-card-body>
        <x-register-form>

            @includeIf('jinyauth::regist.form', ['setting' => $setting])

        </x-register-form>
    </x-card-body>
</x-card>


<x-ui-divider>
    <span class="text-body-emphasis fw-medium text-nowrap mx-4">or continue with</span>
</x-ui-divider>


@includeIf('jinyauth::regist.seocial', ['setting' => $setting])
