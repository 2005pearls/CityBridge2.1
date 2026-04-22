<?php
include "db.php";
include "auth.php";

require_login("admin");

$account_id = (int)$_SESSION["account_id"];

//  Admin info (join admin_account, not user_account) 
$stmt = $conn->prepare(
  "SELECT a.username, a.email, aa.admin_name, aa.role_title, aa.phone_number
   FROM account a
   JOIN admin_account aa ON aa.account_id = a.account_id
   WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$admin = $stmt->get_result()->fetch_assoc();
$stmt->close();

//  Stats 
$totals = $conn->query("
  SELECT
    COUNT(*) AS total,
    SUM(CASE WHEN status='pending'  THEN 1 ELSE 0 END) AS pending,
    SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
    SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected
  FROM permit
")->fetch_assoc();

$total    = (int)$totals['total'];
$pending  = (int)$totals['pending'];
$approved = (int)$totals['approved'];
$rejected = (int)$totals['rejected'];

/** Helper: list permits joined to applicant info. */
function list_permits(mysqli $conn, $status) {
    $sql = "SELECT p.permit_id, p.permit_type, p.status, p.submitted_date,
                   p.approved_date, p.rejection_reason,
                   ua.first_name, ua.last_name
            FROM permit p
            JOIN user_account ua ON ua.account_id = p.user_account_id
            WHERE p.status = ?
            ORDER BY p.submitted_date DESC";
    $s = $conn->prepare($sql);
    $s->bind_param("s", $status);
    $s->execute();
    return $s->get_result();
}

$resPending  = list_permits($conn, 'pending');
$resApproved = list_permits($conn, 'approved');
$resRejected = list_permits($conn, 'rejected');
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Admin Dashboard - CityBridge</title>
  <link rel="stylesheet" href="style.css">
</head>
<body <?php echo flash_attrs(); ?>>

<header class="user-header">
  <svg viewBox="0 0 500 72" width="320" xmlns="http://www.w3.org/2000/svg">
    <text x="0" y="44" font-family="'Helvetica Neue', 'Segoe UI', Arial, sans-serif" font-size="42" font-weight="700" letter-spacing="-1">
      <tspan fill="#e8f0fb">City</tspan><tspan fill="#4baee8">Bridge</tspan>
    </text>
    <line x1="0" y1="52" x2="220" y2="52" stroke="#4baee8" stroke-width="1.6" opacity="0.4" />
    <text x="0" y="67" font-family="'DM Sans', 'Helvetica Neue', Arial, sans-serif" font-size="10.5" font-weight="300" fill="#7dcef8" letter-spacing="4" opacity="0.82">CONNECTING COMMUNITIES</text>
  </svg>
  <nav>
    <a href="logout.php" class="logout">Log Out</a>
  </nav>
</header>

<main class="dashboard">

  <div class="page-title">
    <h1>Admin Dashboard</h1>
    <p>Manage and review all permit requests submitted by users.</p>
  </div>

  <div class="panel">
    <h3>Administrator Information</h3>
    <div class="info-row"><span class="label">Name</span><span class="value"><?php echo e($admin['admin_name'] ?? ''); ?></span></div>
    <div class="info-row"><span class="label">Role</span><span class="value"><?php echo e($admin['role_title'] ?? 'System Administrator'); ?></span></div>
    <div class="info-row"><span class="label">Phone Number</span><span class="value"><?php echo e($admin['phone_number'] ?? ''); ?></span></div>
    <div class="info-row"><span class="label">Email</span><span class="value"><?php echo e($admin['email'] ?? ''); ?></span></div>
    <div class="info-row"><span class="label">Username</span><span class="value"><?php echo e($admin['username'] ?? ''); ?></span></div>
  </div>

  <div class="stats">
    <div class="stat"><p>Total Requests</p><span><?php echo $total; ?></span></div>
    <div class="stat"><p>Pending</p><span class="pending"><?php echo $pending; ?></span></div>
    <div class="stat"><p>Approved</p><span class="approved"><?php echo $approved; ?></span></div>
    <div class="stat"><p>Rejected</p><span class="rejected"><?php echo $rejected; ?></span></div>
  </div>

  <!--  PENDING  -->
  <div class="panel">
    <h3>Pending Requests</h3>
    <table>
      <thead><tr>
        <th>Permit ID</th><th>Type</th><th>Applicant</th><th>Submitted</th><th>Action</th>
      </tr></thead>
      <tbody>
      <?php if ($resPending->num_rows === 0): ?>
        <tr><td colspan="5" style="text-align:center;">No pending requests.</td></tr>
      <?php else: while ($row = $resPending->fetch_assoc()): ?>
        <tr>
          <td><?php echo permit_code($row['permit_id']); ?></td>
          <td><?php echo e(type_label($row['permit_type'])); ?></td>
          <td><?php echo e($row['first_name'].' '.$row['last_name']); ?></td>
          <td><?php echo fmt_date($row['submitted_date']); ?></td>
          <td><a href="permit-details.php?id=<?php echo (int)$row['permit_id']; ?>" class="table-link">Review</a></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>

  <!--  APPROVED  -->
  <div class="panel">
    <h3>Approved Requests</h3>
    <table>
      <thead><tr>
        <th>Permit ID</th><th>Type</th><th>Applicant</th><th>Approval Date</th><th>Action</th>
      </tr></thead>
      <tbody>
      <?php if ($resApproved->num_rows === 0): ?>
        <tr><td colspan="5" style="text-align:center;">No approved requests.</td></tr>
      <?php else: while ($row = $resApproved->fetch_assoc()): ?>
        <tr>
          <td><?php echo permit_code($row['permit_id']); ?></td>
          <td><?php echo e(type_label($row['permit_type'])); ?></td>
          <td><?php echo e($row['first_name'].' '.$row['last_name']); ?></td>
          <td><?php echo fmt_date($row['approved_date']); ?></td>
          <td><a href="permit-details.php?id=<?php echo (int)$row['permit_id']; ?>" class="table-link">View</a></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>

  <!--  REJECTED  -->
  <div class="panel">
    <h3>Rejected Requests</h3>
    <table>
      <thead><tr>
        <th>Permit ID</th><th>Type</th><th>Applicant</th><th>Reason</th><th>Action</th>
      </tr></thead>
      <tbody>
      <?php if ($resRejected->num_rows === 0): ?>
        <tr><td colspan="5" style="text-align:center;">No rejected requests.</td></tr>
      <?php else: while ($row = $resRejected->fetch_assoc()): ?>
        <tr>
          <td><?php echo permit_code($row['permit_id']); ?></td>
          <td><?php echo e(type_label($row['permit_type'])); ?></td>
          <td><?php echo e($row['first_name'].' '.$row['last_name']); ?></td>
          <td><?php echo e($row['rejection_reason'] ?: '—'); ?></td>
          <td><a href="permit-details.php?id=<?php echo (int)$row['permit_id']; ?>" class="table-link">View</a></td>
        </tr>
      <?php endwhile; endif; ?>
      </tbody>
    </table>
  </div>

</main>

<footer><p>&copy; 2026 CityBridge. Smart cities, seamless access.</p></footer>
<script src="script.js"></script>
</body>
</html>
