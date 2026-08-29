<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Manage Students";
$active = "students";

// Add a new student directly (librarian-created account)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'add') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: manage_students.php?error=A student with this email already exists.");
        exit();
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("INSERT INTO students (full_name, email, password, phone, address) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$full_name, $email, $hashed, $phone, $address]);

    header("Location: manage_students.php?msg=Student added successfully.");
    exit();
}

// Edit an existing student's details (and optionally reset their password)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'edit') {
    $student_id = (int)$_POST['student_id'];
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $new_password = trim($_POST['password'] ?? '');

    $stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ? AND student_id != ?");
    $stmt->execute([$email, $student_id]);
    if ($stmt->get_result()->num_rows > 0) {
        header("Location: manage_students.php?error=Another student already uses that email.");
        exit();
    }

    if ($new_password !== '') {
        $hashed = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET full_name=?, email=?, phone=?, address=?, password=? WHERE student_id=?");
        $stmt->execute([$full_name, $email, $phone, $address, $hashed, $student_id]);
    } else {
        $stmt = $conn->prepare("UPDATE students SET full_name=?, email=?, phone=?, address=? WHERE student_id=?");
        $stmt->execute([$full_name, $email, $phone, $address, $student_id]);
    }

    header("Location: manage_students.php?msg=Student updated successfully.");
    exit();
}

if (isset($_GET['toggle'])) {
    $id = (int)$_GET['toggle'];
    $stmt = $conn->prepare("SELECT status FROM students WHERE student_id = ?");
    $stmt->execute([$id]);
    $s = $stmt->get_result()->fetch_assoc();
    if ($s) {
        $new_status = $s['status'] === 'active' ? 'inactive' : 'active';
        $stmt = $conn->prepare("UPDATE students SET status = ? WHERE student_id = ?");
        $stmt->execute([$new_status, $id]);
    }
    header("Location: manage_students.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM transactions WHERE student_id = ? AND status IN ('issued','return_requested')");
    $stmt->execute([$id]);
    $active_loans = $stmt->get_result()->fetch_assoc()['c'];

    if ($active_loans > 0) {
        header("Location: manage_students.php?error=Cannot delete: this student has books currently issued.");
        exit();
    }
    $stmt = $conn->prepare("DELETE FROM students WHERE student_id = ?");
    $stmt->execute([$id]);
    header("Location: manage_students.php?msg=Student deleted.");
    exit();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$students = $conn->query("SELECT * FROM students ORDER BY full_name");
$students_list = $students->fetch_all(MYSQLI_ASSOC);
?>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manage Students</h4>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addStudentModal">
      <i class="bi bi-person-plus"></i> Add New Student
    </button>
  </div>

  <?php if (isset($_GET['msg'])): ?><div class="alert alert-success"><?php echo clean($_GET['msg']); ?></div><?php endif; ?>
  <?php if (isset($_GET['error'])): ?><div class="alert alert-danger"><?php echo clean($_GET['error']); ?></div><?php endif; ?>

  <input type="text" class="form-control table-search-input mb-3" placeholder="Search students...">

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover searchable-table align-middle">
        <thead>
          <tr><th>Name</th><th>Email</th><th>Phone</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
        </thead>
        <tbody>
          <?php foreach ($students_list as $s): ?>
          <tr>
            <td><?php echo clean($s['full_name']); ?></td>
            <td><?php echo clean($s['email']); ?></td>
            <td><?php echo clean($s['phone']); ?></td>
            <td><?php echo $s['registration_date']; ?></td>
            <td>
              <?php if ($s['status']==='active'): ?>
                <span class="badge badge-available">Active</span>
              <?php else: ?>
                <span class="badge badge-unavailable">Inactive</span>
              <?php endif; ?>
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editStudentModal<?php echo $s['student_id']; ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <a href="manage_students.php?toggle=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-outline-secondary">
                Toggle Status
              </a>
              <a href="manage_students.php?delete=<?php echo $s['student_id']; ?>" class="btn btn-sm btn-outline-danger"
                 data-confirm="Delete this student?">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Edit Modal for this student -->
          <div class="modal fade" id="editStudentModal<?php echo $s['student_id']; ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form method="POST">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Student</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="student_id" value="<?php echo $s['student_id']; ?>">
                    <div class="mb-2">
                      <label class="form-label">Full Name</label>
                      <input type="text" name="full_name" class="form-control" value="<?php echo clean($s['full_name']); ?>" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Email</label>
                      <input type="email" name="email" class="form-control" value="<?php echo clean($s['email']); ?>" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Phone</label>
                      <input type="text" name="phone" class="form-control" value="<?php echo clean($s['phone']); ?>">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Address</label>
                      <textarea name="address" class="form-control" rows="2"><?php echo clean($s['address']); ?></textarea>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Reset Password (optional)</label>
                      <input type="password" name="password" class="form-control" minlength="6" placeholder="Leave blank to keep unchanged">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Student Modal -->
<div class="modal fade" id="addStudentModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Student</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          <div class="mb-2">
            <label class="form-label">Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Phone</label>
            <input type="text" name="phone" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Address</label>
            <textarea name="address" class="form-control" rows="2"></textarea>
          </div>
          <div class="mb-2">
            <label class="form-label">Initial Password</label>
            <input type="password" name="password" class="form-control" minlength="6" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-accent">Add Student</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>
