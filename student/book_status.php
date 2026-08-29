<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_student_login($root);
$page_title = "Book Status";
$active = "status";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/student_navbar.php';

$cat_filter = (int)($_GET['category'] ?? 0);

$sql = "SELECT b.*, c.category_name FROM books b LEFT JOIN categories c ON b.category_id=c.category_id WHERE 1=1";
$params = [];
if ($cat_filter > 0) {
    $sql .= " AND b.category_id = ?";
    $params[] = $cat_filter;
}
$sql .= " ORDER BY b.title ASC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$books = $stmt->get_result();

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
?>
<div class="container my-4">
  <h4 class="mb-3">Book Availability Status</h4>

  <form method="GET" class="row g-2 mb-3">
    <div class="col-md-4">
      <select name="category" class="form-select" onchange="this.form.submit()">
        <option value="0">All Categories</option>
        <?php while ($cat = $categories->fetch_assoc()): ?>
          <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat_filter==$cat['category_id'])?'selected':''; ?>>
            <?php echo clean($cat['category_name']); ?>
          </option>
        <?php endwhile; ?>
      </select>
    </div>
  </form>

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover align-middle">
        <thead>
          <tr><th>Title</th><th>Author</th><th>Category</th><th>Shelf</th><th>Copies</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php while ($book = $books->fetch_assoc()): ?>
          <tr>
            <td><?php echo clean($book['title']); ?></td>
            <td><?php echo clean($book['author']); ?></td>
            <td><?php echo clean($book['category_name'] ?? '—'); ?></td>
            <td><?php echo clean($book['shelf_location']); ?></td>
            <td><?php echo (int)$book['available_copies']; ?> / <?php echo (int)$book['total_copies']; ?></td>
            <td>
              <?php if ($book['available_copies'] > 0): ?>
                <span class="badge badge-available">Available</span>
              <?php else: ?>
                <span class="badge badge-unavailable">Not Available</span>
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
