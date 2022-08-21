<h1>Social Auth Login</h1>

<ul>
    @foreach($providers as $item)
    <li>
        <a href="{{route('oauth-redirect', $item->name)}}">{{$item->name}}</a>
    </li>
    @endforeach
</ul>
