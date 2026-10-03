-- =========================================================
-- Run this on your EXISTING database. It only ADDS a column.
-- phpMyAdmin -> select "library_management" database -> SQL tab -> paste -> Go
-- =========================================================
USE library_management;

UPDATE students
    SET terms_accepted = 'no';

-- Defaulting to 'no' means every EXISTING student will also be asked to
-- accept the Terms & Conditions the next time they log in, not just new
-- registrations -- exactly as you asked for.
