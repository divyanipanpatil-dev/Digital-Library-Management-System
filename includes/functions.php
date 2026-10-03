<?php
/**
 * Core helper functions, session handling, and constants.
 * Include this file at the top of every protected page.
 */
session_start();
require_once __DIR__ . '/../config/db_connect.php';

// Never let the browser cache a rendered page from this app.
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

define('FINE_PER_DAY', 5);       // Rs. 5 fine per day overdue
define('LOAN_PERIOD_DAYS', 14);  // Books are issued for 14 days

// ---- Auth guards ----
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
    
    // Every student must accept Terms & Conditions before using any page
    $current_script = basename($_SERVER['SCRIPT_NAME']);
    if ($current_script !== 'terms.php') {
        global $conn;
        $stmt = $conn->prepare("SELECT terms_accepted FROM students WHERE student_id = ?");
        $stmt->bind_param("i", $_SESSION['student_id']);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($row && $row['terms_accepted'] !== 'yes') {
            header("Location: " . $root . "student/terms.php");
            exit();
        }
    }
}

// ---- Utility ----
function clean($value) {
    return htmlspecialchars(trim($value ?? ''), ENT_QUOTES, 'UTF-8');
}

if (!function_exists('calculate_fine')) {
    function calculate_fine($due_date, $return_date = null) {
        $due = new DateTime($due_date);
        $compareDate = $return_date ? new DateTime($return_date) : new DateTime();
        if ($compareDate > $due) {
            $diff = $due->diff($compareDate);
            return $diff->days * FINE_PER_DAY;
        }
        return 0;
    }
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

// Function to generate automated due notifications
if (!function_exists('generate_due_notifications')) {
    function generate_due_notifications($conn, $student_id) {
        $today = date('Y-m-d');
        
        $stmt = $conn->prepare("SELECT transaction_id, due_date FROM transactions WHERE student_id = ? AND status IN ('issued', 'return_requested')");
        $stmt->bind_param("i", $student_id);
        $stmt->execute();
        $res = $stmt->get_result();

        while ($row = $res->fetch_assoc()) {
            $due_date = $row['due_date'];
            $type = '';
            $msg = '';

            if ($due_date < $today) {
                $type = 'overdue';
                $msg = "Your book loan is overdue. Please return it as soon as possible.";
            } elseif ($due_date === $today) {
                $type = 'due_soon';
                $msg = "Your book loan is due today.";
            }

            if ($type !== '') {
                // Check if notification already sent today for this type
                $check = $conn->prepare("SELECT notification_id FROM notifications WHERE student_id = ? AND type = ? AND DATE(created_at) = ?");
                $check->bind_param("iss", $student_id, $type, $today);
                $check->execute();
                if ($check->get_result()->num_rows === 0) {
                    $ins = $conn->prepare("INSERT INTO notifications (student_id, message, type, is_read, created_at) VALUES (?, ?, ?, 'no', NOW())");
                    $ins->bind_param("iss", $student_id, $msg, $type);
                    $ins->execute();
                }
            }
        }
    }
}
?>