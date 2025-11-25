<!doctype html>
<html lang="ko">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}" />

    <!-- darkmode js -->
    <script src="{{ asset('assets/js/vendors/darkMode.js') }}"></script>

    <!-- Libs CSS -->
    <link href="{{ asset('assets/fonts/feather/feather.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/simplebar/dist/simplebar.min.css') }}" rel="stylesheet" />

    @stack('styles')

    <!-- Theme CSS -->
    {{-- <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}"> --}}
    {{-- Theme CSS (Vite compiled) --}}
    @vite('resources/scss/bootstrap/theme.scss')

    <title>@yield('title', 'Dashboard') | Jiny Auth11</title>
</head>

<body>
    <!-- Wrapper -->
    <!-- Wrapper -->
    <div id="db-wrapper">
        <!-- Sidebar -->
        @include('jiny-auth::partials.admin.sidebar')

        <!-- Page Content -->
        <main id="page-content">
            <!-- Header -->
            @hasSection('header')
                @yield('header')
            @else
                @include('jiny-auth::partials.admin.header')
            @endif

            <!-- Content -->
            @yield('content')
        </main>
    </div>

    {{-- Scripts (Vite compiled) --}}
    @vite('resources/js/app.js')

    @stack('scripts')
    @stack('page-scripts')
</body>

</html>
