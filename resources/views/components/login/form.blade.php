<form method="POST" action="{{ route('login.session') }}">
    @csrf

    {{$slot}}

</form>
