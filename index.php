<?php
$root = "";
require_once __DIR__ . '/includes/functions.php';
$page_title = "Home";
include __DIR__ . '/includes/header.php';

$books_result = $conn->query("SELECT title, author, available_copies FROM books ORDER BY added_date DESC LIMIT 4");
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-book-half"></i> Digital Library</a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#homeNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="homeNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link" href="#about">About Library</a></li>
        <li class="nav-item"><a class="nav-link" href="#features">Features</a></li>
        <li class="nav-item"><a class="nav-link" href="#rules">Rules &amp; Guidelines</a></li>
        <li class="nav-item"><a class="nav-link" href="#contact">Contact Us</a></li>
      </ul>
      <a href="login.php" class="btn btn-outline-light btn-sm me-2">Login</a>
      <a href="register.php" class="btn btn-accent btn-sm">Register</a>
    </div>
  </div>
</nav>

<div class="hero-section">
  <div class="container">
    <div class="row align-items-center g-5">
      <div class="col-lg-7">
        <span class="hero-eyebrow">DIGITAL lIBRARY MANAGEMENT SYSTEM</span>
        <h1>Your Library, Made Simple.</h1>
        <p class="lead mt-3">
          Search books, check availability, and manage library records through one easy-to-use digital system.
        </p>
      </div>
      <div class="col-lg-5"> 
        <!--<div class="library-card-visual">
          <div class="library-card-visual__header">
            <span>Date Due</span><span>Book</span>
          </div>
          <div class="library-card-visual__row"><span>Aug 22</span><span>Intro to Algorithms</span></div>
          <div class="library-card-visual__row"><span>Sep 05</span><span>The Alchemist</span></div>
          <div class="library-card-visual__row"><span>Sep 12</span><span>Sapiens</span></div>
          <div class="library-card-visual__row"><span>Sep 19</span><span>Discrete Mathematics</span></div>-->
        </div>
      </div>
    </div>
  </div>
</div>

<div class="container my-5">

  <section id="about" class="mb-5">
    <h4 class="mb-3">About the Digital Library</h4>
    <p class="text-muted">
      The Digital Library Management System is a web-based application designed to simplify common library activities.
       It helps students search for books and check their availability, while librarians can manage books, members, and issue and return records digitally.
       The system reduces paperwork, saves time, and keeps library records organized.
    </p>
  </section>

 <section id="features" class="mb-5">
  <h4 class="mb-4">Main Features</h4>

  <div class="row g-4">

    <!-- 1. Search Books -->
    <div class="col-md-6 col-lg-4">
      <div class="card feature-card h-100 text-center border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="feature-icon mb-3">
            <i class="bi bi-search"></i>
          </div>
          <h5>Search Books</h5>
          <p class="text-muted mb-0">
            Search books by title, author, category or ISBN and quickly check their availability.
          </p>
        </div>
      </div>
    </div>

    <!-- 2. Track Due Dates -->
    <div class="col-md-6 col-lg-4">
      <div class="card feature-card h-100 text-center border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="feature-icon mb-3">
            <i class="bi bi-clock-history"></i>
          </div>
          <h5>Track Due Dates</h5>
          <p class="text-muted mb-0">
            Keep track of book due dates and overdue returns.
          </p>
        </div>
      </div>
    </div>

    <!-- 3. Real-Time Availability -->
    <div class="col-md-6 col-lg-4">
      <div class="card feature-card h-100 text-center border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="feature-icon mb-3">
            <i class="bi bi-graph-up"></i>
          </div>
          <h5>Book Availability</h5>
          <p class="text-muted mb-0">
            Check whether a book is currently available for borrowing.
          </p>
        </div>
      </div>
    </div>

    <!-- 4. Issue & Return System -->
    <div class="col-md-6 col-lg-4">
      <div class="card feature-card h-100 text-center border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="feature-icon mb-3">
            <i class="bi bi-arrow-left-right"></i>
          </div>
          <h5>Issue &amp; Return System</h5>
          <p class="text-muted mb-0">
            Manage book issue and return transactions, due dates and overdue fines.
          </p>
        </div>
      </div>
    </div>

    <!-- 5. Reports -->
    <div class="col-md-6 col-lg-4">
      <div class="card feature-card h-100 text-center border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="feature-icon mb-3">
            <i class="bi bi-bar-chart"></i>
          </div>
          <h5>Reports</h5>
          <p class="text-muted mb-0">
            View book availability, issue, return, overdue and borrowing statistics.
          </p>
        </div>
      </div>
    </div>

    <!-- 6. Security -->
    <div class="col-md-6 col-lg-4">
      <div class="card feature-card h-100 text-center border-0 shadow-sm">
        <div class="card-body p-4">
          <div class="feature-icon mb-3">
            <i class="bi bi-shield-lock"></i>
          </div>
          <h5>Security</h5>
          <p class="text-muted mb-0">
            Secure login with password protection and role-based access for students and librarians.
          </p>
        </div>
      </div>
    </div>

  </div>
</section>
<!-- rules -->
  <section id="rules" class="mb-5">
    <h4 class="mb-3">Rules &amp; Guidelines</h4>

    <div class="card rules-card ">
        <div class="card-body">

            <ul class=" mb-0">

                <li>
                    <i class="bi bi-calendar-check"></i>
                    <span>
                        Books can be borrowed for
                        <strong><?php echo LOAN_PERIOD_DAYS; ?> days</strong>.
                    </span>
                </li>

                <li>
                    <i class="bi bi-book"></i>
                    <span>
                        Each student can borrow a maximum of
                        <strong>3 books</strong> at a time.
                    </span>
                </li>

                <li>
                    <i class="bi bi-arrow-return-left"></i>
                    <span>
                        Books should be returned on or before the
                        <strong>due date</strong>.
                    </span>
                </li>

                <li>
                    <i class="bi bi-cash-coin"></i>
                    <span>
                        A fine of <strong>Rs. <?php echo FINE_PER_DAY; ?> per day</strong>
                        may apply for late returns.
                    </span>
                </li>

                <li>
                    <i class="bi bi-exclamation-circle"></i>
                    <span>
                        Lost or damaged books should be reported to the
                        <strong>librarian immediately</strong>.
                    </span>
                </li>

                <li>
                    <i class="bi bi-shield-lock"></i>
                    <span>
                        Students should keep their library account
                        <strong>safe and private</strong>.
                    </span>
                </li>

                <li>
                    <i class="bi bi-book-half"></i>
                    <span>
                        Library books should be
                        <strong>handled carefully</strong>.
                    </span>
                </li>

                <li>
                    <i class="bi bi-volume-mute"></i>
                    <span>
                        Students should maintain
                        <strong>proper discipline</strong> in the library.
                    </span>
                </li>

            </ul>

        </div>
    </div>
</section>

 <section id="contact" class="mb-5">
    <h4 class="mb-4">Contact Us</h4>

    <div class="card contact-info-card border-0 shadow-sm">
        <div class="card-body p-4">

            <p class="text-muted mb-4">
                Have questions about the library? Contact the library team.
            </p>

            <div class="row g-4">

                <!-- Address -->
                <div class="col-md-6">
                    <div class="contact-item">
                        <i class="bi bi-geo-alt-fill"></i>
                        <div>
                            <strong>Library Address</strong>
                            <p class="mb-0">
                                College Campus, Main Building
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="col-md-6">
                    <div class="contact-item">
                        <i class="bi bi-envelope-fill"></i>
                        <div>
                            <strong>Email</strong>
                            <p class="mb-0">
                                library@college.edu
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Phone -->
                <div class="col-md-6">
                    <div class="contact-item">
                        <i class="bi bi-telephone-fill"></i>
                        <div>
                            <strong>Phone</strong>
                            <p class="mb-0">
                                +91 XXXXX XXXXX
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Library Hours -->
                <div class="col-md-6">
                    <div class="contact-item">
                        <i class="bi bi-clock-fill"></i>
                        <div>
                            <strong>Library Hours</strong>
                            <p class="mb-0">
                                Monday - Friday: 9:00 AM - 5:00 PM
                            </p>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</section>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>