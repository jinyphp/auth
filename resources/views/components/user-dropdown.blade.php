@auth
<li class="dropdown ms-2">
    <a class="rounded-circle" href="#" role="button" id="dropdownUser" data-bs-toggle="dropdown" aria-expanded="false">
        <div class="avatar avatar-md avatar-indicators avatar-online">
            <img alt="avatar" src="{{ auth()->user()->avatar ?? asset('assets/images/avatar/avatar-1.jpg') }}" class="rounded-circle" />
        </div>
    </a>
    <div class="dropdown-menu dropdown-menu-end" aria-labelledby="dropdownUser">
        <div class="dropdown-item">
            <div class="d-flex">
                <div class="avatar avatar-md avatar-indicators avatar-online">
                    <img alt="avatar" src="{{ auth()->user()->avatar ?? asset('assets/images/avatar/avatar-1.jpg') }}" class="rounded-circle" />
                </div>
                <div class="ms-3 lh-1">
                    <h5 class="mb-1">{{ auth()->user()->name ?? 'User' }}</h5>
                    <p class="mb-0">{{ auth()->user()->email ?? 'user@example.com' }}</p>
                </div>
            </div>
        </div>
        <div class="dropdown-divider"></div>
        <ul class="list-unstyled">
            <li class="dropdown-submenu dropstart-lg">
                <a class="dropdown-item dropdown-list-group-item dropdown-toggle" href="#">
                    <i class="fe fe-circle me-2"></i>
                    Status
                </a>
                <ul class="dropdown-menu">
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="badge-dot bg-success me-2"></span>
                            Online
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="badge-dot bg-secondary me-2"></span>
                            Offline
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="badge-dot bg-warning me-2"></span>
                            Away
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="#">
                            <span class="badge-dot bg-danger me-2"></span>
                            Busy
                        </a>
                    </li>
                </ul>
            </li>
            <li>
                <a class="dropdown-item" href="/profile">
                    <i class="fe fe-user me-2"></i>
                    Profile
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="/subscription">
                    <i class="fe fe-star me-2"></i>
                    Subscription
                </a>
            </li>
            <li>
                <a class="dropdown-item" href="/settings">
                    <i class="fe fe-settings me-2"></i>
                    Settings
                </a>
            </li>
        </ul>
        <div class="dropdown-divider"></div>
        <ul class="list-unstyled">
            <li>
                <form method="POST" action="{{ route('logout') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="dropdown-item border-0 bg-transparent">
                        <i class="fe fe-power me-2"></i>
                        Sign Out
                    </button>
                </form>
            </li>
        </ul>
    </div>
</li>
@endauth