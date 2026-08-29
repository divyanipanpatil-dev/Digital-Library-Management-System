<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Dashboard";
$active = "dashboard";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$total_books = $conn->query("SELECT SUM(total_copies) AS t FROM books")->fetch_assoc()['t'] ?? 0;
$total_students = $conn->query("SELECT COUNT(*) AS c FROM students")->fetch_assoc()['c'];
$issued_count = $conn->query("SELECT COUNT(*) AS c FROM transactions WHERE status IN ('issued','return_requested')")->fetch_assoc()['c'];
$overdue_count = $conn->query("SELECT COUNT(*) AS c FROM transactions WHERE status IN ('issued','return_requested') AND due_date < CURDATE()")->fetch_assoc()['c'];
$pending_returns = $conn->query("SELECT COUNT(*) AS c FROM transactions WHERE status='return_requested'")->fetch_assoc()['c'];

$recent = $conn->query("
    SELECT t.*, b.title, m.full_name
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    JOIN students m ON t.student_id = m.student_id
    ORDER BY t.transaction_id DESC
    LIMIT 6
");
?>

<div class="container my-4">
  <h4 class="mb-4">Welcome, <?php echo clean($_SESSION['admin_name']); ?></h4>

  <div class="row row-cols-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
    <div class="col">
      <div class="stat-card bg-books">
        <div class="stat-number"><?php echo $total_books; ?></div>
        <div>Total Books</div>
      </div>
    </div>
    <div class="col">
      <div class="stat-card bg-students">
        <div class="stat-number"><?php echo $total_students; ?></div>
        <div>Total Students</div>
      </div>
    </div>
    <div class="col">
      <div class="stat-card bg-issued">
        <div class="stat-number"><?php echo $issued_count; ?></div>
        <div>Currently Issued</div>
      </div>
    </div>
    <div class="col">
      <div class="stat-card bg-overdue">
        <div class="stat-number"><?php echo $overdue_count; ?></div>
        <div>Overdue Books</div>
      </div>
    </div>
    <div class="col">
      <div class="stat-card bg-issued">
        <div class="stat-number"><?php echo $pending_returns; ?></div>
        <div>Pending Returns</div>
      </div>
    </div>
  </div>

  <div class="card">
    <div class="card-body">
      <h5 class="card-title mb-3">Recent Transactions</h5>
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead>
            <tr>
              <th>Book</th>
              <th>Student</th>
              <th>Issue Date</th>
              <th>Due Date</th>
              <th>Status</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($row = $recent->fetch_assoc()): ?>
              <tr>
                <td><?php echo clean($row['title']); ?></td>
                <td><?php echo clean($row['full_name']); ?></td>
                <td><?php echo $row['issue_date']; ?></td>
                <td><?php echo $row['due_date']; ?></td>
                <td>
                  <?php if ($row['status'] === 'issued'): ?>
                    <span class="badge badge-issued">Issued</span>
                  <?php elseif ($row['status'] === 'return_requested'): ?>
                    <span class="badge badge-issued">Return Requested</span>
                  <?php else: ?>
                    <span class="badge badge-returned">Returned</span>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
