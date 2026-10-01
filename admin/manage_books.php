<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);

$page_title = "Manage Books";
$active = "books";

include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$categories = $conn->query("SELECT * FROM categories ORDER BY category_name");
$categories_list = $categories->fetch_all(MYSQLI_ASSOC);

$books = $conn->query("
    SELECT b.*, c.category_name
    FROM books b
    LEFT JOIN categories c ON b.category_id = c.category_id
    ORDER BY b.title ASC
");
?>

<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Manage Books</h4>
    <button class="btn btn-accent" data-bs-toggle="modal" data-bs-target="#addBookModal">
      <i class="bi bi-plus-circle"></i> Add New Book
    </button>
  </div>

  <?php if (isset($_GET['msg'])): ?>
    <div class="alert alert-success"><?php echo clean($_GET['msg']); ?></div>
  <?php endif; ?>
  <?php if (isset($_GET['error'])): ?>
    <div class="alert alert-danger"><?php echo clean($_GET['error']); ?></div>
  <?php endif; ?>

  <input type="text" class="form-control table-search-input mb-3" placeholder="Search by title, author, or ISBN...">

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover searchable-table align-middle">
        <thead>
          <tr>
            <th>Title</th><th>Author</th><th>ISBN</th><th>Category</th>
            <th>Copies (Avail/Total)</th><th>Shelf</th><th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php while ($book = $books->fetch_assoc()): ?>
          <tr>
            <td><?php echo clean($book['title']); ?></td>
            <td><?php echo clean($book['author']); ?></td>
            <td><?php echo clean($book['isbn']); ?></td>
            <td><?php echo clean($book['category_name'] ?? '—'); ?></td>
            <td><?php echo (int)$book['available_copies']; ?> / <?php echo (int)$book['total_copies']; ?></td>
            <td><?php echo clean($book['shelf_location']); ?></td>
            <td>
              <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editBookModal<?php echo $book['book_id']; ?>">
                <i class="bi bi-pencil"></i>
              </button>
              <a href="process_book.php?action=delete&id=<?php echo $book['book_id']; ?>"
                 class="btn btn-sm btn-outline-danger"
                 onclick="return confirm('Delete this book? This cannot be undone.');">
                <i class="bi bi-trash"></i>
              </a>
            </td>
          </tr>

          <!-- Edit Modal -->
          <div class="modal fade" id="editBookModal<?php echo $book['book_id']; ?>" tabindex="-1">
            <div class="modal-dialog">
              <div class="modal-content">
                <form action="process_book.php" method="POST">
                  <div class="modal-header">
                    <h5 class="modal-title">Edit Book</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                  </div>
                  <div class="modal-body">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="book_id" value="<?php echo $book['book_id']; ?>">
                    <div class="mb-2">
                      <label class="form-label">Title</label>
                      <input type="text" name="title" class="form-control" value="<?php echo clean($book['title']); ?>" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Author</label>
                      <input type="text" name="author" class="form-control" value="<?php echo clean($book['author']); ?>" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">ISBN</label>
                      <input type="text" name="isbn" class="form-control" value="<?php echo clean($book['isbn']); ?>">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Category</label>
                      <select name="category_id" class="form-select">
                        <option value="">-- None --</option>
                        <?php foreach ($categories_list as $cat): ?>
                          <option value="<?php echo $cat['category_id']; ?>" <?php echo ($cat['category_id'] == $book['category_id']) ? 'selected' : ''; ?>>
                            <?php echo clean($cat['category_name']); ?>
                          </option>
                        <?php endforeach; ?>
                      </select>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Publisher</label>
                      <input type="text" name="publisher" class="form-control" value="<?php echo clean($book['publisher']); ?>">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Edition</label>
                      <input type="text" name="edition" class="form-control" value="<?php echo clean($book['edition']); ?>">
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Total Copies</label>
                      <input type="number" min="1" name="total_copies" class="form-control" value="<?php echo (int)$book['total_copies']; ?>" required>
                    </div>
                    <div class="mb-2">
                      <label class="form-label">Shelf Location</label>
                      <input type="text" name="shelf_location" class="form-control" value="<?php echo clean($book['shelf_location']); ?>">
                    </div>
                  </div>
                  <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                  </div>
                </form>
              </div>
            </div>
          </div>
          <?php endwhile; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>

<!-- Add Book Modal -->
<div class="modal fade" id="addBookModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <form action="process_book.php" method="POST">
        <div class="modal-header">
          <h5 class="modal-title">Add New Book</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="action" value="add">
          <div class="mb-2">
            <label class="form-label">Title</label>
            <input type="text" name="title" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Author</label>
            <input type="text" name="author" class="form-control" required>
          </div>
          <div class="mb-2">
            <label class="form-label">ISBN</label>
            <input type="text" name="isbn" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-select">
              <option value="">-- None --</option>
              <?php foreach ($categories_list as $cat): ?>
                <option value="<?php echo $cat['category_id']; ?>"><?php echo clean($cat['category_name']); ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="mb-2">
            <label class="form-label">Publisher</label>
            <input type="text" name="publisher" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Edition</label>
            <input type="text" name="edition" class="form-control">
          </div>
          <div class="mb-2">
            <label class="form-label">Total Copies</label>
            <input type="number" min="1" name="total_copies" class="form-control" value="1" required>
          </div>
          <div class="mb-2">
            <label class="form-label">Shelf Location</label>
            <input type="text" name="shelf_location" class="form-control">
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-accent">Add Book</button>
        </div>
      </form>
    </div>
  </div>
</div>

<?php include __DIR__ . '/../includes/footer.php'; ?>