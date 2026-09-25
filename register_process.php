<?php
require_once __DIR__ . '/includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: register.php");
    exit();
}

$full_name = trim($_POST['full_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$alternate_phone = trim($_POST['alternate_phone'] ?? '');
$branch = trim($_POST['branch'] ?? '');
$roll_no = trim($_POST['roll_no'] ?? '');
$admission_year_start = $_POST['admission_year_start'] !== '' ? (int)$_POST['admission_year_start'] : null;
$admission_year_end = $_POST['admission_year_end'] !== '' ? (int)$_POST['admission_year_end'] : null;
$address = trim($_POST['address'] ?? '');
$password = $_POST['password'] ?? '';
$confirm = $_POST['confirm_password'] ?? '';

if ($full_name === '' || $email === '' || $password === '') {
    header("Location: register.php?error=Please fill in all required fields.");
    exit();
}
if ($password !== $confirm) {
    header("Location: register.php?error=Passwords do not match.");
    exit();
}
if (strlen($password) < 6) {
    header("Location: register.php?error=Password must be at least 6 characters.");
    exit();
}

// Check duplicate email
$stmt = $conn->prepare("SELECT student_id FROM students WHERE email = ?");
$stmt->execute([$email]);
if ($stmt->get_result()->num_rows > 0) {
    header("Location: register.php?error=An account with this email already exists.");
    exit();
}

$hashed = password_hash($password, PASSWORD_DEFAULT);
$stmt = $conn->prepare("INSERT INTO students (full_name, email, password, phone, alternate_phone, branch, roll_no, admission_year_start, admission_year_end, address) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->execute([$full_name, $email, $hashed, $phone, $alternate_phone, $branch, $roll_no, $admission_year_start, $admission_year_end, $address]);

header("Location: login.php?msg=Registration successful! Please log in.");
exit();
?>
