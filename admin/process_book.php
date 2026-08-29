<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);

$action = $_POST['action'] ?? ($_GET['action'] ?? '');

if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $category_id = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $publisher = trim($_POST['publisher']);
    $edition = trim($_POST['edition']);
    $total_copies = max(1, (int)$_POST['total_copies']);
    $shelf_location = trim($_POST['shelf_location']);

    $stmt = $conn->prepare("INSERT INTO books (title, author, isbn, category_id, publisher, edition, total_copies, available_copies, shelf_location) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $author, $isbn ?: null, $category_id, $publisher, $edition, $total_copies, $total_copies, $shelf_location]);

    header("Location: manage_books.php?msg=Book added successfully.");
    exit();

} elseif ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = (int)$_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $isbn = trim($_POST['isbn']);
    $category_id = $_POST['category_id'] !== '' ? (int)$_POST['category_id'] : null;
    $publisher = trim($_POST['publisher']);
    $edition = trim($_POST['edition']);
    $new_total = max(1, (int)$_POST['total_copies']);
    $shelf_location = trim($_POST['shelf_location']);

    // Adjust available_copies proportionally if total_copies changed
    $stmt = $conn->prepare("SELECT total_copies, available_copies FROM books WHERE book_id = ?");
    $stmt->execute([$book_id]);
    $current = $stmt->get_result()->fetch_assoc();

    if ($current) {
        $diff = $new_total - (int)$current['total_copies'];
        $new_available = max(0, (int)$current['available_copies'] + $diff);

        $stmt = $conn->prepare("UPDATE books SET title=?, author=?, isbn=?, category_id=?, publisher=?, edition=?, total_copies=?, available_copies=?, shelf_location=? WHERE book_id=?");
        $stmt->execute([$title, $author, $isbn ?: null, $category_id, $publisher, $edition, $new_total, $new_available, $shelf_location, $book_id]);
    }

    header("Location: manage_books.php?msg=Book updated successfully.");
    exit();

} elseif ($action === 'delete') {
    $book_id = (int)($_GET['id'] ?? 0);

    // Prevent deletion if the book currently has active issues
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM transactions WHERE book_id = ? AND status = 'issued'");
    $stmt->execute([$book_id]);
    $active = $stmt->get_result()->fetch_assoc()['c'];

    if ($active > 0) {
        header("Location: manage_books.php?error=Cannot delete: this book has copies currently issued.");
        exit();
    }

    $stmt = $conn->prepare("DELETE FROM books WHERE book_id = ?");
    $stmt->execute([$book_id]);

    header("Location: manage_books.php?msg=Book deleted successfully.");
    exit();

} else {
    header("Location: manage_books.php");
    exit();
}
?>
