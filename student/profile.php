<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "My Profile";
$active = "profile";

$student_id = $_SESSION['student_id'];
$success = "";
$error = "";

// Update phone / address / branch / roll no / admission year / alternate number
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'update_profile') {
    $phone = trim($_POST['phone']);
    $alternate_phone = trim($_POST['alternate_phone']);
    $address = trim($_POST['address']);
    $branch = trim($_POST['branch']);
    $roll_no = trim($_POST['roll_no']);
    $ays = $_POST['admission_year_start'] !== '' ? (int)$_POST['admission_year_start'] : null;
    $aye = $_POST['admission_year_end'] !== '' ? (int)$_POST['admission_year_end'] : null;

    $stmt = $conn->prepare("UPDATE students SET phone=?, alternate_phone=?, address=?, branch=?, roll_no=?, admission_year_start=?, admission_year_end=? WHERE student_id=?");
    $stmt->bind_param("sssssiii", $phone, $alternate_phone, $address, $branch, $roll_no, $ays, $aye, $student_id);
    $stmt->execute();
    $success = "Your profile has been updated.";
}

// Change password
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'change_password') {
    $current  = $_POST['current_password'];
    $new      = $_POST['new_password'];
    $confirm  = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM students WHERE student_id = ?");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
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
        $stmt->bind_param("si", $hashed, $student_id);
        $stmt->execute();
        $success = "Your password has been changed.";
    }
}

// Always re-fetch the latest data after any update
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$profile = $stmt->get_result()->fetch_assoc();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';
?>
<div class="container my-4">
  <h4 class="mb-3">My Profile</h4>

  <?php if ($success): ?><div class="alert alert-success"><?php echo clean($success); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo clean($error); ?></div><?php endif; ?>

  <div class="row g-4">
    <div class="col-lg-7">
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
          <p class="mb-3">
            <strong>Membership:</strong>
            <?php echo $profile['membership_start_date'] ? clean($profile['membership_start_date']) : 'Not set'; ?>
            &rarr;
            <?php echo $profile['membership_end_date'] ? clean($profile['membership_end_date']) : 'Not set'; ?>
            <?php if ($profile['membership_end_date'] && strtotime($profile['membership_end_date']) < strtotime(date('Y-m-d'))): ?>
              <span class="badge badge-overdue ms-1">Expired</span>
            <?php elseif ($profile['membership_end_date']): ?>
              <span class="badge badge-available ms-1">Active</span>
            <?php endif; ?>
            <br><small class="text-muted">Set by the librarian &mdash; contact them to renew or correct this.</small>
          </p>

          <form method="POST">
            <input type="hidden" name="form" value="update_profile">
            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Phone</label>
                <input type="text" name="phone" class="form-control" value="<?php echo clean($profile['phone']); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Alternate Number</label>
                <input type="text" name="alternate_phone" class="form-control" value="<?php echo clean($profile['alternate_phone']); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Branch</label>
                <input type="text" name="branch" class="form-control" placeholder="e.g. BCA" value="<?php echo clean($profile['branch']); ?>">
              </div>
              <div class="col-md-6">
                <label class="form-label">Roll No</label>
                <input type="text" name="roll_no" class="form-control" value="<?php echo clean($profile['roll_no']); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Admission Year (Start)</label>
                <input type="number" name="admission_year_start" class="form-control" min="2000" max="2100" value="<?php echo clean($profile['admission_year_start']); ?>">
              </div>
              <div class="col-md-3">
                <label class="form-label">Admission Year (End)</label>
                <input type="number" name="admission_year_end" class="form-control" min="2000" max="2100" value="<?php echo clean($profile['admission_year_end']); ?>">
              </div>
              <div class="col-md-12">
                <label class="form-label">Address</label>
                <textarea name="address" class="form-control" rows="2"><?php echo clean($profile['address']); ?></textarea>
              </div>
            </div>
            <button type="submit" class="btn btn-accent mt-3">Save Changes</button>
          </form>
        </div>
      </div>
    </div>

    <div class="col-lg-5">
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