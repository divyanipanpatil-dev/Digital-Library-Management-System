# 📚 Digital Library Management System

A web-based system to manage a college/school library digitally: students, books, issue and return, memberships, notifications, tracking, and reports, all in one place.

---

## ✨ Features

### Core
- Student registration and login
- Book catalog with add, edit, delete and search
- Issue and return of books
- Admin / librarian dashboard

### Newly Added Features

| # | Feature | Description |
|---|---------|-------------|
| 1 | **Student Profile** | Each student has a profile page showing personal details, issued books and history. |
| 2 | **Book ID** | Every book gets a unique Book ID for easy identification and lookup. |
| 3 | **Extended Profile Fields** | Branch, roll number, admission year and alternate phone number. |
| 4 | **Membership Start / End** | Library membership has a start and end date; expired members are flagged. |
| 5 | **Notification Options** | Students choose how they receive alerts (e.g. in-app, email, SMS). |
| 6 | **Issue → Return Request → Notification** | Student raises a return request; the librarian is notified and the student is notified once it is approved. |
| 7 | **Due Date → Notification** | Automatic reminders before and on the due date. |
| 8 | **Book Tracking** | See the status of every book (available, issued, requested for return, overdue) and who has it. |
| 9 | **Terms & Conditions on Login** | Users must read and accept the Terms & Conditions when they log in. |
| 10 | **Date-Range / Year-Wise Reports** | Generate issue/return and membership reports for a custom date range or a specific year. |

---

## 🛠️ Tech Stack

> Update this section to match your project.

- **Frontend:** HTML, CSS, JavaScript, Bootstrap
- **Backend:** PHP 
- **Database:** MySQL

---

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


## 📖 How It Works

1. **Admin** adds books (each gets a unique Book ID) and registers students with their membership dates.
2. **Student** logs in, accepts the Terms & Conditions, and can browse books and view their profile.
3. **Issue:** the librarian issues a book to a student, and a due date is set automatically.
4. **Reminders:** the student receives notifications as the due date approaches, based on their notification preferences.
5. **Return:** the student raises a return request, the librarian confirms it, and the student is notified.
6. **Reports:** the admin generates date-range or year-wise reports at any time.

---

## 👥 User Roles

| Role | Permissions |
|------|-------------|
| **Admin / Librarian** | Manage books, students, memberships, issue/return, reports |
| **Student** | View profile, browse books, request returns, manage notification settings |

---

## 📁 Folder Structure 
```
Digital-Library-Management-System/
├── admin/
│   ├── dashboard.php
│   ├── issue_book.php
│   ├── manage_books.php
│   ├── manage_categories.php
│   ├── manage_students.php
│   ├── process_book.php
│   ├── reports.php
│   ├── return_book.php
│   └── track_book.php
├── assets/
│   ├── css/
│   └── js/
├── config/
│   └── db_connect.php
├── database/
│   └── library_db.sql
├── includes/
│   ├── admin_navbar.php
│   ├── footer.php
│   ├── functions.php
│   ├── header.php
│   └── student_navbar.php
├── student/
│   ├── book_status.php
│   ├── dashboard.php
│   ├── notifications.php
│   ├── profile.php
│   ├── return_book.php
│   ├── search_books.php
│   ├── terms.php
│   └── view_issued.php
├── index.php
├── login.php
├── login_process.php
├── logout.php
├── register.php
├── register_process.php
└── README.md
---

## 🔮 Future Improvements

- Fine / late-fee calculation
- Book reservation queue
- QR / barcode scanning for Book IDs
- Export reports to PDF / Excel

---
