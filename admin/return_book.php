<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Return Book";
$active = "return";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    $transaction_id = (int)$_POST['transaction_id'];
    $fine_paid = isset($_POST['fine_paid']) ? 'yes' : 'no';
    $return_date = date('Y-m-d');

    $stmt = $conn->prepare("SELECT t.*, b.title FROM transactions t JOIN books b ON t.book_id=b.book_id WHERE t.transaction_id = ? AND t.status IN ('issued','return_requested')");
    $stmt->execute([$transaction_id]);
    $txn = $stmt->get_result()->fetch_assoc();

    if ($txn) {
        $fine = calculate_fine($txn['due_date'], $return_date);

        $stmt = $conn->prepare("UPDATE transactions SET return_date=?, fine_amount=?, fine_paid=?, status='returned' WHERE transaction_id=?");
        $stmt->execute([$return_date, $fine, $fine_paid, $transaction_id]);

        $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies + 1 WHERE book_id = ?");
        $stmt->execute([$txn['book_id']]);

        // NEW: notify the student that their return has been confirmed
        $notify_msg = 'Your book "' . $txn['title'] . '" has been returned successfully.';
        if ($fine > 0) {
            $notify_msg .= ' Fine due: Rs. ' . $fine . ($fine_paid === 'yes' ? ' (paid).' : ' (unpaid).');
        }
        notify($conn, $txn['student_id'], $notify_msg, 'return_confirmed');

        $success = "Book returned successfully." . ($fine > 0 ? " Fine due: Rs. " . $fine : "");
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$issued = $conn->query("
    SELECT t.transaction_id, t.due_date, t.issue_date, t.status, b.title, s.full_name
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    JOIN students s ON t.student_id = s.student_id
    WHERE t.status IN ('issued','return_requested')
    ORDER BY (t.status = 'return_requested') DESC, t.due_date ASC
");
?>
<div class="container my-4">
  <h4 class="mb-3">Return a Book</h4>
  <?php if ($success): ?><div class="alert alert-success"><?php echo clean($success); ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Book</th><th>Student</th><th>Issue Date</th><th>Due Date</th><th>Fine (if today)</th><th>Requested?</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($issued->num_rows === 0): ?>
            <tr><td colspan="7" class="text-center text-muted">No books currently issued.</td></tr>
          <?php endif; ?>
          <?php while ($row = $issued->fetch_assoc()):
              $fine_today = calculate_fine($row['due_date']);
          ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo clean($row['full_name']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td>
              <?php echo $row['due_date']; ?>
              <?php if ($fine_today > 0): ?>
                <span class="badge badge-overdue ms-1">Overdue</span>
              <?php endif; ?>
            </td>
            <td><?php echo $fine_today > 0 ? 'Rs. ' . $fine_today : '—'; ?></td>
            <td>
              <?php if ($row['status'] === 'return_requested'): ?>
                <span class="badge badge-issued">Yes — by student</span>
              <?php else: ?>
                <span class="text-muted">—</span>
              <?php endif; ?>
            </td>
            <td>
              <form method="POST" class="d-flex align-items-center gap-2">
                <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                <?php if ($fine_today > 0): ?>
                  <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="fine_paid" id="fp<?php echo $row['transaction_id']; ?>">
                    <label class="form-check-label small" for="fp<?php echo $row['transaction_id']; ?>">Fine paid</label>
                  </div>
                <?php endif; ?>
                <button type="submit" class="btn btn-sm btn-accent">Return</button>
              </form>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>
