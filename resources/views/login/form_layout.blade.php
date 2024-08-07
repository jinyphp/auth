<form method="POST" action="{{ route('login') }}">
    @csrf

    @includeIf($viewForm)

</form>
