<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>JinyPHP</title>

        {{-- @vite('resources/css/app.css') --}}
        {{-- <link rel="stylesheet" href="{{ asset('css/app.4387550e.css') }}"> --}}
        <script src="https://cdn.tailwindcss.com"></script>
        @stack('css')
        @livewireStyles
    </head>
    <body>

        <!-- Page Container -->
        <div id="page-container" class="flex flex-col mx-auto w-full min-h-screen bg-gray-100">
            <!-- Page Content -->
            <main id="page-content" class="flex flex-auto flex-col max-w-full">
                <div class="min-h-screen flex items-center justify-center relative overflow-hidden max-w-10xl mx-auto p-4 lg:p-8 w-full">
                    <!-- Patterns Background -->
                    <div class="pattern-dots-md text-gray-300 absolute top-0 right-0 w-32 h-32 lg:w-48 lg:h-48 transform translate-x-16 translate-y-16"></div>
                    <div class="pattern-dots-md text-gray-300 absolute bottom-0 left-0 w-32 h-32 lg:w-48 lg:h-48 transform -translate-x-16 -translate-y-16"></div>
                    <!-- END Patterns Background -->

                    <!-- Sign In Section -->
                    <div class="py-6 lg:py-0 w-full md:w-8/12 lg:w-6/12 xl:w-4/12 relative">
                        <div>지니Auth 패키지</div>

                        시스템에서 회원가입이 잠시 중단중 입니다. 관리자에게 문의해 주시길 바랍니다.


                    </div>
                    <!-- END Sign In Section -->
                </div>
            </main>
            <!-- END Page Content -->
        </div>
        <!-- END Page Container -->

        @livewireScripts
        @stack('scripts')
    </body>
</html>
