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
                    <!-- Header -->
                    <!--
                    <div class="mb-8 text-center">
                        <h1 class="text-4xl font-bold inline-flex items-center mb-1 space-x-3">
                        <svg class="hi-solid hi-cube-transparent inline-block w-8 h-8 text-indigo-500" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" d="M9.504 1.132a1 1 0 01.992 0l1.75 1a1 1 0 11-.992 1.736L10 3.152l-1.254.716a1 1 0 11-.992-1.736l1.75-1zM5.618 4.504a1 1 0 01-.372 1.364L5.016 6l.23.132a1 1 0 11-.992 1.736L4 7.723V8a1 1 0 01-2 0V6a.996.996 0 01.52-.878l1.734-.99a1 1 0 011.364.372zm8.764 0a1 1 0 011.364-.372l1.733.99A1.002 1.002 0 0118 6v2a1 1 0 11-2 0v-.277l-.254.145a1 1 0 11-.992-1.736l.23-.132-.23-.132a1 1 0 01-.372-1.364zm-7 4a1 1 0 011.364-.372L10 8.848l1.254-.716a1 1 0 11.992 1.736L11 10.58V12a1 1 0 11-2 0v-1.42l-1.246-.712a1 1 0 01-.372-1.364zM3 11a1 1 0 011 1v1.42l1.246.712a1 1 0 11-.992 1.736l-1.75-1A1 1 0 012 14v-2a1 1 0 011-1zm14 0a1 1 0 011 1v2a1 1 0 01-.504.868l-1.75 1a1 1 0 11-.992-1.736L16 13.42V12a1 1 0 011-1zm-9.618 5.504a1 1 0 011.364-.372l.254.145V16a1 1 0 112 0v.277l.254-.145a1 1 0 11.992 1.736l-1.735.992a.995.995 0 01-1.022 0l-1.735-.992a1 1 0 01-.372-1.364z" clip-rule="evenodd"/></svg>
                        <span>회원가입</span>
                        </h1>
                        <p class="text-gray-500">
                            서비스를 이용하기 위해서는 먼저 회원가입이 필요합니다.
                        </p>
                    </div>
                    -->
                    <!-- END Header -->

                    <!-- Session Status -->
                    @if(Session::has('status'))
                    <div class="mb-4 text-sm font-medium text-green-600">{{Session::get('status')}}</div>
                    @endif


                    <!-- Validation Errors -->
                    @if ($errors->any())
                    <div class="mb-4">
                        <div class="font-medium text-red-600">
                            {{ __('Whoops! Something went wrong.') }}
                        </div>

                        <ul class="mt-3 list-disc list-inside text-sm text-red-600">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    @endif


                    <!-- Sign In Form -->
                    <div class="flex flex-col rounded shadow-sm bg-white overflow-hidden">
                        <div class="p-5 lg:p-6 grow w-full">
                        <div class="sm:p-5 lg:px-10 lg:py-8">
                            <form method="POST" action="{{ route('register') }}" class="space-y-6">
                                @csrf

                                <!-- Name -->
                                <div class="space-y-1">
                                    <label for="email" name="email" class="font-medium">{{__('Name')}}</label>
                                    <input class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    type="text" id="name" name="name" placeholder="회원명을 입력해 주세요" :value="old('name')" required autofocus>
                                </div>

                                <!-- Email -->
                                <div class="space-y-1">
                                    <label for="email" name="email" class="font-medium">Email</label>
                                    <input class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    type="email" id="email" name="email" placeholder="Enter your email" :value="old('email')" required autofocus>
                                </div>

                                <!-- password -->
                                <div class="space-y-1">
                                    <label for="password" name="email" class="font-medium">{{__('Password')}}</label>
                                    <input class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    type="password" id="password" name="password" placeholder="Enter your password" required >
                                </div>

                                <!-- Confirm Password -->
                                <div class="space-y-1">
                                    <label for="password_confirmatio" name="email" class="font-medium">{{__('Confirm Password')}}</label>
                                    <input class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                    type="password" id="password_confirmatio" name="password_confirmation" required >
                                </div>


                                <div>
                                    <button type="submit" class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none w-full px-4 py-3 leading-6 rounded border-indigo-700 bg-indigo-700 text-white hover:text-white hover:bg-indigo-800 hover:border-indigo-800 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 active:bg-indigo-700 active:border-indigo-700">
                                        {{ __('Register') }}
                                    </button>
                                </div>

                            </form>
                        </div>
                        </div>
                        <div class="py-4 px-5 lg:px-6 w-full text-sm text-center bg-gray-50">
                            <a class="text-sm text-gray-600 underline hover:text-gray-900" href="{{ route('login') }}">
                                {{ __('회원이시면 로그인을 해주세요.') }}
                            </a>
                        </div>
                    </div>
                    <!-- END Sign In Form -->

                    <!-- Footer -->
                    <!--
                    <div class="text-sm text-gray-500 text-center mt-6">
                        <a class="font-medium text-indigo-600 hover:text-indigo-400" 
                            href="https://jinyerp.com" target="_blank">
                            JinyERP
                        </a>
                    </div>
                    -->
                    <!-- END Footer -->
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
