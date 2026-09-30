<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "My Dashboard";
$active = "dashboard";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';

$student_id = $_SESSION['student_id'];

// 1. Fetch count of currently issued books
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM transactions WHERE student_id=? AND status IN ('issued','return_requested')");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$issued_count = $stmt->get_result()->fetch_assoc()['c'];

// 2. Fetch count of overdue books
$stmt = $conn->prepare("SELECT COUNT(*) AS c FROM transactions WHERE student_id=? AND status IN ('issued','return_requested') AND due_date < CURDATE()");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$overdue_count = $stmt->get_result()->fetch_assoc()['c'];

// 3. Fetch overdue records to calculate fine
$stmt = $conn->prepare("SELECT due_date FROM transactions WHERE student_id=? AND status IN ('issued','return_requested') AND due_date < CURDATE()");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$overdue_rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_fine = 0;
foreach ($overdue_rows as $r) {
    $total_fine += calculate_fine($r['due_date']);
}
?>
<div class="container my-4">
  <h4 class="mb-4">Welcome, <?php echo clean($_SESSION['student_name']); ?></h4>

  <div class="row g-3 mb-4">
    <div class="col-md-4 col-sm-6">
      <div class="stat-card bg-issued">
        <div class="stat-number"><?php echo $issued_count; ?></div>
        <div>Books Currently Issued</div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6">
      <div class="stat-card bg-overdue">
        <div class="stat-number"><?php echo $overdue_count; ?></div>
        <div>Overdue Books</div>
      </div>
    </div>
    <div class="col-md-4 col-sm-6">
      <div class="stat-card bg-students">
        <div class="stat-number">Rs. <?php echo $total_fine; ?></div>
        <div>Estimated Fine Due</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title">Quick Links</h5>
      <a href="search_books.php" class="btn btn-outline-primary me-2 mb-2">Search Book</a>
      <a href="view_issued.php" class="btn btn-outline-primary me-2 mb-2">View Issued Books</a>
      <a href="return_book.php" class="btn btn-outline-primary me-2 mb-2">Return Book</a>
      <a href="book_status.php" class="btn btn-outline-primary mb-2">Book Status</a>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>