<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "View Issued Books";
$active = "issued";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';

$student_id = $_SESSION['student_id'];

$stmt = $conn->prepare("
    SELECT t.*, b.title, b.author
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    WHERE t.student_id = ?
    ORDER BY t.transaction_id DESC
");
$stmt->execute([$student_id]);
$all_txns = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$current = array_filter($all_txns, fn($t) => in_array($t['status'], ['issued', 'return_requested']));
$history = array_filter($all_txns, fn($t) => $t['status'] === 'returned');
?>
<div class="container my-4">
  <h4 class="mb-3">Currently Issued Books</h4>
  <div class="card mb-4">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Title</th><th>Author</th><th>Issue Date</th><th>Due Date</th><th>Fine (if today)</th><th>Status</th></tr></thead>
        <tbody>
          <?php if (empty($current)): ?>
            <tr><td colspan="6" class="text-center text-muted">No books currently issued.</td></tr>
          <?php endif; ?>
          <?php foreach ($current as $row):
              $fine_today = calculate_fine($row['due_date']);
          ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo clean($row['author']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td>
              <?php echo $row['due_date']; ?>
              <?php if ($fine_today > 0): ?><span class="badge badge-overdue ms-1">Overdue</span><?php endif; ?>
            </td>
            <td><?php echo $fine_today > 0 ? 'Rs. ' . $fine_today : '—'; ?></td>
            <td>
              <?php if ($row['status'] === 'return_requested'): ?>
                <span class="badge badge-issued">Return Requested</span>
              <?php else: ?>
                <span class="badge badge-issued">Issued</span>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="text-muted small mb-0">To return a book, go to <a href="return_book.php">Return Book</a>.</p>
    </div>
  </div>

  <h4 class="mb-3">Borrowing History</h4>
  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Title</th><th>Issue Date</th><th>Return Date</th><th>Fine</th></tr></thead>
        <tbody>
          <?php if (empty($history)): ?>
            <tr><td colspan="4" class="text-center text-muted">No history yet.</td></tr>
          <?php endif; ?>
          <?php foreach ($history as $row): ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td><?php echo $row['return_date']; ?></td>
            <td><?php echo $row['fine_amount'] > 0 ? 'Rs. '.number_format($row['fine_amount'],2).($row['fine_paid']=='yes'?' (paid)':' (unpaid)') : '—'; ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
