<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo $root; ?>admin/dashboard.php">
      <i class="bi bi-book-half"></i> Library Admin
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="adminNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?php echo ($active=='dashboard')?'active':''; ?>" href="<?php echo $root; ?>admin/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='books')?'active':''; ?>" href="<?php echo $root; ?>admin/manage_books.php">Books</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='categories')?'active':''; ?>" href="<?php echo $root; ?>admin/manage_categories.php">Categories</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='students')?'active':''; ?>" href="<?php echo $root; ?>admin/manage_students.php">Students</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='issue')?'active':''; ?>" href="<?php echo $root; ?>admin/issue_book.php">Issue Book</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='return')?'active':''; ?>" href="<?php echo $root; ?>admin/return_book.php">Return Book</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='reports')?'active':''; ?>" href="<?php echo $root; ?>admin/reports.php">Reports</a></li>
      </ul>
      <span class="navbar-text text-light me-3">
        <span class="role-badge role-badge--admin me-2">Librarian</span>
        <i class="bi bi-person-circle"></i> <?php echo clean($_SESSION['admin_name'] ?? 'Admin'); ?>
      </span>
      <a href="<?php echo $root; ?>logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>
