<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Track Book";
$active = "track";

$book_id = isset($_GET['book_id']) ? (int)$_GET['book_id'] : 0;

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$books_list = $conn->query("SELECT book_id, title, author FROM books ORDER BY title")->fetch_all(MYSQLI_ASSOC);

$book = null;
$history = [];
if ($book_id > 0) {
    $stmt = $conn->prepare("
        SELECT b.*, c.category_name
        FROM books b LEFT JOIN categories c ON b.category_id = c.category_id
        WHERE b.book_id = ?
    ");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    if ($book) {
        $stmt = $conn->prepare("
            SELECT t.*, s.full_name, s.email
            FROM transactions t JOIN students s ON t.student_id = s.student_id
            WHERE t.book_id = ?
            ORDER BY t.transaction_id DESC
        ");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();
        $history = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>
<div class="container my-4">
  <h4 class="mb-3">Track a Book</h4>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-8">
          <label class="form-label">Select a Book</label>
          <select name="book_id" class="form-select" required>
            <option value="">-- Choose a book --</option>
            <?php foreach ($books_list as $b): ?>
              <option value="<?php echo $b['book_id']; ?>" <?php echo ($b['book_id']==$book_id)?'selected':''; ?>>
                #<?php echo $b['book_id']; ?> &mdash; <?php echo clean($b['title']); ?> (<?php echo clean($b['author']); ?>)
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-4">
          <button type="submit" class="btn btn-accent w-100">View History</button>
        </div>
      </form>
    </div>
  </div>

  <?php if ($book_id > 0 && !$book): ?>
    <div class="alert alert-danger">No book found with that ID.</div>
  <?php endif; ?>

  <?php if ($book): ?>
    <div class="card mb-4">
      <div class="card-body">
        <h5 class="card-title mb-3">Book Details</h5>
        <div class="row g-2">
          <div class="col-md-6"><strong>Book ID:</strong> #<?php echo (int)$book['book_id']; ?></div>
          <div class="col-md-6"><strong>Title:</strong> <?php echo clean($book['title']); ?></div>
          <div class="col-md-6"><strong>Author:</strong> <?php echo clean($book['author']); ?></div>
          <div class="col-md-6"><strong>ISBN:</strong> <?php echo clean($book['isbn']) ?: '—'; ?></div>
          <div class="col-md-6"><strong>Category:</strong> <?php echo clean($book['category_name']) ?: '—'; ?></div>
          <div class="col-md-6"><strong>Publisher:</strong> <?php echo clean($book['publisher']) ?: '—'; ?></div>
          <div class="col-md-6"><strong>Edition:</strong> <?php echo clean($book['edition']) ?: '—'; ?></div>
          <div class="col-md-6"><strong>Shelf Location:</strong> <?php echo clean($book['shelf_location']) ?: '—'; ?></div>
          <div class="col-md-6"><strong>Copies:</strong> <?php echo (int)$book['available_copies']; ?> available / <?php echo (int)$book['total_copies']; ?> total</div>
          <div class="col-md-6"><strong>Added:</strong> <?php echo clean($book['added_date']); ?></div>
        </div>
      </div>
    </div>

    <div class="card">
      <div class="card-body table-responsive">
        <h5 class="card-title mb-3">Full History (<?php echo count($history); ?> record<?php echo count($history)==1?'':'s'; ?>)</h5>
        <table class="table table-hover align-middle">
          <thead>
            <tr><th>Student</th><th>Issue Date</th><th>Due Date</th><th>Return Date</th><th>Status</th><th>Fine</th></tr>
          </thead>
          <tbody>
            <?php if (empty($history)): ?>
              <tr><td colspan="6" class="text-center text-muted">This book has never been issued.</td></tr>
            <?php endif; ?>
            <?php foreach ($history as $h): ?>
              <tr>
                <td><?php echo clean($h['full_name']); ?><br><small class="text-muted"><?php echo clean($h['email']); ?></small></td>
                <td><?php echo $h['issue_date']; ?></td>
                <td><?php echo $h['due_date']; ?></td>
                <td><?php echo $h['return_date'] ?: '—'; ?></td>
                <td>
                  <?php if ($h['status']==='issued'): ?><span class="badge badge-issued">Issued</span>
                  <?php elseif ($h['status']==='return_requested'): ?><span class="badge badge-issued">Return Requested</span>
                  <?php else: ?><span class="badge badge-returned">Returned</span><?php endif; ?>
                </td>
                <td><?php echo $h['fine_amount'] > 0 ? 'Rs. '.number_format($h['fine_amount'],2).($h['fine_paid']=='yes'?' (paid)':' (unpaid)') : '—'; ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  <?php endif; ?>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>