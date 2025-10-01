<!doctype html>
<html lang="en">
<head>
    <!-- Required meta tags -->
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon icon -->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}" />

    <!-- Libs CSS -->
    <link href="{{ asset('assets/fonts/feather/feather.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/simplebar/dist/simplebar.min.css') }}" rel="stylesheet" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}">

    <!-- Page specific styles -->
    @stack('styles')

    <title>@yield('title', 'Jiny - Bootstrap 5 Template')</title>
</head>

<body class="bg-white">
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid px-0">
            <div class="d-flex">
                <a class="navbar-brand" href="/"><img src="{{ asset('assets/images/brand/logo/logo.svg') }}" alt="Jiny" /></a>
                <div class="dropdown d-none d-md-block">
                    <button class="btn btn-light-primary text-primary" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fe fe-list me-2 align-middle"></i>
                        Category
                    </button>
                    <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                        @include('partials.category-dropdown')
                    </ul>
                </div>
            </div>

            <!-- Mobile view nav wrap -->
            <div class="ms-auto d-flex align-items-center order-lg-3">
                <div class="d-flex align-items-center">
                    @include('partials.theme-switcher')
                    <a href="{{ route('sign-in') }}" class="btn btn-outline-primary me-2 d-none d-md-block">Sign in</a>
                    <a href="{{ route('sign-up') }}" class="btn btn-primary d-none d-md-block">Sign up</a>
                    <button
                        class="navbar-toggler collapsed ms-2 d-lg-none"
                        type="button"
                        data-bs-toggle="collapse"
                        data-bs-target="#navbar-default"
                        aria-controls="navbar-default"
                        aria-expanded="false"
                        aria-label="Toggle navigation">
                        <span class="icon-bar top-bar mt-0"></span>
                        <span class="icon-bar middle-bar"></span>
                        <span class="icon-bar bottom-bar"></span>
                    </button>
                </div>
            </div>

            <!-- Collapse -->
            <div class="collapse navbar-collapse" id="navbar-default">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarBrowse" data-bs-toggle="dropdown" aria-expanded="false">
                            Landings
                        </a>
                        <ul class="dropdown-menu dropdown-menu-arrow" aria-labelledby="navbarBrowse">
                            <li><a class="dropdown-item" href="{{ route('landing.abroad') }}">Study Abroad</a></li>
                            <li><a class="dropdown-item" href="{{ route('landing.sass') }}">SaaS Product</a></li>
                            <li><a class="dropdown-item" href="{{ route('jobs.index') }}">Job Portal</a></li>
                            <li><a class="dropdown-item" href="{{ route('landing.courses') }}">Course Landing</a></li>
                            <li><a class="dropdown-item" href="{{ route('landing.education') }}">Online Education</a></li>
                            <li><a class="dropdown-item" href="{{ route('landing.academy') }}">Home Academy</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarPages" data-bs-toggle="dropdown" aria-expanded="false">
                            Pages
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarPages">
                            <li><a class="dropdown-item" href="{{ route('about') }}">About</a></li>
                            <li><a class="dropdown-item" href="{{ route('pricing') }}">Pricing</a></li>
                            <li><a class="dropdown-item" href="{{ route('blog') }}">Blog</a></li>
                            <li><a class="dropdown-item" href="{{ route('contact') }}">Contact</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarAccount" data-bs-toggle="dropdown" aria-expanded="false">
                            Accounts
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarAccount">
                            <li><a class="dropdown-item" href="{{ route('sign-in') }}">Sign In</a></li>
                            <li><a class="dropdown-item" href="{{ route('sign-up') }}">Sign Up</a></li>
                            <li><a class="dropdown-item" href="{{ route('forget-password') }}">Forgot Password</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown dropdown-fullwidth">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Mega Menu</a>
                        <div class="dropdown-menu dropdown-menu-md">
                            <div class="px-4 pt-2 pb-2">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="lh-1 mb-5">
                                            <h3 class="mb-1">Earn a Degree</h3>
                                            <p>Breakthrough pricing on 100% online degrees designed to fit into your life.</p>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12">
                                        <div class="border-bottom pb-2 mb-3">
                                            <h5 class="mb-0">Degrees</h5>
                                        </div>
                                        <div>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/degree-1.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">University of Michigan</small>
                                                        <h6 class="mb-0">Master of Applied Data Science</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/degree-2.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">A&B College 1980</small>
                                                        <h6 class="mb-0">MBA in Business Analytics</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/degree-3.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Imperial College London</small>
                                                        <h6 class="mb-0">Master of Science in Machine</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/degree-4.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">University of Colorado</small>
                                                        <h6 class="mb-0">Master of Computer Science</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <div class="mt-4">
                                                <a href="#" class="btn btn-outline-primary btn-sm">View all degree</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12 mt-4 mt-lg-0">
                                        <div class="border-bottom pb-2 mb-3">
                                            <h5 class="mb-0">Certificate Programs</h5>
                                        </div>
                                        <div>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/google.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">No Prerequisites</small>
                                                        <h6 class="mb-0">Google Data Analytics</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/IBM.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">No Prerequisites</small>
                                                        <h6 class="mb-0">IBM Data Science</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/microsoft.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Expert Feedback</small>
                                                        <h6 class="mb-0">Machine Learning for Analytics</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/tensorflow.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Certification Prerequisites</small>
                                                        <h6 class="mb-0">TensorFlow Developer Certificate</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <div class="mt-4">
                                                <a href="#" class="btn btn-outline-primary btn-sm">View all certificate</a>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-4 col-12 mt-4 mt-lg-0">
                                        <div class="border-bottom pb-2 mb-3">
                                            <h5 class="mb-0">Career opportunities</h5>
                                        </div>
                                        <div>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/mit.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Business & Management</small>
                                                        <h6 class="mb-0">Entrepreneurship Essentials</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/uog.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Product Management</small>
                                                        <h6 class="mb-0">MasterTrack Certificate in PM</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/scu.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Marketing Essentials</small>
                                                        <h6 class="mb-0">Digital Marketing & E-Commerce</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <a href="#">
                                                <div class="d-flex mb-3">
                                                    <img src="{{ asset('assets/images/png/ucb.png') }}" alt="" />
                                                    <div class="ms-2">
                                                        <small class="text-body">Business Model Innovation</small>
                                                        <h6 class="mb-0">Business Innovation Certificate</h6>
                                                    </div>
                                                </div>
                                            </a>
                                            <div class="mt-4">
                                                <a href="#" class="btn btn-outline-primary btn-sm">View all opportunities</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </li>
                </ul>
                <form class="d-flex align-items-center">
                    <span class="position-absolute ps-3 search-icon">
                        <i class="fe fe-search"></i>
                    </span>
                    <input type="search" class="form-control ps-6" placeholder="Search Courses" />
                </form>
            </div>
        </div>
    </nav>

    <!-- Main content -->
    @yield('content')

    <!-- Footer -->
    @hasSection('footer')
        @yield('footer')
    @else
        @includeIf($footer ?? 'partials.footers.white')
    @endif

    <!-- Scroll top -->
    <div class="btn-scroll-top">
        <svg class="progress-square svg-content" width="100%" height="100%" viewBox="0 0 40 40">
            <path d="M8 1H32C35.866 1 39 4.13401 39 8V32C39 35.866 35.866 39 32 39H8C4.13401 39 1 35.866 1 32V8C1 4.13401 4.13401 1 8 1Z" />
        </svg>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

    <!-- Theme JS -->
    <script src="{{ asset('assets/js/theme.min.js') }}"></script>

    <!-- Page specific scripts -->
    @stack('scripts')
</body>
</html>
