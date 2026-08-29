<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Reports";
$active = "reports";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$filter = $_GET['filter'] ?? 'all';
$sql = "
    SELECT t.*, b.title, s.full_name
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    JOIN students s ON t.student_id = s.student_id
";
if ($filter === 'issued') {
    $sql .= " WHERE t.status='issued'";
} elseif ($filter === 'requested') {
    $sql .= " WHERE t.status='return_requested'";
} elseif ($filter === 'overdue') {
    $sql .= " WHERE t.status IN ('issued','return_requested') AND t.due_date < CURDATE()";
} elseif ($filter === 'returned') {
    $sql .= " WHERE t.status='returned'";
}
$sql .= " ORDER BY t.transaction_id DESC";
$transactions = $conn->query($sql);
?>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Reports — Issued, Returned &amp; Overdue Books</h4>
    <div class="btn-group flex-wrap">
      <a href="?filter=all" class="btn btn-sm btn-outline-primary <?php echo $filter=='all'?'active':''; ?>">All</a>
      <a href="?filter=issued" class="btn btn-sm btn-outline-primary <?php echo $filter=='issued'?'active':''; ?>">Issued</a>
      <a href="?filter=requested" class="btn btn-sm btn-outline-primary <?php echo $filter=='requested'?'active':''; ?>">Return Requested</a>
      <a href="?filter=overdue" class="btn btn-sm btn-outline-primary <?php echo $filter=='overdue'?'active':''; ?>">Overdue</a>
      <a href="?filter=returned" class="btn btn-sm btn-outline-primary <?php echo $filter=='returned'?'active':''; ?>">Returned</a>
    </div>
  </div>

  <input type="text" class="form-control table-search-input mb-3" placeholder="Search reports...">

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover searchable-table align-middle">
        <thead>
          <tr><th>Book</th><th>Student</th><th>Issue</th><th>Due</th><th>Return</th><th>Fine</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php while ($row = $transactions->fetch_assoc()): ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo clean($row['full_name']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td><?php echo $row['return_date'] ?? '—'; ?></td>
            <td>
              <?php echo $row['fine_amount'] > 0 ? 'Rs. ' . number_format($row['fine_amount'],2) . ($row['fine_paid']=='yes' ? ' (paid)' : ' (unpaid)') : '—'; ?>
            </td>
            <td>
              <?php
                $is_overdue = in_array($row['status'], ['issued','return_requested']) && strtotime($row['due_date']) < strtotime(date('Y-m-d'));
              ?>
              <?php if ($row['status'] === 'returned'): ?>
                <span class="badge badge-returned">Returned</span>
              <?php elseif ($row['status'] === 'return_requested'): ?>
                <span class="badge badge-issued">Return Requested</span>
                <?php if ($is_overdue): ?><span class="badge badge-overdue">Overdue</span><?php endif; ?>
              <?php else: ?>
                <?php if ($is_overdue): ?>
                  <span class="badge badge-overdue">Overdue</span>
                <?php else: ?>
                  <span class="badge badge-issued">Issued</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
