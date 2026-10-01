<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "Return Book";
$active = "return";

$student_id = $_SESSION['student_id'];
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    $transaction_id = (int)$_POST['transaction_id'];

    $stmt = $conn->prepare("SELECT t.*, b.title FROM transactions t JOIN books b ON t.book_id=b.book_id WHERE t.transaction_id = ? AND t.student_id = ? AND t.status = 'issued'");
    $stmt->bind_param("ii", $transaction_id, $student_id);
    $stmt->execute();
    $txn = $stmt->get_result()->fetch_assoc();

    if ($txn) {
        $stmt = $conn->prepare("UPDATE transactions SET status = 'return_requested' WHERE transaction_id = ?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();

        // Notify the student that their return request was sent
        if (function_exists('notify')) {
            notify($conn, $student_id, 'Your return request for "' . $txn['title'] . '" has been sent to the librarian.', 'return_requested');
        }

        $success = "Return request submitted. Please hand the book to the librarian.";
    }
}

$stmt = $conn->prepare("
    SELECT t.transaction_id, t.due_date, t.status, b.title, b.author
    FROM transactions t JOIN books b ON t.book_id = b.book_id
    WHERE t.student_id = ? AND t.status IN ('issued','return_requested')
    ORDER BY t.due_date ASC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$issued = $stmt->get_result();

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';
?>
<div class="container my-4">
  <h4 class="mb-3">Return a Book</h4>
  <?php if ($success): ?><div class="alert alert-success"><?php echo clean($success); ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Title</th><th>Author</th><th>Due Date</th><th>Fine (if today)</th><th>Action</th></tr></thead>
        <tbody>
          <?php if ($issued->num_rows === 0): ?><tr><td colspan="5" class="text-center text-muted">No books to return.</td></tr><?php endif; ?>
          <?php while ($row = $issued->fetch_assoc()): $fine_today = calculate_fine($row['due_date']); ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo clean($row['author']); ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td><?php echo $fine_today > 0 ? 'Rs. ' . $fine_today : '—'; ?></td>
            <td>
              <?php if ($row['status'] === 'return_requested'): ?>
                <span class="badge badge-neutral">Pending Librarian Confirmation</span>
              <?php else: ?>
                <form method="POST">
                  <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                  <button type="submit" class="btn btn-sm btn-accent">Request Return</button>
                </form>
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