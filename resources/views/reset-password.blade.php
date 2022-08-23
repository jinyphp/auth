<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>JinyERP</title>

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

                    <!-- Sign In Section -->
                    <div class="py-6 lg:py-0 w-full md:w-8/12 lg:w-6/12 xl:w-4/12 relative">
                        <!-- Header -->
                        <div class="mb-8 text-center">
                            <h1 class="text-4xl font-bold inline-flex items-center mb-1 space-x-3">
                                <span>비밀번호 변경</span>
                            </h1>
                        </div>
                        <!-- END Header -->

                        <!-- Session Status -->
                        @if(Session::has('status'))
                        <div class="mb-4 text-sm font-medium text-green-600">
                            {{Session::get('status')}}
                        </div>
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
                                    <form method="POST" action="{{ route('password.update') }}">
                                        @csrf

                                        <!-- Password Reset Token -->
                                        <input type="hidden" name="token" value="{{ $request->route('token') }}">

                                        <!-- Email -->
                                        <div class="space-y-1">
                                            <label for="email" name="email" class="font-medium">Email</label>
                                            <input class="block border border-gray-200 rounded px-5 py-3 leading-6 w-full focus:border-indigo-500 focus:ring focus:ring-indigo-500 focus:ring-opacity-50"
                                            type="email" id="email" name="email" placeholder="Enter your email" :value="old('email', $request->email)" required autofocus>
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


                                        <div class="flex items-center justify-end mt-4">
                                            <button type="submit" class="inline-flex justify-center items-center space-x-2 border font-semibold focus:outline-none w-full px-4 py-3 leading-6 rounded border-indigo-700 bg-indigo-700 text-white hover:text-white hover:bg-indigo-800 hover:border-indigo-800 focus:ring focus:ring-indigo-500 focus:ring-opacity-50 active:bg-indigo-700 active:border-indigo-700">
                                                {{ __('Reset Password') }}
                                            </button>
                                        </div>
                                    </form>
                                </div>

                            </div>
                            <div class="py-4 px-5 lg:px-6 w-full text-sm text-center bg-gray-50">
                                <a class="font-medium text-indigo-600 hover:text-indigo-400" href="/login">로그인 이동</a>
                            </div>
                        </div>
                        <!-- END Sign In Form -->

                        <!-- Footer -->
                        <div class="text-sm text-gray-500 text-center mt-6">
                            <a class="font-medium text-indigo-600 hover:text-indigo-400" href="https://jinyerp.com" target="_blank">JinyERP</a></a>
                        </div>
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
