<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: login.php");
    exit();
}

$role = $_POST['role'] ?? '';
$identifier = trim($_POST['identifier'] ?? '');
$password = $_POST['password'] ?? '';

if ($identifier === '' || $password === '') {
    header("Location: login.php?error=Please fill in all fields.");
    exit();
}

if ($role === 'admin') {
    $stmt = $conn->prepare("SELECT admin_id, username, password, full_name FROM admin WHERE username = ?");
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $admin = $result->fetch_assoc();

    if ($admin && password_verify($password, $admin['password'])) {
        session_unset();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $admin['admin_id'];
        $_SESSION['admin_name'] = $admin['full_name'];
        header("Location: admin/dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=Invalid librarian credentials.");
        exit();
    }

} elseif ($role === 'student') {
    $stmt = $conn->prepare("SELECT student_id, email, password, full_name, status FROM students WHERE email = ?");
    $stmt->bind_param("s", $identifier);
    $stmt->execute();
    $result = $stmt->get_result();
    $student = $result->fetch_assoc();

    if ($student && password_verify($password, $student['password'])) {
        if ($student['status'] !== 'active') {
            header("Location: login.php?error=Your account is inactive. Contact the librarian.");
            exit();
        }
        session_unset();
        session_regenerate_id(true);
        $_SESSION['student_id'] = $student['student_id'];
        $_SESSION['student_name'] = $student['full_name'];
        header("Location: student/dashboard.php");
        exit();
    } else {
        header("Location: login.php?error=Invalid student credentials.");
        exit();
    }
} else {
    header("Location: login.php?error=Invalid role selected.");
    exit();
}
?>