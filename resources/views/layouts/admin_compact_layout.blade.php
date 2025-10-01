<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon icon-->
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('assets/images/favicon/favicon.ico') }}" />

    <!-- darkmode js -->
    <script src="{{ asset('assets/js/vendors/darkMode.js') }}"></script>

    <!-- Libs CSS -->
    <link href="{{ asset('assets/fonts/feather/feather.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/bootstrap-icons/font/bootstrap-icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/libs/simplebar/dist/simplebar.min.css') }}" rel="stylesheet" />

    <!-- Theme CSS -->
    <link rel="stylesheet" href="{{ asset('assets/css/theme.min.css') }}">

    <title>@yield('title', 'Dashboard') - Compact Layout | Jiny</title>
    @stack('styles')
</head>

<body>
    <!-- Wrapper -->
    <div id="db-wrapper" class="h-100">
        <!-- navbar vertical compact -->
        <nav class="navbar-vertical-compact">
    <!-- Brand logo -->
    <a class="navbar-brand" href="{{ route('home') }}">
        <img src="{{ asset('assets/images/brand/logo/logo-icon.svg" alt="Jiny" class="text-inverse" height="30" />
    </a>
    <div class="h-100" data-simplebar>
        <!-- Navbar nav -->
        <ul class="navbar-nav flex-column" id="sideNavbar">
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="dashboardDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-home"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="dashboardDropdown">
                    <li><span class="dropdown-header">Dashboard</span></li>
                    <li><a class="dropdown-item" href="{{ route('dashboard') }}">Overview</a></li>
                    <li><a class="dropdown-item" href="../../../pages/dashboard/dashboard-analytics.html">Analytics</a></li>
                </ul>
            </li>
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="courseDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-book"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="courseDropdown">
                    <li><span class="dropdown-header">Courses</span></li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-course-overview.html">All Courses</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-course-category.html">Courses Category</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-course-category-single.html">Category Single</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-user"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="userDropdown">
                    <li><span class="dropdown-header">Users</span></li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-instructor.html">Instructor</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-students.html">Students</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="cmsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-book-open"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="cmsDropdown">
                    <li><span class="dropdown-header">CMS</span></li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-cms-overview.html">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-cms-post.html">All Post</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-cms-post-new.html">New Post</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/admin-cms-post-category.html">Category</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="projectDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-file"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="projectDropdown">
                    <li><span class="dropdown-header">Project</span></li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-grid.html">Grid</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-list.html">List</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-overview.html">Overview</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-task.html">Task</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-budget.html">Budget</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-team.html">Team</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-files.html">Files</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/project-summary.html">Summary</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="authenticationDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-lock"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="authenticationDropdown">
                    <li><span class="dropdown-header">Authentication</span></li>

                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/sign-in.html">Sign In</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/sign-up.html">Sign Up</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/forget-password.html">Forget Password</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/notification-history.html">Notifications</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/404-error.html">404 Error</a>
                    </li>
                </ul>
            </li>
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="ecommerceDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-shopping-bag"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="ecommerceDropdown">
                    <li><span class="dropdown-header">Ecommerce</span></li>
                    <li class="dropdown-submenu dropend">
                        <a class="dropdown-item dropdown-toggle d-flex justify-content-between" href="#">Products</a>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/product-grid.html">Grid</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/product-grid-with-sidebar.html">Grid Sidebar</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/products.html">Products</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/product-single.html">Product Single</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/product-single-v2.html">Product Single v2</a>
                            </li>
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/add-product.html">Add Product</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/shopping-cart.html">Shopping Cart</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/checkout.html">Checkout</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/order.html">Order</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/order-single.html">Order Single</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/order-history.html">Order History</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/order-summary.html">Order Summary</a>
                    </li>

                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/customers.html">Customers</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/customer-single.html">Customer Single</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/ecommerce/add-customer.html">Add Customer</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="layoutsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-layout"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="layoutsDropdown">
                    <li><span class="dropdown-header">Layouts</span></li>

                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/layouts/layout-horizontal.html">Top</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/layouts/layout-vertical.html">Vertical</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/layouts/layout-compact.html">Compact</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link dropdownTooltip" href="../../../pages/dashboard/chat-app.html" data-template="chat">
                    <i class="nav-icon fe fe-message-square"></i>
                    <div id="chat" class="d-none">
                        <span class="fw-semibold fs-6">Chat</span>
                    </div>
                </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
                <a class="nav-link dropdownTooltip" href="../../../pages/dashboard/task-kanban.html" data-template="task">
                    <span class="me-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-trello">
                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                            <rect x="7" y="7" width="3" height="9"></rect>
                            <rect x="14" y="7" width="3" height="5"></rect>
                        </svg>
                    </span>
                    <div id="task" class="d-none">
                        <span class="fw-semibold fs-6">Task</span>
                    </div>
                </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
                <a class="nav-link dropdownTooltip" href="../../../pages/dashboard/mail.html" data-template="mail">
                    <span class="me-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-mail">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path>
                            <polyline points="22,6 12,13 2,6"></polyline>
                        </svg>
                    </span>
                    <div id="mail" class="d-none">
                        <span class="fw-semibold fs-6">Mail</span>
                    </div>
                </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
                <a class="nav-link dropdownTooltip" href="../../../pages/dashboard/calendar.html" data-template="calendar">
                    <span class="me-2">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            width="20"
                            height="20"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            class="feather feather-calendar">
                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </span>
                    <div id="calendar" class="d-none">
                        <span class="fw-semibold fs-6">Calendar</span>
                    </div>
                </a>
            </li>
            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="tableDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-database"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="tableDropdown">
                    <li><span class="dropdown-header">Tables</span></li>

                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/basic-table.html">Basic</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/datatables.html">Data Tables</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item">
                <a class="nav-link dropdownTooltip" href="../../../pages/help-center.html" data-template="helpCenter">
                    <i class="nav-icon fe fe-help-circle"></i>
                    <div id="helpCenter" class="d-none">
                        <span class="fw-semibold fs-6">Help Center</span>
                    </div>
                </a>
            </li>

            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="siteSettingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-settings"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="siteSettingDropdown">
                    <li><span class="dropdown-header">Site Setting</span></li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-general.html">General</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-google.html">Google</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-social.html">Social</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-social-login.html">Social Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-payment.html">Payment</a>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-smpt.html">SMPT</a>
                    </li>
                </ul>
            </li>

            <li class="nav-item dropdown dropend">
                <a class="nav-link" href="#" id="menulevelDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="nav-icon fe fe-corner-left-down"></i>
                </a>
                <ul class="dropdown-menu" aria-labelledby="menulevelDropdown">
                    <li><span class="dropdown-header">Menu Level</span></li>
                    <li class="dropdown-submenu dropend">
                        <a class="dropdown-item dropdown-toggle d-flex justify-content-between" href="#">Two level</a>
                        <ul class="dropdown-menu">
                            <li class="nav-item">
                                <a class="dropdown-item" href="../../../pages/dashboard/setting-google.html">Three Level</a>
                            </li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="dropdown-item" href="../../../pages/dashboard/setting-google.html">Three Level</a>
                    </li>
                </ul>
            </li>
        </ul>
        <!-- Card -->
    </div>
</nav>

        <!-- Page Content -->
        <main id="page-content-for-mini">
            <!-- Header -->
            <div class="header">
                <!-- navbar -->
                <nav class="navbar-default navbar navbar-expand-lg">
                    <a id="nav-toggle" href="#">
                        <i class="fe fe-menu"></i>
                    </a>
                    <div class="ms-lg-3 d-none d-md-none d-lg-block">
                        <!-- Form -->
                        <form class="d-flex align-items-center">
                            <span class="position-absolute ps-3 search-icon">
                                <i class="fe fe-search"></i>
                            </span>
                            <input type="search" class="form-control ps-6" placeholder="Search Entire Dashboard" />
                        </form>
                    </div>
                    <!--Navbar nav -->
                    <div class="ms-auto d-flex">
                        <ul class="navbar-nav navbar-right-wrap ms-2 d-flex nav-top-wrap">
                            <li class="dropdown ms-2">
                                <a class="rounded-circle" href="#" role="button" id="dropdownUser" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <div class="avatar avatar-md">
                                        <img alt="avatar" src="{{ asset('assets/images/avatar/avatar-1.jpg') }}" class="rounded-circle" />
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
                                    <a class="dropdown-item" href="#">Profile</a>
                                    <a class="dropdown-item" href="#">Settings</a>
                                    <a class="dropdown-item" href="#">Logout</a>
                                </div>
                            </li>
                        </ul>
                    </div>
                </nav>
            </div>
            @yield('content')
        </main>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>
    <script src="{{ asset('assets/js/theme.min.js') }}"></script>
    @stack('scripts')
</body>
</html>