-- =========================================================
-- Run this on your EXISTING database. It only ADDS a new
-- table, nothing else is touched.
-- phpMyAdmin -> select "library_management" database -> SQL tab -> paste -> Go
-- =========================================================
USE library_management;

CREATE TABLE IF NOT EXISTS notifications (
    notification_id INT AUTO_INCREMENT PRIMARY KEY,
    student_id INT NOT NULL,
    message VARCHAR(255) NOT NULL,
    type ENUM('issue','return_requested','return_confirmed','due_soon','overdue','membership','general') DEFAULT 'general',
    is_read ENUM('yes','no') DEFAULT 'no',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- The 'type' list already includes issue / return_requested / return_confirmed /
-- due_soon / overdue / membership so no schema change will be needed when we
-- wire up the actual trigger points in the next two features.
