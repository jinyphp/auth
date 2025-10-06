<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
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

    <link rel="canonical" href="https://geeksui.codescandy.com/geeks/docs/accordions.html">
    <link href="{{ asset('assets/libs/prismjs/themes/prism-okaidia.min.css') }}" rel="stylesheet">
    <title>Accordion Bootstrap 5 Example - Jiny UI</title>
</head>

<body class="bg-white">
    <!-- Main wrapper -->
    <div class="docs-main-wrapper">
        <div class="docs-header">
            <nav class="navbar navbar-expand-md fixed-top ms-0">
                <a class="navbar-brand" href="/"><img src="{{ asset('assets/images/brand/logo/logo.svg') }}"
                        alt="Jiny" /></a>
                <div class="ms-auto d-flex align-items-center">
                    <div class="dropdown">
                        <button class="btn btn-light btn-icon rounded-circle d-flex align-items-center" type="button"
                            aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme (auto)">
                            <i class="bi theme-icon-active"></i>
                            <span class="visually-hidden bs-theme-text">Toggle theme</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bs-theme-text">
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center"
                                    data-bs-theme-value="light" aria-pressed="false">
                                    <i class="bi theme-icon bi-sun-fill"></i>
                                    <span class="ms-2">Light</span>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center"
                                    data-bs-theme-value="dark" aria-pressed="false">
                                    <i class="bi theme-icon bi-moon-stars-fill"></i>
                                    <span class="ms-2">Dark</span>
                                </button>
                            </li>
                            <li>
                                <button type="button" class="dropdown-item d-flex align-items-center active"
                                    data-bs-theme-value="auto" aria-pressed="true">
                                    <i class="bi theme-icon bi-circle-half"></i>
                                    <span class="ms-2">Auto</span>
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="d-flex">
                        <div class="me-2 me-lg-0 ms-2">
                            <a class="btn btn-primary" href="https://bit.ly/geeksui">Buy now</a>
                        </div>
                        <div>
                            <button class="btn btn-icon navbar-toggler" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                <span class="navbar-toggler-icon"></span>
                            </button>
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- left sidebar -->
        <!-- Nav Sidebar -->
        <aside class="docs-nav-sidebar">
            <div class="py-5"></div>
            <div class="docs-nav">
                <nav class="navbar navbar-expand-md">
                    <div class="collapse navbar-collapse" id="navbarNav">
                        <ul class="navbar-nav flex-column" id="sidebarnav">
                            <li class="navbar-header">
                                <h5 class="heading">Getting Started</h5>
                            </li>
                            <li class="nav-item"><a href="/" class="nav-link">Introduction</a></li>
                            <li class="nav-item"><a href="{{ route('docs.environment-setup') }}"
                                    class="nav-link">Environment setup</a></li>
                            <li class="nav-item"><a href="{{ route('docs.working-with-gulp') }}"
                                    class="nav-link">Working with Gulp</a></li>
                            <li class="nav-item"><a href="{{ route('docs.npm-scripts') }}" class="nav-link">Npm
                                    Commands</a></li>
                            <li class="nav-item"><a href="{{ route('docs.compiled-files') }}" class="nav-link">Compiled
                                    Files</a></li>
                            <li class="nav-item"><a href="{{ route('docs.file-structure') }}" class="nav-link">File
                                    Structure</a></li>
                            <li class="nav-item"><a href="{{ route('docs.resources-assets') }}"
                                    class="nav-link">Resources & assets</a></li>
                            <li class="nav-item"><a href="{{ route('docs.changelog') }}"
                                    class="nav-link">Changelog</a></li>

                            <li>
                                <div class="navbar-border"></div>
                            </li>
                            <li class="navbar-header mt-0">
                                <h5 class="heading">Foundation</h5>
                            </li>
                            <li class="nav-item"><a href="{{ route('docs.typography') }}"
                                    class="nav-link">Typography</a></li>
                            <li class="nav-item"><a href="{{ route('docs.colors') }}" class="nav-link">Colors</a>
                            </li>
                            <li class="nav-item"><a href="{{ route('docs.shadows') }}" class="nav-link">Shadows</a>
                            </li>

                            <li>
                                <div class="navbar-border"></div>
                            </li>

                            <li class="navbar-header mt-0">
                                <h5 class="heading">Snippet</h5>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.bootstrap-5-snippets') }}" class="nav-link">Introduction</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.accordions-snippet') }}" class="nav-link">Accordions</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.card-snippet') }}" class="nav-link">Card</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.chart') }}" class="nav-link">Chart</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.client-snippet') }}" class="nav-link">Clients</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.cta-snippet') }}" class="nav-link">CTA</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.features-snippet') }}" class="nav-link">Features</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.footer-snippet') }}" class="nav-link">Footer</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.header-snippet') }}" class="nav-link">Headers</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.hero-snippet') }}" class="nav-link">Hero</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.image-snippet') }}" class="nav-link">Image</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.integration-snippet') }}" class="nav-link">Integrations</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('pricing') }}" class="nav-link">Pricing</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.reviews') }}" class="nav-link">Reviews</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.slider-snippet') }}" class="nav-link">Slider</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.stats') }}" class="nav-link">Stats</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.tables-snippet') }}" class="nav-link">Tables</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.team-snippet') }}" class="nav-link">Team</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.testimonials-snippet') }}" class="nav-link">Testimonials</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.user-profile') }}" class="nav-link">User Profile</a>
                            </li>
                            <li>
                                <div class="navbar-border"></div>
                            </li>
                            <li class="navbar-header mt-0">
                                <h5 class="heading">Components</h5>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.accordions') }}" class="nav-link">Accordions</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.alerts') }}" class="nav-link">Alerts</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.avatar') }}" class="nav-link">Avatar</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.badge') }}" class="nav-link">Badge</a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('docs.breadcrumb') }}" class="nav-link">Breadcrumb</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.buttons') }}" class="nav-link">Buttons</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.button-group') }}" class="nav-link">Button group</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.card') }}" class="nav-link">Card</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.carousel') }}" class="nav-link">Carousel</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.close-button') }}" class="nav-link">Close Button</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.collapse') }}" class="nav-link">Collapse</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.dropdowns') }}" class="nav-link">Dropdowns</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.images') }}" class="nav-link">Images</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.list-group') }}" class="nav-link">List group</a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('docs.modal') }}" class="nav-link">Modal</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.navs-tabs') }}" class="nav-link">Navs and tabs</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.navbar') }}" class="nav-link">Navbar</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.offcanvas') }}" class="nav-link">Offcanvas</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.pagination') }}" class="nav-link">Pagination</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.placeholders') }}" class="nav-link">Placeholders</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.popovers') }}" class="nav-link">Popovers</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.progress') }}" class="nav-link">Progress</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.scrollspy') }}" class="nav-link">Scrollspy</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.spinners') }}" class="nav-link">Spinners</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.tables') }}" class="nav-link">Tables</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.toasts') }}" class="nav-link">Toasts</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.tooltips') }}" class="nav-link">Tooltips</a>
                            </li>
                            <li>
                                <div class="navbar-border"></div>
                            </li>
                            <li class="navbar-header mt-0">
                                <h5 class="heading">Forms</h5>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.forms') }}" class="nav-link">Basic Forms</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.advance-forms') }}" class="nav-link">Advance Forms</a>
                            </li>

                            <li class="nav-item">
                                <a href="{{ route('docs.dropzone') }}" class="nav-link">Dropzone</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.flatpickr') }}" class="nav-link">Datepicker</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.input-group') }}" class="nav-link">Input Group</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.inputmask') }}" class="nav-link">Imask</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.quill-editor') }}" class="nav-link">Quill Editor</a>
                            </li>
                            <li class="nav-item">
                                <a href="{{ route('docs.tagify') }}" class="nav-link">Tagify</a>
                            </li>

                            <li>
                                <div class="navbar-border"></div>
                            </li>
                            <li class="navbar-header mt-0">
                                <h5 class="heading">Utilities</h5>
                            </li>
                            <li class="nav-item"><a href="{{ route('docs.background') }}"
                                    class="nav-link">Background</a></li>
                            <li class="nav-item"><a href="{{ route('docs.borders') }}" class="nav-link">Borders</a>
                            </li>
                            <li class="nav-item"><a href="{{ route('docs.colored-links') }}"
                                    class="nav-link">Colored Links</a></li>
                            <li class="nav-item"><a href="{{ route('docs.opacity') }}" class="nav-link">Opacity</a>
                            </li>
                            <li class="nav-item"><a href="{{ route('docs.ratio') }}" class="nav-link">Ratio</a></li>
                            <li class="nav-item"><a href="{{ route('docs.stacks') }}" class="nav-link">Stacks</a>
                            </li>
                            <li class="nav-item"><a href="{{ route('docs.text-color') }}"
                                    class="nav-link">Colors</a></li>
                            <li class="nav-item"><a href="{{ route('docs.text') }}" class="nav-link">Text</a></li>
                            <li class="nav-item"><a href="{{ route('docs.text-truncation') }}" class="nav-link">Text
                                    truncation</a></li>
                            <li class="nav-item"><a href="{{ route('docs.vertical-rule') }}"
                                    class="nav-link">Vertical rule</a></li>
                        </ul>
                    </div>
                </nav>
            </div>
            <div class="nav-footer">
                <p class="mb-0">
                    Developed by
                    <a href="https://codescandy.com" target="_blank">Codescandy</a>
                </p>
            </div>
        </aside>

        <!-- Wrapper  -->
        <main class="docs-wrapper">

            @yield('content')

        </main>
    </div>

    <!-- Scroll top -->
    <div class="btn-scroll-top">
        <svg class="progress-square svg-content" width="100%" height="100%" viewBox="0 0 40 40">
            <path
                d="M8 1H32C35.866 1 39 4.13401 39 8V32C39 35.866 35.866 39 32 39H8C4.13401 39 1 35.866 1 32V8C1 4.13401 4.13401 1 8 1Z" />
        </svg>
    </div>

    <!-- Scripts -->
    <!-- Libs JS -->
    <script src="{{ asset('assets/libs/@popperjs/core/dist/umd/popper.min.js') }}"></script>
    <script src="{{ asset('assets/libs/bootstrap/dist/js/bootstrap.min.js') }}"></script>
    <script src="{{ asset('assets/libs/simplebar/dist/simplebar.min.js') }}"></script>

    <!-- Theme JS -->

    <script src="{{ asset('assets/js/vendors/sidebarMenu.js') }}"></script>
    <script src="{{ asset('assets/libs/prismjs/prism.js') }}"></script>
    <script src="{{ asset('assets/libs/prismjs/components/prism-scss.min.js') }}"></script>
    <script src="{{ asset('assets/libs/prismjs/plugins/toolbar/prism-toolbar.min.js') }}"></script>
    <script src="{{ asset('assets/libs/prismjs/plugins/copy-to-clipboard/prism-copy-to-clipboard.min.js') }}"></script>
</body>

</html>
