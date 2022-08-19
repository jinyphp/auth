<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>JinyERP</title>

        {{-- @vite('resources/css/app.css') --}}
        <link rel="stylesheet" href="{{ asset('css/app.4387550e.css') }}">
        @stack('css')
        @livewireStyles
    </head>
    <body>

        <!-- Page Container -->
        <div id="page-container" class="flex flex-col mx-auto w-full min-h-screen bg-gray-100">
            <!-- Page Content -->
            <main id="page-content" class="flex flex-auto flex-col max-w-full">
                <div class="min-h-screen flex items-center justify-center relative overflow-hidden max-w-10xl mx-auto p-4 lg:p-8 w-full">

                    <div class="py-6 lg:py-0 w-full md:w-8/12 lg:w-6/12 xl:w-6/12 relative">
                        <!-- Danger Alert -->
                        <div class="p-4 md:p-5 rounded text-red-700 bg-red-100">
                            <div class="flex items-center mb-3">
                                <svg class="hi-solid hi-x-circle inline-block w-5 h-5 mr-3 flex-none text-red-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                <h3 class="font-semibold">
                                    {{$message}}
                                </h3>
                            </div>

                            @if (session()->has('message_item'))
                            <ul class="list-inside ml-8 space-y-2">
                                @foreach(session('message_item') as $item)
                                <li class="flex items-center">
                                    <svg class="hi-solid hi-arrow-narrow-right inline-block w-4 h-4 flex-none mr-2" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd"/></svg>
                                    {{$item}}
                                </li>
                                @endforeach
                            </ul>
                            @endif


                        </div>
                        <!-- END Danger Alert -->



                    </div>

                </div>
            </main>
            <!-- END Page Content -->
        </div>
        <!-- END Page Container -->

        @livewireScripts
        @stack('scripts')
    </body>
</html>
