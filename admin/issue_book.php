<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Issue Book";
$active = "issue";

define('MAX_BOOKS_PER_STUDENT', 3);
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = (int)$_POST['student_id'];
    $book_id = (int)$_POST['book_id'];
    $issue_date = $_POST['issue_date'];

    // Validate book availability
    $stmt = $conn->prepare("SELECT available_copies FROM books WHERE book_id = ?");
    $stmt->bind_param("i", $book_id);
    $stmt->execute();
    $book = $stmt->get_result()->fetch_assoc();

    // Validate student's current active loan count (a pending return request still counts —
    // the book is physically with the student until the librarian confirms receipt)
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM transactions WHERE student_id = ? AND status IN ('issued','return_requested')");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    $active_count = $stmt->get_result()->fetch_assoc()['c'];

    // Check duplicate active issue of same book to same student
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM transactions WHERE student_id = ? AND book_id = ? AND status IN ('issued','return_requested')");
    $stmt->bind_param("ii", $student_id, $book_id);
    $stmt->execute();
    $dup = $stmt->get_result()->fetch_assoc()['c'];

    if (!$book || $book['available_copies'] < 1) {
        $error = "This book is not currently available.";
    } elseif ($active_count >= MAX_BOOKS_PER_STUDENT) {
        $error = "This student already has the maximum of " . MAX_BOOKS_PER_STUDENT . " books issued.";
    } elseif ($dup > 0) {
        $error = "This student already has this book issued.";
    } else {
        $due_date = date('Y-m-d', strtotime($issue_date . ' + ' . LOAN_PERIOD_DAYS . ' days'));

        $stmt = $conn->prepare("INSERT INTO transactions (book_id, student_id, issue_date, due_date, status) VALUES (?, ?, ?, ?, 'issued')");
        $stmt->bind_param("iiss", $book_id, $student_id, $issue_date, $due_date);
        $stmt->execute();

        $stmt = $conn->prepare("UPDATE books SET available_copies = available_copies - 1 WHERE book_id = ?");
        $stmt->bind_param("i", $book_id);
        $stmt->execute();

        $success = "Book issued successfully. Due date: " . $due_date;
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$students = $conn->query("SELECT student_id, full_name, email FROM students WHERE status='active' ORDER BY full_name");
$books = $conn->query("SELECT book_id, title, author, available_copies FROM books WHERE available_copies > 0 ORDER BY title");
?>
<div class="container my-4">
  <h4 class="mb-3">Issue a Book</h4>

  <?php if ($error): ?><div class="alert alert-danger"><?php echo clean($error); ?></div><?php endif; ?>
  <?php if ($success): ?><div class="alert alert-success"><?php echo clean($success); ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body">
      <form method="POST" class="row g-3">
        <div class="col-md-6">
          <label class="form-label">Student</label>
          <select name="student_id" class="form-select" required>
            <option value="">-- Select Student --</option>
            <?php while ($m = $students->fetch_assoc()): ?>
              <option value="<?php echo $m['student_id']; ?>"><?php echo clean($m['full_name']) . " (" . clean($m['email']) . ")"; ?></option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Book</label>
          <select name="book_id" class="form-select" required>
            <option value="">-- Select Book --</option>
            <?php while ($b = $books->fetch_assoc()): ?>
              <option value="<?php echo $b['book_id']; ?>">
                <?php echo clean($b['title']) . " — " . clean($b['author']) . " (" . $b['available_copies'] . " available)"; ?>
              </option>
            <?php endwhile; ?>
          </select>
        </div>
        <div class="col-md-6">
          <label class="form-label">Issue Date</label>
          <input type="date" id="issue_date" name="issue_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
        </div>
        <div class="col-md-6">
          <label class="form-label">Due Date (auto-calculated, <?php echo LOAN_PERIOD_DAYS; ?> days)</label>
          <div class="form-control bg-light" id="due_date_display"><?php echo date('Y-m-d', strtotime('+'.LOAN_PERIOD_DAYS.' days')); ?></div>
        </div>
        <div class="col-12">
          <button type="submit" class="btn btn-accent">Issue Book</button>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>