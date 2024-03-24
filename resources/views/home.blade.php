<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>JinyPHP</title>

  {{-- @vite('resources/css/app.css') --}}

</head>
<body>
    <div class="relative sm:flex sm:justify-center sm:items-center min-h-screen bg-dots-darker bg-center bg-gray-100 dark:bg-dots-lighter dark:bg-gray-900 selection:bg-red-500 selection:text-white">
        @if (Route::has('login'))
            <div class="sm:fixed sm:top-0 sm:right-0 p-6 text-right z-10">
                @auth
                    <a href="{{ url('/logout') }}" class="font-semibold text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white focus:outline focus:outline-2 focus:rounded-sm focus:outline-red-500">
                        Logout
                    </a>
                @endauth
            </div>
        @endif
    </div>

    <main>

        <div class="container mt-4">
            <div class="mb-4">
                <h1 class="mb-0 h3">
                    {{userName()}}님은 {{userType()}} 회원 입니다.
                </h1>
                <p class="mb-0 fs-6">최근 접속일은 {{userLastLog()}} 입니다.</p>
            </div>
        </div>

    </main>

    @foreach ($message as $item)
    <p>{{$item}}</p>
    @endforeach

</body>
</html>

