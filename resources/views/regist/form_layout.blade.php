<form method="POST" action="{{ route('regist.create') }}"
    class="needs-validation mb-6" novalidate>
    @csrf

    {{-- 회원 가입양식 --}}
    @includeIf($viewForm)

</form>
