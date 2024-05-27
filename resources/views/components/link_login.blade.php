@php
    $str = explode('|',$slot);
    if(isset($str[0])) {
        $title1 = $str[0];
    } else {
        $title1 = "로그인";
    }

    if(isset($str[1])) {
        $title2 = $str[1];
    } else {
        $title2 = "마이페이지";
    }

@endphp

@auth
    {{--
        <a {{ $attributes->merge(['href'=>"/home"]) }}>
        {{$title2}}
        </a>
    --}}

    <a {{ $attributes->merge(['href'=>"/home", 'class'=>"dropdown-toggle"]) }}
        data-bs-toggle="dropdown" aria-expanded="false" data-bs-display="static">
        {{$title2}}
    </a>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="/home">
                <div class="d-flex">
                    <div>
                        <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32"
                            fill="currentColor" class="bi bi-person-fill" viewBox="0 0 16 16">
                            <path d="M3 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6"/>
                        </svg>
                    </div>
                    <div class="flex-grow-1">
                        <div>{{Auth::user()->name}}</div>
                        <div>{{Auth::user()->email}}</div>
                    </div>
                </div>

            </a>

        </li>
        <li>
            <hr>
        </li>
        <li>
            <a class="dropdown-item" href="/logout">Logout</a>
        </li>
    </ul>

@else
    <a {{ $attributes->merge(['href'=>"/login"]) }}>
        {{$title1}}
    </a>
@endauth
