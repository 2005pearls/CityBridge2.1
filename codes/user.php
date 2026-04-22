<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];

// load user + company info
$sql = "SELECT a.username, a.email,
               ua.first_name, ua.last_name, ua.phone_number, ua.job_title,
               c.company_name, c.sector
        FROM account a
        JOIN user_account ua ON ua.account_id = a.account_id
        JOIN company      c  ON c.company_id  = ua.company_id
        WHERE a.account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Stats
$statSql = "SELECT
              SUM(CASE WHEN status='pending'  THEN 1 ELSE 0 END) AS pending,
              SUM(CASE WHEN status='approved' THEN 1 ELSE 0 END) AS approved,
              SUM(CASE WHEN status='rejected' THEN 1 ELSE 0 END) AS rejected,
              COUNT(*) AS total
            FROM permit
            WHERE user_account_id = ?";
$stmt = $conn->prepare($statSql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$stats = $stmt->get_result()->fetch_assoc();
$stmt->close();

$total    = (int)($stats["total"]    ?? 0);
$pending  = (int)($stats["pending"]  ?? 0);
$approved = (int)($stats["approved"] ?? 0);
$rejected = (int)($stats["rejected"] ?? 0);

// Recent permits (latest 5) 
$recentSql = "SELECT permit_id, permit_type, status, submitted_date
              FROM permit
              WHERE user_account_id = ?
              ORDER BY submitted_date DESC
              LIMIT 5";
$stmt = $conn->prepare($recentSql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$recent = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - CityBridge</title>
  <link rel="stylesheet" href="style.css" />
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
      <a href="user.php" class="active">Profile</a>
      <a href="my-permits.php">My Permits</a>
      <a href="reqprimt.php">Request a Permit</a>
      <a href="safety-guidelines.php">Safety Guidelines</a>
      <a href="authorities.php">View Authorities</a>
      <a href="logout.php" class="logout">Log Out</a>
    </nav>
  </header>

  <main class="dashboard">

    <div class="page-title">
      <h1>My Dashboard</h1>
      <p>Your company profile and permit activity at a glance.</p>
    </div>

    <div class="two-panels">
      <div class="panel">
        <h3>Company Information</h3>
        <div class="info-row">
          <span class="label">Company Name</span>
          <span class="value"><?php echo e($me['company_name']); ?></span>
        </div>
        <div class="info-row">
          <span class="label">Industry / Sector</span>
          <span class="value"><?php echo e($me['sector']); ?></span>
        </div>
      </div>

      <div class="panel">
        <h3>Account Representative</h3>
        <div class="info-row">
          <span class="label">First Name</span>
          <span class="value"><?php echo e($me['first_name']); ?></span>
        </div>
        <div class="info-row">
          <span class="label">Last Name</span>
          <span class="value"><?php echo e($me['last_name']); ?></span>
        </div>
        <div class="info-row">
          <span class="label">Phone Number</span>
          <span class="value"><?php echo e($me['phone_number']); ?></span>
        </div>
        <div class="info-row">
          <span class="label">Email</span>
          <span class="value"><?php echo e($me['email']); ?></span>
        </div>
        <div class="info-row">
          <span class="label">Username</span>
          <span class="value"><?php echo e($me['username']); ?></span>
        </div>
      </div>
    </div>

    <div class="stats">
      <div class="stat">
        <p>Total Permits</p>
        <span><?php echo $total; ?></span>
      </div>
      <div class="stat">
        <p>Pending</p>
        <span class="pending"><?php echo $pending; ?></span>
      </div>
      <div class="stat">
        <p>Approved</p>
        <span class="approved"><?php echo $approved; ?></span>
      </div>
      <div class="stat">
        <p>Rejected</p>
        <span class="rejected"><?php echo $rejected; ?></span>
      </div>
    </div>

    <div class="panel">
      <h3>Recent Permits <a href="my-permits.php">View All</a></h3>
      <table>
        <thead>
          <tr>
            <th>Permit ID</th>
            <th>Type</th>
            <th>Submitted</th>
            <th>Status</th>
          </tr>
        </thead>
        <tbody>
          <?php if ($recent->num_rows === 0): ?>
            <tr><td colspan="4" style="text-align:center;">No permits submitted yet.</td></tr>
          <?php else: while ($row = $recent->fetch_assoc()): ?>
            <tr>
              <td><?php echo permit_code($row['permit_id']); ?></td>
              <td><?php echo e(type_label($row['permit_type'])); ?></td>
              <td><?php echo fmt_date($row['submitted_date']); ?></td>
              <td><span class="badge <?php echo e($row['status']); ?>"><?php echo ucfirst($row['status']); ?></span></td>
            </tr>
          <?php endwhile; endif; ?>
        </tbody>
      </table>
    </div>

  </main>

  <footer>
    <p>&copy; 2026 CityBridge. Smart cities, seamless access.</p>
  </footer>

<script src="script.js"></script>
</body>
</html>
