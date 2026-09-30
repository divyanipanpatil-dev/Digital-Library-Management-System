<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "Return Book";
$active = "return";
$student_id = $_SESSION['student_id'];
$success = "";
$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['transaction_id'])) {
    $transaction_id = (int)$_POST['transaction_id'];

    $stmt = $conn->prepare("SELECT * FROM transactions WHERE transaction_id = ? AND student_id = ? AND status = 'issued'");
    $stmt->bind_param("ii", $transaction_id, $student_id);
    $stmt->execute();
    $txn = $stmt->get_result()->fetch_assoc();

    if ($txn) {
        $stmt = $conn->prepare("UPDATE transactions SET status = 'return_requested' WHERE transaction_id = ?");
        $stmt->bind_param("i", $transaction_id);
        $stmt->execute();
        $success = "Return request submitted. Please hand the book to the librarian to complete the return.";
    } else {
        $error = "That book could not be found in your active loans.";
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';

$stmt = $conn->prepare("
    SELECT t.transaction_id, t.due_date, t.issue_date, t.status, b.title, b.author
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    WHERE t.student_id = ? AND t.status IN ('issued', 'return_requested')
    ORDER BY t.due_date ASC
");
$stmt->bind_param("i", $student_id);
$stmt->execute();
$books = $stmt->get_result();
?>
<div class="container my-4">
  <h4 class="mb-3">Return a Book</h4>

  <?php if ($success): ?><div class="alert alert-success"><?php echo clean($success); ?></div><?php endif; ?>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo clean($error); ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Title</th><th>Author</th><th>Due Date</th><th>Fine (if today)</th><th>Action</th></tr>
        </thead>
        <tbody>
          <?php if ($books->num_rows === 0): ?>
            <tr><td colspan="5" class="text-center text-muted">You have no books to return.</td></tr>
          <?php endif; ?>
          <?php while ($row = $books->fetch_assoc()):
              $fine_today = calculate_fine($row['due_date']);
          ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo clean($row['author']); ?></td>
            <td>
              <?php echo $row['due_date']; ?>
              <?php if ($fine_today > 0): ?><span class="badge badge-overdue ms-1">Overdue</span><?php endif; ?>
            </td>
            <td><?php echo $fine_today > 0 ? 'Rs. ' . $fine_today : '—'; ?></td>
            <td>
              <?php if ($row['status'] === 'return_requested'): ?>
                <span class="badge badge-issued">Pending librarian confirmation</span>
              <?php else: ?>
                <form method="POST">
                  <input type="hidden" name="transaction_id" value="<?php echo $row['transaction_id']; ?>">
                  <button type="submit" class="btn btn-sm btn-accent" data-confirm="Submit a return request for this book?">
                    Request Return
                  </button>
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