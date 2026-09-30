<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "Notifications";
$active = "notifications";

$student_id = $_SESSION['student_id'];

// Mark all as read when student visits this page
$stmt = $conn->prepare("UPDATE notifications SET is_read = 'yes' WHERE student_id = ?");
$stmt->bind_param("i", $student_id);
$stmt->execute();

// Fetch notifications
$stmt = $conn->prepare("SELECT * FROM notifications WHERE student_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$notifications = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';
?>
<div class="container my-4">
  <h4 class="mb-3">Notifications</h4>

  <div class="card">
    <div class="card-body">
      <?php if ($notifications->num_rows === 0): ?>
        <p class="text-muted mb-0">No notifications found.</p>
      <?php else: ?>
        <div class="list-group list-group-flush">
          <?php while ($n = $notifications->fetch_assoc()): ?>
            <div class="list-group-item px-0 py-3">
              <div class="d-flex w-100 justify-content-between">
                <h6 class="mb-1 text-capitalize"><?php echo clean($n['type']); ?> Notification</h6>
                <small class="text-muted"><?php echo $n['created_at']; ?></small>
              </div>
              <p class="mb-1"><?php echo clean($n['message']); ?></p>
            </div>
          <?php endwhile; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>