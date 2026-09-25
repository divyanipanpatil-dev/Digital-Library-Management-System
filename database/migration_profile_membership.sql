-- =========================================================
-- Run this on your EXISTING database. It only ADDS columns,
-- your books/students/transactions data is untouched.
-- phpMyAdmin -> select "library_management" database -> SQL tab -> paste -> Go
-- =========================================================
USE library_management;

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
