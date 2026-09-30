<?php
/**
 * Core helper functions, session handling, and constants.
 * Include this file at the top of every protected page.
 */
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// Never let the browser cache a rendered page from this app. Without this,
// pressing Back (or switching tabs) after logging in as a different role can
// display a stale copy of a page from the previous session — which looks
// exactly like a session/identity bug even though the server-side session
// itself is correct. Every page includes this file before any output, so
// it's safe to set headers here.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

define('FINE_PER_DAY', 5);       // Rs. 5 fine per day overdue
define('LOAN_PERIOD_DAYS', 14);  // Books are issued for 14 days

// ---- Auth guards ----
// A session may hold exactly one identity at a time. If both happen to be
// set (e.g. a leftover session from before this check existed), treat it as
// invalid rather than silently picking one — this is what previously caused
// a logged-in librarian to also appear logged in as a student.
function is_admin_logged_in() {
    return isset($_SESSION['admin_id']) && !isset($_SESSION['student_id']);
}
function is_student_logged_in() {
    return isset($_SESSION['student_id']) && !isset($_SESSION['admin_id']);
}
function require_admin_login($root = '') {
    if (!is_admin_logged_in()) {
        header("Location: " . $root . "login.php");
        exit();
    }
}
function require_student_login($root = '') {
    if (!is_student_logged_in()) {
        header("Location: " . $root . "login.php");
        exit();
    }
}

// ---- Utility ----
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

function calculate_fine($due_date, $return_date = null) {
    $due = new DateTime($due_date);
    $compareDate = $return_date ? new DateTime($return_date) : new DateTime();
    if ($compareDate > $due) {
        $diff = $due->diff($compareDate);
        return $diff->days * FINE_PER_DAY;
    }
    return 0;
}

// ---- Notifications ----
function notify($conn, $student_id, $message, $type = 'general') {
    $stmt = $conn->prepare("INSERT INTO notifications (student_id, message, type) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $student_id, $message, $type);
    $stmt->execute();
}

function unread_notification_count($conn, $student_id) {
    $stmt = $conn->prepare("SELECT COUNT(*) AS c FROM notifications WHERE student_id = ? AND is_read = 'no'");
    $stmt->bind_param("i", $student_id);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['c'];
}
?>