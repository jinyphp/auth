<div class="position-relative">
  <nav class="navbar navbar-expand-lg sidenav sidenav-navbar">
    <!-- Menu -->
    <a class="d-xl-none d-lg-none d-block text-inherit fw-bold" href="#">Menu</a>
    <!-- Button -->

    <button
      class="navbar-toggler d-lg-none icon-shape icon-sm rounded bg-primary text-light"
      type="button"
      data-bs-toggle="collapse"
      data-bs-target="#sidenavNavbar"
      aria-controls="sidenavNavbar"
      aria-expanded="false"
      aria-label="Toggle navigation">
      <span class="fe fe-menu"></span>
    </button>

    <!-- Collapse -->
    <div class="collapse navbar-collapse" id="sidenavNavbar">
      <div class="navbar-nav flex-column mt-4 mt-lg-0 d-flex flex-column gap-3">
        <ul class="list-unstyled mb-0">
          <!-- Nav item -->
          <li class="nav-item">
            <a class="nav-link" href="../pages/dashboard-instructor.html">
              <i class="fe fe-home nav-icon"></i>
              My Dashboard
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/instructor-courses.html">
              <i class="fe fe-book nav-icon"></i>
              My Courses
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/instructor-reviews.html">
              <i class="fe fe-star nav-icon"></i>
              Reviews
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/instructor-earning.html">
              <i class="fe fe-pie-chart nav-icon"></i>
              Earnings
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/instructor-order.html">
              <i class="fe fe-shopping-bag nav-icon"></i>
              Orders
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/instructor-students.html">
              <i class="fe fe-users nav-icon"></i>
              Students
            </a>
          </li>
          <li class="nav-item">
            <a class="nav-link" href="../pages/instructor-payouts.html">
              <i class="fe fe-dollar-sign nav-icon"></i>
              Payouts
            </a>
          </li>
          <!-- Nav item with dropdown -->
          <li class="nav-item nav-collapse">
            <a class="nav-sub-link" data-bs-toggle="collapse" href="#collapseQuiz">
              <i class="fe fe-help-circle nav-icon"></i>
              Quiz
            </a>
            <div class="collapse" id="collapseQuiz">
              <ul class="list-unstyled py-2 px-4">
                <li class="nav-item">
                  <a class="nav-link" href="../pages/instructor-quiz.html">All Quizzes</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="../pages/instructor-quiz-details.html">Quiz Single</a>
                </li>
                <li class="nav-item">
                  <a class="nav-link" href="../pages/instructor-quiz-result.html">Quiz Result</a>
                </li>
              </ul>
            </div>
          </li>
        </ul>

        <!-- Navbar header -->
        <div class="d-flex flex-column gap-1">
          <span class="navbar-header">Account Settings</span>
          <ul class="list-unstyled mb-0">
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/profile-edit.html">
                <i class="fe fe-settings nav-icon"></i>
                Edit Profile
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/security.html">
                <i class="fe fe-user nav-icon"></i>
                Security
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/social-profile.html">
                <i class="fe fe-refresh-cw nav-icon"></i>
                Social Profiles
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/notifications.html">
                <i class="fe fe-bell nav-icon"></i>
                Notifications
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/profile-privacy.html">
                <i class="fe fe-lock nav-icon"></i>
                Profile Privacy
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/delete-profile.html">
                <i class="fe fe-trash nav-icon"></i>
                Delete Profile
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="../pages/linked-accounts.html">
                <i class="fe fe-user nav-icon"></i>
                Linked Accounts
              </a>
            </li>
            <!-- Nav item -->
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                <i class="fe fe-power nav-icon"></i>
                Sign Out
              </a>
              <form id="logout-form" action="/sign-out" method="POST" style="display: none;">
                @csrf
              </form>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </nav>
</div>
