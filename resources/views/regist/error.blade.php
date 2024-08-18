<x-app>
    <x-bootstrap>
        <!-- 회원가입 오류 -->
        <div class="container">

            <x-page-center>
            @foreach ($messages as $item)
                <div class="alert alert-danger">
                    {{$item}}
                </div>
            @endforeach
            </x-page-center>

        </div>
    </x-bootstrap>
</x-app>
