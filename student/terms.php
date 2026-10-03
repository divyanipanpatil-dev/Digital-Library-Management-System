<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "Terms and Conditions";
$active = "terms";

$error = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['agree']) && $_POST['agree'] === 'yes') {
        $stmt = $conn->prepare("UPDATE students SET terms_accepted = 'yes' WHERE student_id = ?");
        $stmt->bind_param("i", $_SESSION['student_id']);
        $stmt->execute();
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "You must check the box to confirm you agree before continuing.";
    }
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';
?>
<div class="container my-4">
  <h4 class="mb-3">Terms and Conditions</h4>
  <?php if ($error): ?><div class="alert alert-danger"><?php echo clean($error); ?></div><?php endif; ?>

  <div class="card">
    <div class="card-body">
      <div class="terms-box mb-4">
        <h6>1. Borrowing Books</h6>
        <p>Books are issued for a loan period of <?php echo LOAN_PERIOD_DAYS; ?> days from the date of issue. A student may have a maximum of 3 books issued at any time.</p>

        <h6>2. Fines</h6>
        <p>A fine of Rs. <?php echo FINE_PER_DAY; ?> per day applies to any book returned after its due date. Fines must be settled with the librarian.</p>

        <h6>3. Returning Books</h6>
        <p>To return a book, submit a return request from your dashboard. The book is only considered returned once the librarian confirms receipt in person.</p>

        <h6>4. Care of Books</h6>
        <p>Students are responsible for the condition of books issued to them. Lost or damaged books must be reported to the librarian immediately and may be charged for.</p>

        <h6>5. Notifications</h6>
        <p>The system will notify you when a book is issued to you, when your return request is confirmed, and when a due date is approaching or has passed.</p>

        <h6>6. Account &amp; Membership</h6>
        <p>Your library account is valid for the membership period set by the librarian. Accounts may be suspended for repeated overdue returns or unpaid fines.</p>
      </div>

      <form method="POST">
        <div class="form-check mb-3">
          <input class="form-check-input" type="checkbox" id="termsCheck" name="agree" value="yes" required>
          <label class="form-check-label" for="termsCheck">
            I have read and agree to the above Terms and Conditions.
          </label>
        </div>
        <button type="submit" class="btn btn-accent">Continue to Dashboard</button>
      </form>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>