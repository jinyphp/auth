<form method="POST" action="{{ route('login') }}">
    @csrf

    {{$slot}}

</form>
