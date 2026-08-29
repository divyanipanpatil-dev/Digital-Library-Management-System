<?php
$root = "";
require_once __DIR__ . '/includes/functions.php';
$page_title = "Register";
include __DIR__ . '/includes/header.php';
?>
<nav class="navbar navbar-dark app-navbar">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-book-half"></i> Digital Library</a>
  </div>
</nav>

<div class="container">
  <div class="card auth-card" style="max-width:520px;">
    <div class="card-body p-4">
      <h4 class="mb-3 text-center">Student Registration</h4>

      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo clean($_GET['error']); ?></div>
      <?php endif; ?>

      <form id="registerForm" action="register_process.php" method="POST">
        <div class="mb-3">
          <label class="form-label">Full Name</label>
          <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Email</label>
          <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Phone</label>
          <input type="text" name="phone" class="form-control" pattern="[0-9]{10}" title="10 digit phone number" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Address</label>
          <textarea name="address" class="form-control" rows="2"></textarea>
        </div>
        <div class="mb-3">
          <label class="form-label">Password</label>
          <input type="password" id="password" name="password" class="form-control" minlength="6" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Confirm Password</label>
          <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="6" required>
        </div>
        <button type="submit" class="btn btn-accent w-100">Register</button>
      </form>
      <p class="text-center mt-3 mb-0">
        Already a student? <a href="login.php">Login here</a>
      </p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
