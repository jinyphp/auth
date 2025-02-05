<h1 class="h2 mt-auto">회원가입</h1>
<div class="nav fs-sm mb-3 mb-lg-4">
    가입된 회원이시라면
    <a class="nav-link text-decoration-underline p-0 ms-2" href="/login">
        로그인
    </a>
</div>

<x-card>
    <x-card-body>
        <form method="POST" action="{{ route('regist.create') }}"
            class="needs-validation" novalidate>
            @csrf

            {{-- 회원 가입양식 --}}
            @includeIf('jiny-auth::regist.form', ['setting' => $setting])

        </form>
    </x-card-body>
</x-card>


<x-ui-divider>
    <span class="text-body-emphasis fw-medium text-nowrap mx-4">or continue with</span>
</x-ui-divider>


@includeIf('jiny-auth::regist.seocial', ['setting' => $setting])
