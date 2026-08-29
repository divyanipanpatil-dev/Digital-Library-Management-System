// script.js - Digital Library Management System

document.addEventListener('DOMContentLoaded', function () {

    // Auto-dismiss alerts after 4 seconds
    document.querySelectorAll('.alert').forEach(function (alert) {
        setTimeout(function () {
            var bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
            if (bsAlert) bsAlert.close();
        }, 4000);
    });

    // Confirm before delete / risky actions
    document.querySelectorAll('[data-confirm]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            if (!confirm(el.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    // Live table search filter
    // Works on any table with class 'searchable-table' paired with
    // an input having class 'table-search-input'
    var searchInput = document.querySelector('.table-search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function () {
            var filter = searchInput.value.toLowerCase();
            var rows = document.querySelectorAll('.searchable-table tbody tr');
            rows.forEach(function (row) {
                var text = row.textContent.toLowerCase();
                row.style.display = text.includes(filter) ? '' : 'none';
            });
        });
    }

    // Auto-calculate due date on the Issue Book form (14 days from issue date)
    var issueDateInput = document.getElementById('issue_date');
    var dueDateDisplay = document.getElementById('due_date_display');
    if (issueDateInput && dueDateDisplay) {
        function updateDueDate() {
            var issueDate = new Date(issueDateInput.value);
            if (!isNaN(issueDate)) {
                issueDate.setDate(issueDate.getDate() + 14);
                var yyyy = issueDate.getFullYear();
                var mm = String(issueDate.getMonth() + 1).padStart(2, '0');
                var dd = String(issueDate.getDate()).padStart(2, '0');
                dueDateDisplay.textContent = yyyy + '-' + mm + '-' + dd;
            }
        }
        issueDateInput.addEventListener('change', updateDueDate);
        updateDueDate();
    }

    // Password confirmation check on the registration form
    var pw = document.getElementById('password');
    var cpw = document.getElementById('confirm_password');
    var regForm = document.getElementById('registerForm');
    if (regForm && pw && cpw) {
        regForm.addEventListener('submit', function (e) {
            if (pw.value !== cpw.value) {
                e.preventDefault();
                alert('Passwords do not match. Please re-check.');
            }
        });
    }

});
