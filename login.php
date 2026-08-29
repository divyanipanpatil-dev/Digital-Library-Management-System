<?php
$root = "";
require_once __DIR__ . '/includes/functions.php';
$page_title = "Login";
include __DIR__ . '/includes/header.php';
?>
<nav class="navbar navbar-dark app-navbar">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-book-half"></i> Digital Library</a>
  </div>
</nav>

<div class="container">
  <div class="card auth-card">
    <div class="card-body p-4">
      <h4 class="mb-3 text-center">Login</h4>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
          <?php echo clean($_GET['error']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>
      <?php if (isset($_GET['msg'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <?php echo clean($_GET['msg']); ?>
          <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
      <?php endif; ?>

      <form action="login_process.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Login as</label>
          <div class="btn-group w-100" role="group">
            <input type="radio" class="btn-check" name="role" id="roleAdmin" value="admin" checked>
            <label class="btn btn-outline-primary" for="roleAdmin">Librarian</label>

            <input type="radio" class="btn-check" name="role" id="roleStudent" value="student">
            <label class="btn btn-outline-primary" for="roleStudent">Student</label>
          </div>
        </div>
        <div class="mb-3">
          <label class="form-label">Username / Email</label>
          <input type="text" name="identifier" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-accent w-100">Login</button>
      </form>
      <p class="text-center mt-3 mb-0">
        New student? <a href="register.php">Register here</a>
      </p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
