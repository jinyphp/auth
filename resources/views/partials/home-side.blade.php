<div class="position-relative">
    <nav class="navbar navbar-expand-lg sidenav sidenav-navbar">
        <!-- Menu -->
        <a class="d-xl-none d-lg-none d-block text-inherit fw-bold" href="#">Menu</a>
        <!-- Button -->

        <button class="navbar-toggler d-lg-none icon-shape icon-sm rounded bg-primary text-light" type="button"
            data-bs-toggle="collapse" data-bs-target="#sidenavNavbar" aria-controls="sidenavNavbar" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="fe fe-menu"></span>
        </button>

        <!-- Collapse -->
        <div class="collapse navbar-collapse" id="sidenavNavbar">
            <div class="navbar-nav flex-column mt-4 mt-lg-0 d-flex flex-column gap-3">
                <ul class="list-unstyled mb-0">
                    <!-- Nav item -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home.dashboard') }}">
                            <i class="fe fe-home nav-icon"></i>
                            My Home
                        </a>
                    </li>
                </ul>

                <!-- Navbar header -->
                @includeIf("jiny-auth::partials.home.learn")

                <!-- Navbar header -->
                @includeIf("jiny-chat::partials.home.chat")

                <!-- Navbar header -->
                @includeIf("jiny-auth::partials.home.emoney")

                <!-- Navbar header -->
                @includeIf("jiny-auth::partials.home.message")

                <!-- Navbar header -->
                @includeIf("jiny-auth::partials.home.account")

            </div>
        </div>
    </nav>
</div>
