<form method="POST" action="{{ route('regist.create') }}"
    class="needs-validation" novalidate>
    @csrf

    {{-- 회원 가입양식 --}}
    {{$slot}}

</form>
