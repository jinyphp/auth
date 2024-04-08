<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>JinyPHP</title>

        {{-- <script src="https://cdn.tailwindcss.com"></script> --}}
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH"
            crossorigin="anonymous">
        @stack('css')
        @livewireStyles
    </head>
    <body>

        <x-layout-center-middle>
            <div class="text-center mt-4">
                <h1 class="h2">{{$message}}</h1>
                <p class="lead">
                    회원 해제 신청하기
                </p>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="m-sm-3">

                        @livewire('WireSleeper-UnlockRequest')

                    </div>
                </div>
            </div>
            <div class="text-center mb-3">
                <!-- -->
            </div>
        </x-layout-center-middle>






        @livewireScripts
        @stack('scripts')
    </body>
</html>
