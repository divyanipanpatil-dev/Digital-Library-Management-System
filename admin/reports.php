<?php
$root = "../";
require_once __DIR__ . '/../includes/functions.php';
require_admin_login($root);
$page_title = "Reports";
$active = "reports";
include __DIR__ . '/../includes/header.php';
include __DIR__ . '/../includes/admin_navbar.php';

$filter = $_GET['filter'] ?? 'all';
$from_date = $_GET['from_date'] ?? '';
$to_date = $_GET['to_date'] ?? '';
$year = $_GET['year'] ?? '';

$sql = "
    SELECT t.*, b.title, s.full_name
    FROM transactions t
    JOIN books b ON t.book_id = b.book_id
    JOIN students s ON t.student_id = s.student_id
    WHERE 1=1
";
$params = [];

if ($filter === 'issued') {
    $sql .= " AND t.status='issued'";
} elseif ($filter === 'requested') {
    $sql .= " AND t.status='return_requested'";
} elseif ($filter === 'overdue') {
    $sql .= " AND t.status IN ('issued','return_requested') AND t.due_date < CURDATE()";
} elseif ($filter === 'returned') {
    $sql .= " AND t.status='returned'";
}

// Year takes priority if both a year and a date range are set
if ($year !== '') {
    $sql .= " AND YEAR(t.issue_date) = ?";
    $params[] = (int)$year;
} elseif ($from_date !== '' && $to_date !== '') {
    $sql .= " AND t.issue_date BETWEEN ? AND ?";
    $params[] = $from_date;
    $params[] = $to_date;
} elseif ($from_date !== '') {
    $sql .= " AND t.issue_date >= ?";
    $params[] = $from_date;
} elseif ($to_date !== '') {
    $sql .= " AND t.issue_date <= ?";
    $params[] = $to_date;
}

$sql .= " ORDER BY t.transaction_id DESC";
$stmt = $conn->prepare($sql);

if (!empty($params)) {
    $types = str_repeat('s', count($params));
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$transactions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$total_count = count($transactions);
$fine_collected = 0;
$fine_pending = 0;
foreach ($transactions as $t) {
    if ($t['fine_paid'] === 'yes') { $fine_collected += (float)$t['fine_amount']; }
    else { $fine_pending += (float)$t['fine_amount']; }
}

$years_result = $conn->query("SELECT DISTINCT YEAR(issue_date) AS y FROM transactions ORDER BY y DESC");
$years = $years_result->fetch_all(MYSQLI_ASSOC);
?>
<div class="container my-4">
  <h4 class="mb-3">Reports</h4>

  <div class="card mb-4">
    <div class="card-body">
      <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
          <label class="form-label">Status</label>
          <select name="filter" class="form-select">
            <option value="all" <?php echo $filter=='all'?'selected':''; ?>>All</option>
            <option value="issued" <?php echo $filter=='issued'?'selected':''; ?>>Issued</option>
            <option value="requested" <?php echo $filter=='requested'?'selected':''; ?>>Return Requested</option>
            <option value="overdue" <?php echo $filter=='overdue'?'selected':''; ?>>Overdue</option>
            <option value="returned" <?php echo $filter=='returned'?'selected':''; ?>>Returned</option>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">Year</label>
          <select name="year" class="form-select">
            <option value="">Any</option>
            <?php foreach ($years as $y): ?>
              <option value="<?php echo $y['y']; ?>" <?php echo ($year==$y['y'])?'selected':''; ?>><?php echo $y['y']; ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="col-md-2">
          <label class="form-label">From Date</label>
          <input type="date" name="from_date" class="form-control" value="<?php echo clean($from_date); ?>">
        </div>
        <div class="col-md-2">
          <label class="form-label">To Date</label>
          <input type="date" name="to_date" class="form-control" value="<?php echo clean($to_date); ?>">
        </div>
        <div class="col-md-2">
          <button type="submit" class="btn btn-accent w-100">Apply Filters</button>
        </div>
        <div class="col-md-1">
          <a href="reports.php" class="btn btn-outline-secondary w-100" title="Clear filters"><i class="bi bi-x-lg"></i></a>
        </div>
      </form>
      <p class="text-muted small mt-2 mb-0">Choose a Year for a quick year-wise report, or set From/To Date for a custom range. Year takes priority if both are set.</p>
    </div>
  </div>

  <div class="row g-3 mb-4">
    <div class="col-md-4">
      <div class="stat-card bg-issued">
        <div class="stat-number"><?php echo $total_count; ?></div>
        <div>Records in this Report</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card bg-students">
        <div class="stat-number">Rs. <?php echo number_format($fine_collected, 2); ?></div>
        <div>Fine Collected</div>
      </div>
    </div>
    <div class="col-md-4">
      <div class="stat-card bg-overdue">
        <div class="stat-number">Rs. <?php echo number_format($fine_pending, 2); ?></div>
        <div>Fine Pending</div>
      </div>
    </div>
  </div>

  <input type="text" class="form-control table-search-input mb-3" placeholder="Search these results...">

  <div class="card">
    <div class="card-body table-responsive">
      <table class="table table-hover searchable-table align-middle">
        <thead>
          <tr><th>Book</th><th>Student</th><th>Issue</th><th>Due</th><th>Return</th><th>Fine</th><th>Status</th></tr>
        </thead>
        <tbody>
          <?php if (empty($transactions)): ?>
            <tr><td colspan="7" class="text-center text-muted">No records match these filters.</td></tr>
          <?php endif; ?>
          <?php foreach ($transactions as $row): ?>
          <tr>
            <td><?php echo clean($row['title']); ?></td>
            <td><?php echo clean($row['full_name']); ?></td>
            <td><?php echo $row['issue_date']; ?></td>
            <td><?php echo $row['due_date']; ?></td>
            <td><?php echo $row['return_date'] ?? '—'; ?></td>
            <td>
              <?php echo $row['fine_amount'] > 0 ? 'Rs. ' . number_format($row['fine_amount'],2) . ($row['fine_paid']=='yes' ? ' (paid)' : ' (unpaid)') : '—'; ?>
            </td>
            <td>
              <?php
                $is_overdue = in_array($row['status'], ['issued','return_requested']) && strtotime($row['due_date']) < strtotime(date('Y-m-d'));
              ?>
              <?php if ($row['status'] === 'returned'): ?>
                <span class="badge badge-returned">Returned</span>
              <?php elseif ($row['status'] === 'return_requested'): ?>
                <span class="badge badge-issued">Return Requested</span>
                <?php if ($is_overdue): ?><span class="badge badge-overdue">Overdue</span><?php endif; ?>
              <?php else: ?>
                <?php if ($is_overdue): ?>
                  <span class="badge badge-overdue">Overdue</span>
                <?php else: ?>
                  <span class="badge badge-issued">Issued</span>
                <?php endif; ?>
              <?php endif; ?>
            </td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  </div>
</div>
<?php include __DIR__ . '/../includes/footer.php'; ?>