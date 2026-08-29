# Digital Library Management System

A Field Project (CA-310) built with HTML, CSS, Bootstrap 5, vanilla JavaScript, PHP (mysqli, prepared statements), and MySQL — a Librarian portal and a Student portal, each with their own login.

## Features
- **Home Page**: About Library, Features, Rules & Guidelines, Contact Us
- **Librarian (Admin) portal**: Dashboard, Manage Books, Manage Categories, Manage Students (Add/Edit/Toggle/Delete), Issue Book, Return Book, Reports
- **Student portal**: Dashboard, Search Book, View Issued Books, Return Book (request), Book Status
- Self-registration for students, plus librarian-created student accounts
- A **default librarian account is seeded automatically** — no separate setup step
- Strict session separation: logging in as one role clears any other role's session,
  so a librarian and a student can never appear "logged in" at once in the same browser
- Issue / Return workflow with automatic due-date calculation (14-day loan period)
- **Student-initiated return requests**: a student marks a book as "Request Return"; the librarian confirms
  physical receipt on the Return Book page, at which point the fine (if any) is calculated and the copy
  becomes available again
- Automatic fine calculation (Rs. 5/day overdue)
- Business rules: max 3 books per student, no duplicate active issue of the same book, block delete of
  books/students with active loans
- Reports page with filters (All / Issued / Return Requested / Overdue / Returned)
- Live client-side search filtering on tables (JavaScript) + server-side catalog search
- A distinct visual identity — deep forest green, brass accents, parchment cards, Fraunces/Inter
  typography, and a due-date-stamp motif used for every status badge

## Requirements
- PHP 8.1+ (uses the `mysqli_stmt::execute($params)` array syntax — XAMPP 8.2+ recommended)
- MySQL / MariaDB
- Any local server stack: XAMPP, WAMP, MAMP, or LAMP

## Setup Instructions

1. **Copy the project** into your server's web root:
   - XAMPP: `htdocs/digital-library-management-system/`
   - WAMP: `www/digital-library-management-system/`

2. **Create the database:**
   - Open phpMyAdmin
   - Import `database/library_db.sql`
   - This creates the `library_management` database, all tables, sample categories/books,
     **and a ready-to-use librarian account**

3. **Configure the database connection:**
   - Open `config/db_connect.php`
   - Update `$db_host`, `$db_user`, `$db_pass` if different from the XAMPP defaults
     (`localhost` / `root` / empty password)

4. **Log in — no setup step required:**
   - Visit `http://localhost/digital-library-management-system/index.php`
   - Click **Login**, choose **Librarian**, and sign in with:
     - **Username:** `admin`
     - **Password:** `admin123`
   - Change this password before submitting/demoing the project (see below)
   - Register a new Student account from the Home page, or add one directly from
     **Manage Students** as the librarian

### Changing the default librarian password
There's no in-app "change password" screen for the librarian account (only for students, via
Manage Students → Edit). To set your own password, open phpMyAdmin → SQL tab and run:
```sql
UPDATE admin SET password = '<new-bcrypt-hash>' WHERE username = 'admin';
```
Easiest way to get `<new-bcrypt-hash>`: create a throwaway PHP file with
`<?php echo password_hash('yourNewPassword', PASSWORD_DEFAULT);` run it once in the browser,
copy the output, then delete the file.

## Default Business Rules
Edit these to change behavior:
- `LOAN_PERIOD_DAYS = 14` and `FINE_PER_DAY = 5` — in `includes/functions.php`
- `MAX_BOOKS_PER_STUDENT = 3` — in `admin/issue_book.php`

## How a Return Works
1. Student goes to **Return Book** and clicks "Request Return" on a currently issued title
   → transaction status becomes `return_requested` (the book is still counted as "out")
2. Student physically hands the book to the librarian
3. Librarian opens **Return Book**, sees it flagged "Yes — by student" under Requested?,
   and clicks **Return** to finalize → fine is calculated, `available_copies` is incremented,
   status becomes `returned`
4. A librarian can also return any issued book directly without waiting for a student request

## Session Handling
Only one identity can be active per browser session. Logging in as Librarian clears any Student
session data (and vice versa) and regenerates the session ID. If both were ever set at once —
e.g. from an older version of this project — the auth guards in `includes/functions.php` treat
that as invalid and send you back to the login page rather than guessing which role you meant.

## Folder Structure
```
digital-library-management-system/
├── config/db_connect.php          # DB credentials
├── database/library_db.sql        # Schema + sample data + seeded librarian login
├── includes/                      # Shared header, footer, navbars, functions
├── assets/css/style.css           # Design system (colors, type, components)
├── assets/js/script.js            # Live search, confirm dialogs, due-date calc
├── admin/                         # Librarian portal
│   ├── dashboard.php
│   ├── manage_books.php + process_book.php
│   ├── manage_categories.php
│   ├── manage_students.php        # Add / Edit / Toggle status / Delete
│   ├── issue_book.php
│   ├── return_book.php            # Confirms both direct and student-requested returns
│   └── reports.php                # Filterable transaction reports
├── student/                       # Student portal
│   ├── dashboard.php
│   ├── search_books.php
│   ├── view_issued.php            # Currently issued + borrowing history
│   ├── return_book.php            # Student submits a return request
│   └── book_status.php            # Read-only catalog availability by category
├── index.php                      # Home Page (About / Features / Rules / Contact)
├── login.php, login_process.php
├── register.php, register_process.php, logout.php
└── README.md
```

## Mapping to Your Field Project Report
- **DFD / ERD**: Base these on the tables in `database/library_db.sql`.
  Entities: Books, Students, Categories, Transactions, Admin.
- **Use Case Actors**: Librarian (Admin), Student.
- **Chapter 5 (Implementation)**: Technologies used = HTML/CSS/Bootstrap/JS (frontend),
  PHP + mysqli (backend), MySQL (database). The module list above maps directly to your
  "Description of modules" section.
- **Testing**: Use the issue → student return-request → librarian confirm flow to manually
  test fine calculation, duplicate-issue prevention, the 3-book limit, deletion safeguards,
  and the session-isolation behavior described above. Good material for your manual
  test-case table (Test ID / Steps / Expected / Actual / Pass-Fail).

## Security Notes (useful for your viva)
- Passwords are hashed with `password_hash()` and verified with `password_verify()` (bcrypt)
  — never stored in plain text, including the seeded default librarian account.
- All database queries use prepared statements (`$conn->prepare()` + `execute([...])`)
  to prevent SQL injection.
- All user-supplied output is escaped with `htmlspecialchars()` (via the `clean()` helper)
  to prevent XSS.
- Session-based authentication guards every admin/ and student/ page, with strict
  single-role session isolation (see above).
