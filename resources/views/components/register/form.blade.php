<form method="POST" action="{{ route('register.create') }}"
    class="needs-validation mb-6" novalidate>
    @csrf

    {{-- 회원 가입양식 --}}
    {{$slot}}

</form>
