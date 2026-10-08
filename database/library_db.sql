-- =========================================================
-- Digital Library Management System
-- =========================================================

CREATE DATABASE IF NOT EXISTS library_management;
USE library_management;

-- ---------------------------------------------------------
-- Table: admin  (Librarian login)
-- ---------------------------------------------------------
CREATE TABLE admin (
    admin_id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- ---------------------------------------------------------
-- Default librarian (admin) login — ready to use immediately,
-- no separate setup step required.
--
--   Username: admin
--   Password: admin123
--
-- The hash below is a genuine bcrypt hash of "admin123" — PHP's
-- password_verify() will authenticate it correctly out of the box.
-- Change this password after your first login. There's no
-- Manage-Students-style edit screen for the librarian account itself,
-- so update it directly in phpMyAdmin, or run:
--   UPDATE admin SET password = '<new password_hash() value>' WHERE username = 'admin';
-- ---------------------------------------------------------
INSERT INTO admin (username, password, full_name, email) VALUES
('admin', '$2b$12$1i7CRaMxvFjPk74ff.ftVuUNC5uIzqZH2WaQ50TKelNAgMMJ6LR3O', 'Library Administrator', 'admin@library.local');


-- ---------------------------------------------------------
-- Table: students (Library members)
-- ---------------------------------------------------------
CREATE TABLE students (
    student_id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(15),
    address VARCHAR(255),
    registration_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active','inactive') DEFAULT 'active'
);

-- ---------------------------------------------------------
-- Table: categories
-- ---------------------------------------------------------
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    category_name VARCHAR(100) NOT NULL
);

-- ---------------------------------------------------------
-- Table: books
-- ---------------------------------------------------------
CREATE TABLE books (
    book_id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(200) NOT NULL,
    author VARCHAR(150) NOT NULL,
    isbn VARCHAR(20) UNIQUE,
    category_id INT,
    publisher VARCHAR(150),
    edition VARCHAR(50),
    total_copies INT NOT NULL DEFAULT 1,
    available_copies INT NOT NULL DEFAULT 1,
    shelf_location VARCHAR(50),
    added_date DATE DEFAULT (CURRENT_DATE),
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL
);

-- ---------------------------------------------------------
-- Table: transactions (Issue / Return records)
-- ---------------------------------------------------------
CREATE TABLE transactions (
    transaction_id INT AUTO_INCREMENT PRIMARY KEY,
    book_id INT NOT NULL,
    student_id INT NOT NULL,
    issue_date DATE NOT NULL,
    due_date DATE NOT NULL,
    return_date DATE DEFAULT NULL,
    fine_amount DECIMAL(10,2) DEFAULT 0.00,
    fine_paid ENUM('yes','no') DEFAULT 'no',
    status ENUM('issued','return_requested','returned') DEFAULT 'issued',
    FOREIGN KEY (book_id) REFERENCES books(book_id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE
);

-- ---------------------------------------------------------
-- Sample Data: Categories
-- ---------------------------------------------------------
INSERT INTO categories (category_name) VALUES
('Computer Science'),
('Fiction'),
('Mathematics'),
('History'),
('Business & Management');

-- ---------------------------------------------------------
-- Sample Data: Books
-- ---------------------------------------------------------
INSERT INTO books (title, author, isbn, category_id, publisher, edition, total_copies, available_copies, shelf_location) VALUES
('Introduction to Algorithms', 'Thomas H. Cormen', '9780262033848', 1, 'MIT Press', '3rd', 4, 4, 'A1-01'),
('Database System Concepts', 'Abraham Silberschatz', '9780073523323', 1, 'McGraw-Hill', '6th', 3, 3, 'A1-02'),
('The Alchemist', 'Paulo Coelho', '9780062315007', 2, 'HarperOne', '1st', 5, 5, 'B2-05'),
('Discrete Mathematics', 'Kenneth Rosen', '9780073383095', 3, 'McGraw-Hill', '7th', 2, 2, 'C1-03'),
('Sapiens: A Brief History of Humankind', 'Yuval Noah Harari', '9780062316097', 4, 'Harper', '1st', 3, 3, 'D3-01'),
('Principles of Management', 'Peter Drucker', '9780061252662', 5, 'Harper Business', '2nd', 2, 2, 'E1-04');

-- ---------------------------------------------------------
-- Notification
-- ---------------------------------------------------------

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


-- ---------------------------------------------------------
-- Membership 
-- --------------------------------------------------------

ALTER TABLE students
    ADD COLUMN alternate_phone      VARCHAR(15) NULL,
    ADD COLUMN branch               VARCHAR(100) NULL,
    ADD COLUMN roll_no              VARCHAR(50) NULL,
    ADD COLUMN admission_year_start YEAR NULL,
    ADD COLUMN admission_year_end   YEAR NULL,
    ADD COLUMN membership_start_date DATE NULL,
    ADD COLUMN membership_end_date   DATE NULL;

-- Optional: give every existing student a 1-year membership starting today.
-- Uncomment the next line if you want that instead of setting each one
-- manually from Manage Students.
-- UPDATE students SET membership_start_date = CURDATE(), membership_end_date = DATE_ADD(CURDATE(), INTERVAL 1 YEAR);


-- ---------------------------------------------------------
-- Terms Conditions 
-- --------------------------------------------------------

UPDATE students
    SET terms_accepted = 'no';

-- Defaulting to 'no' means every EXISTING student will also be asked to
-- accept the Terms & Conditions the next time they log in, not just new
-- registrations -- exactly as you asked for.