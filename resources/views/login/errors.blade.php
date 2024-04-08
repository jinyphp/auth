<x-app>
    <x-bootstrap>
        <x-page-center>
            <div class="text-center mt-4">
                <h1 class="h2">회원 로그인</h1>
                <p class="lead"></p>
            </div>

            <div class="card">
                <div class="card-body">

                @if(!is_array($message))
                <div class="text-center">
                    {{$message}}
                </div>
                @else
                <ul>
                    @foreach($message as $item)
                    <li>{{$item}}</li>
                    @endforeach
                </ul>
                @endif


                </div>
            </div>
            <div class="text-center mb-3">
                Copyright all right reserved JinyPHP
            </div>
        </x-page-center>
    </x-bootstrap>
</x-app>
