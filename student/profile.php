<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "My Profile";
$active = "profile";

$student_id = $_SESSION['student_id'];
$success = "";
$error = "";

// Update phone / address
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'update_profile') {
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);

    $stmt = $conn->prepare("UPDATE students SET phone=?, address=? WHERE student_id=?");
    $stmt->execute([$phone, $address, $student_id]);
    $success = "Your profile has been updated.";
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'change_password') {
    $current  = $_POST['current_password'];
    $new      = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM students WHERE student_id = ?");
    $stmt->execute([$student_id]);
    $row = $stmt->get_result()->fetch_assoc();

    if (!$row || !password_verify($current, $row['password'])) {
        $error = "Your current password is incorrect.";
    } elseif (strlen($new) < 6) {
        $error = "New password must be at least 6 characters.";
    } elseif ($new !== $confirm) {
        $error = "New password and confirmation do not match.";
    } else {
        $hashed = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password=? WHERE student_id=?");
        $stmt->execute([$hashed, $student_id]);
        $success = "Your password has been changed.";
    }
}

// Always re-fetch the latest data after any update
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->execute([$student_id]);
$profile = $stmt->get_result()->fetch_assoc();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';
?>
<div class="container my-4">
  <h4 class="mb-3">My Profile</h4>

  <?php if ($success): ?><div class="alert alert-success"><?php echo clean($success); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo clean($error); ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">Profile Details</h5>
          <p class="mb-2"><strong>Full Name:</strong> <?php echo clean($profile['full_name']); ?></p>
          <p class="mb-2"><strong>Email:</strong> <?php echo clean($profile['email']); ?></p>
          <p class="mb-2">
            <strong>Status:</strong>
            <?php if ($profile['status'] === 'active'): ?>
              <span class="badge badge-available">Active</span>
            <?php else: ?>
              <span class="badge badge-unavailable">Inactive</span>
            <?php endif; ?>
          </p>
          <p class="mb-3"><strong>Member Since:</strong> <?php echo clean($profile['registration_date']); ?></p>

          <form method="POST">
            <input type="hidden" name="form" value="update_profile">
            <div class="mb-3">
              <label class="form-label">Phone</label>
              <input type="text" name="phone" class="form-control" value="<?php echo clean($profile['phone']); ?>">
            </div>
            <div class="mb-3">
              <label class="form-label">Address</label>
              <textarea name="address" class="form-control" rows="2"><?php echo clean($profile['address']); ?></textarea>
            </div>
            <button type="submit" class="btn btn-accent">Save Changes</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-6">
      <div class="card h-100">
        <div class="card-body">
          <h5 class="card-title mb-3">Change Password</h5>
          <form method="POST">
            <input type="hidden" name="form" value="change_password">
            <div class="mb-3">
              <label class="form-label">Current Password</label>
              <input type="password" name="current_password" class="form-control" required>
            </div>
            <div class="mb-3">
              <label class="form-label">New Password</label>
              <input type="password" name="new_password" class="form-control" minlength="6" required>
            </div>
            <div class="mb-3">
              <label class="form-label">Confirm New Password</label>
              <input type="password" name="confirm_password" class="form-control" minlength="6" required>
            </div>
            <button type="submit" class="btn btn-outline-primary">Change Password</button>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>