<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];

// Optional status filter 
$allowed = ["all", "pending", "approved", "rejected"];
$filter  = $_GET["status"] ?? "all";
if (!in_array($filter, $allowed, true)) $filter = "all";

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

// Permits 
if ($filter === "all") {
    $sql = "SELECT permit_id, permit_type, status, submitted_date, expiry_date
            FROM permit
            WHERE user_account_id = ?
            ORDER BY submitted_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
} else {
    $sql = "SELECT permit_id, permit_type, status, submitted_date, expiry_date
            FROM permit
            WHERE user_account_id = ? AND status = ?
            ORDER BY submitted_date DESC";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("is", $account_id, $filter);
}
$stmt->execute();
$permits = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>My Permits - CityBridge</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <header class="user-header">
    <svg viewBox="0 0 500 72" width="320" xmlns="http://www.w3.org/2000/svg">
      <text x="0" y="44" font-family="'Helvetica Neue', 'Segoe UI', Arial, sans-serif" font-size="42" font-weight="700" letter-spacing="-1">
        <tspan fill="#e8f0fb">City</tspan><tspan fill="#4baee8">Bridge</tspan>
      </text>
      <line x1="0" y1="52" x2="220" y2="52" stroke="#4baee8" stroke-width="1.6" opacity="0.4" />
      <text x="0" y="67" font-family="'DM Sans', 'Helvetica Neue', Arial, sans-serif" font-size="10.5" font-weight="300" fill="#7dcef8" letter-spacing="4" opacity="0.82">CONNECTING COMMUNITIES</text>
    </svg>
    <nav>
      <a href="user.php">Profile</a>
      <a href="my-permits.php" class="active">My Permits</a>
      <a href="reqprimt.php">Request a Permit</a>
      <a href="safety-guidelines.php">Safety Guidelines</a>
      <a href="authorities.php">View Authorities</a>
      <a href="logout.php" class="logout">Log Out</a>
    </nav>
  </header>

  <main class="dashboard">

    <div class="page-title">
      <h1>My Permits</h1>
      <p>Track, filter, and manage all your permit requests in one place.</p>
    </div>

    <div class="stats">
      <div class="stat"><p>Total Permits</p><span><?php echo (int)$stats['total']; ?></span></div>
      <div class="stat"><p>Pending</p><span class="pending"><?php echo (int)$stats['pending']; ?></span></div>
      <div class="stat"><p>Approved</p><span class="approved"><?php echo (int)$stats['approved']; ?></span></div>
      <div class="stat"><p>Rejected</p><span class="rejected"><?php echo (int)$stats['rejected']; ?></span></div>
    </div>

    <div class="panel">
      <h3>Requested Permits</h3>

      <table>
        <thead>
          <tr>
            <th>Permit ID</th>
            <th>Type</th>
            <th>Submitted</th>
            <th>Expiry Date</th>
            <th>
              <div class="status-filter-header">
                <span>Status</span>
                <select onchange="location.href='my-permits.php?status='+this.value">
                  <option value="all"      <?php echo $filter==='all'?'selected':''; ?>>All</option>
                  <option value="pending"  <?php echo $filter==='pending'?'selected':''; ?>>Pending</option>
                  <option value="approved" <?php echo $filter==='approved'?'selected':''; ?>>Approved</option>
                  <option value="rejected" <?php echo $filter==='rejected'?'selected':''; ?>>Rejected</option>
                </select>
              </div>
            </th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
        <?php if ($permits->num_rows === 0): ?>
          <tr><td colspan="6" style="text-align:center;">No permits found.</td></tr>
        <?php else: while ($row = $permits->fetch_assoc()):
            $id = (int)$row['permit_id'];
            $st = $row['status'];
            $detailsPage =
                $st === 'approved' ? "permit-details-approved.php?id=$id"  :
                ($st === 'rejected' ? "permit-details-rejected.php?id=$id" :
                                      "permit-details-pending.php?id=$id");
        ?>
          <tr class="permit-row" data-status="<?php echo e($st); ?>">
            <td><?php echo permit_code($id); ?></td>
            <td><?php echo e(type_label($row['permit_type'])); ?></td>
            <td><?php echo fmt_date($row['submitted_date']); ?></td>
            <td><?php echo fmt_date($row['expiry_date']); ?></td>
            <td><span class="badge <?php echo e($st); ?>"><?php echo ucfirst($st); ?></span></td>
            <td><a href="<?php echo $detailsPage; ?>" class="table-link">View Details</a></td>
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
