<?php
$root = "";
require_once __DIR__ . '/includes/functions.php';
$page_title = "Register";
include __DIR__ . '/includes/header.php';
$this_year = date('Y');
?>
<nav class="navbar navbar-dark app-navbar">
  <div class="container">
    <a class="navbar-brand" href="index.php"><i class="bi bi-book-half"></i> Digital Library</a>
  </div>
</nav>
<main class="page-main">

<div class="container">
  <div class="card auth-card" style="max-width:620px;">
    <div class="card-body p-4">
      <h4 class="mb-3 text-center">Student Registration</h4>
      <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger"><?php echo clean($_GET['error']); ?></div>
      <?php endif; ?>
      <form id="registerForm" action="register_process.php" method="POST">
        <div class="row g-3">
          <div class="col-md-6">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control" pattern="[0-9]{10}" title="10 digit phone number" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Alternate Number</label>
            <input type="text" name="alternate_phone" class="form-control" pattern="[0-9]{10}" title="10 digit phone number">
          </div>
          <div class="col-md-6">
            <label class="form-label">Branch</label>
            <input type="text" name="branch" class="form-control" placeholder="e.g. BCA">
          </div>
          <div class="col-md-6">
            <label class="form-label">Roll No</label>
            <input type="text" name="roll_no" class="form-control">
          </div>
          <div class="col-md-3">
            <label class="form-label">Admission Year (Start)</label>
            <input type="number" name="admission_year_start" class="form-control" min="2000" max="2100" value="<?php echo $this_year; ?>">
          </div>
          <div class="col-md-3">
            <label class="form-label">Admission Year (End)</label>
            <input type="number" name="admission_year_end" class="form-control" min="2000" max="2100" value="<?php echo $this_year + 3; ?>">
          </div>
          <div class="col-md-6">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="1"></textarea>
          </div>
          <div class="col-md-6"></div>
          <div class="col-md-6">
            <label class="form-label">Password</label>
            <input type="password" id="password" name="password" class="form-control" minlength="6" required>
          </div>
          <div class="col-md-6">
            <label class="form-label">Confirm Password</label>
            <input type="password" id="confirm_password" name="confirm_password" class="form-control" minlength="6" required>
          </div>
        </div>
        <button type="submit" class="btn btn-accent w-100 mt-4">Register</button>
      </form>
      <p class="text-center mt-3 mb-0">Already a student? <a href="login.php">Login here</a></p>
    </div>
  </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
