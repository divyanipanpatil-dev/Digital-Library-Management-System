
<?php
$__unread = 0;
if (isset($_SESSION['student_id'])) {
    $__unread = unread_notification_count($conn, $_SESSION['student_id']);
}
?>
<nav class="navbar navbar-expand-lg navbar-dark app-navbar">
  <div class="container-fluid">
    <a class="navbar-brand" href="<?php echo $root; ?>student/dashboard.php">
      <i class="bi bi-book-half"></i> Digital Library
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#studentNav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="studentNav">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <li class="nav-item"><a class="nav-link <?php echo ($active=='dashboard')?'active':''; ?>" href="<?php echo $root; ?>student/dashboard.php">Dashboard</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='search')?'active':''; ?>" href="<?php echo $root; ?>student/search_books.php">Search Book</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='issued')?'active':''; ?>" href="<?php echo $root; ?>student/view_issued.php">View Issued Books</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='return')?'active':''; ?>" href="<?php echo $root; ?>student/return_book.php">Return Book</a></li>
        <li class="nav-item"><a class="nav-link <?php echo ($active=='status')?'active':''; ?>" href="<?php echo $root; ?>student/book_status.php">Book Status</a></li>
      </ul>

      <a href="<?php echo $root; ?>student/notifications.php" class="btn btn-outline-light btn-sm position-relative me-3">
        <i class="bi bi-bell"></i>
        <?php if ($__unread > 0): ?>
          <span class="notif-dot"><?php echo $__unread > 9 ? '9+' : $__unread; ?></span>
        <?php endif; ?>
      </a>
      
      <span class="navbar-text text-light me-3">
        <span class="role-badge role-badge--student me-2">Student</span>
        <a href="profile.php">
        <i class="bi bi-person-circle"></i> <?php echo clean($_SESSION['student_name'] ?? 'Student'); ?>
      </span>
</a>
      <a href="<?php echo $root; ?>logout.php" class="btn btn-outline-light btn-sm">Logout</a>
    </div>
  </div>
</nav>
