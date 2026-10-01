<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Manage Categories";
$active = "categories";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add') {
        $name = trim($_POST['category_name']);
        if ($name !== '') {
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $name);
            $stmt->execute();
        }
    }
    header("Location: manage_categories.php");
    exit();
}

if (isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    header("Location: manage_categories.php");
    exit();
}

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$categories = $conn->query("
    SELECT c.*, COUNT(b.book_id) AS book_count
    FROM categories c
    LEFT JOIN books b ON b.category_id = c.category_id
    GROUP BY c.category_id
    ORDER BY c.category_name
");
?>
<div class="container my-4">
  <h4 class="mb-3">Manage Categories</h4>

  <div class="card mb-4">
    <div class="card-body">
      <form method="POST" class="row g-2">
        <input type="hidden" name="action" value="add">
        <div class="col-sm-8">
          <input type="text" name="category_name" class="form-control" placeholder="New category name" required>
        </div>
        <div class="col-sm-4">
          <button type="submit" class="btn btn-accent w-100">Add Category</button>
        </div>
      </form>
    </div>
  </div>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead><tr><th>Category</th><th>Books</th><th>Actions</th></tr></thead>
        <tbody>
          <?php while ($cat = $categories->fetch_assoc()): ?>
          <tr>
            <td><?php echo clean($cat['category_name']); ?></td>
            <td><?php echo (int)$cat['book_count']; ?></td>
            <td>
              <a href="manage_categories.php?delete=<?php echo $cat['category_id']; ?>"
                 class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete this category? Books in it will become uncategorized.');">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>