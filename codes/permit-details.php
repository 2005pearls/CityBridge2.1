<?php
include "db.php";
include "auth.php";

// Single page for both users and admins
require_login();

$role       = $_SESSION["role"] ?? "user";
$account_id = (int)$_SESSION["account_id"];
$is_admin   = ($role === "admin");

$permit_id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['permit_id'] ?? 0);
$error     = "";

/* -------- Admin actions (approve / reject) -------- */
if ($is_admin && $_SERVER["REQUEST_METHOD"] === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "approve") {
        $stmt = $conn->prepare(
          "UPDATE permit
           SET status='approved',
               reviewed_date=NOW(),
               approved_date=NOW(),
               reviewed_by_admin_id=?,
               expiry_date=DATE_ADD(CURDATE(), INTERVAL 12 MONTH)
           WHERE permit_id=? AND status='pending'");
        $stmt->bind_param("ii", $account_id, $permit_id);
        $stmt->execute();
        $stmt->close();
        $_SESSION['flash'] = "Permit has been approved successfully.";
        header("Location: admin.php");
        exit();

    } elseif ($action === "reject") {
        $reason = trim($_POST["reject_reason"] ?? "");
        if ($reason === "") {
            $error = "Please provide a reason for rejection.";
        } else {
            $stmt = $conn->prepare(
              "UPDATE permit
               SET status='rejected',
                   reviewed_date=NOW(),
                   reviewed_by_admin_id=?,
                   rejection_reason=?
               WHERE permit_id=? AND status='pending'");
            $stmt->bind_param("isi", $account_id, $reason, $permit_id);
            $stmt->execute();
            $stmt->close();
            $_SESSION['flash'] = "Permit has been rejected.";
            header("Location: admin.php");
            exit();
        }
    }
}

/* -------- Load permit (scoped to user if not admin) -------- */
if ($is_admin) {
    $sql = "SELECT p.*, ua.first_name, ua.last_name, ua.phone_number, a.email
            FROM permit p
            JOIN user_account ua ON ua.account_id = p.user_account_id
            JOIN account a       ON a.account_id  = p.user_account_id
            WHERE p.permit_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $permit_id);
} else {
    $sql = "SELECT p.*, ua.first_name, ua.last_name, ua.phone_number, a.email
            FROM permit p
            JOIN user_account ua ON ua.account_id = p.user_account_id
            JOIN account a       ON a.account_id  = p.user_account_id
            WHERE p.permit_id = ? AND p.user_account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $permit_id, $account_id);
}
$stmt->execute();
$permit = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$permit) {
    header("Location: " . ($is_admin ? "admin.php" : "my-permits.php"));
    exit();
}

$status = $permit['status']; // 'pending' | 'approved' | 'rejected'

/* -------- Load sub-type row -------- */
$sub = null;
switch ($permit['permit_type']) {
    case 'labor':      $sub = $conn->query("SELECT * FROM labor_permit      WHERE permit_id=$permit_id")->fetch_assoc(); break;
    case 'equipment':  $sub = $conn->query("SELECT * FROM equipment_permit  WHERE permit_id=$permit_id")->fetch_assoc(); break;
    case 'medical':    $sub = $conn->query("SELECT * FROM medical_permit    WHERE permit_id=$permit_id")->fetch_assoc(); break;
    case 'electronic': $sub = $conn->query("SELECT * FROM electronic_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
}
$att = $conn->query("SELECT * FROM attachment WHERE permit_id=$permit_id LIMIT 1")->fetch_assoc();

/* -------- Expiry check (for renewal eligibility) -------- */
$is_expired = false;
if ($status === 'approved' && !empty($permit['expiry_date'])) {
    $t = strtotime($permit['expiry_date']);
    if ($t !== false && $t < strtotime(date('Y-m-d'))) {
        $is_expired = true;
    }
}

/* -------- Per-status / per-role page config (fully dynamic) -------- */
$userNotes = [
    'pending'  => 'This permit is currently under review and can still be edited.',
    'approved' => $is_expired
                    ? 'This permit has expired. You can submit a renewal request to continue your activity.'
                    : 'This permit has been approved. You can submit a renewal request when it is close to expiry.',
    'rejected' => 'This permit was rejected. Please submit a new request with the corrected information.',
];
$adminNotes = [
    'pending'  => 'Review the permit request information and take action below.',
    'approved' => 'This permit has been approved.',
    'rejected' => 'This permit request has been rejected.',
];
$titles = [
    'pending'  => $is_admin ? 'Review permit request information and take action.'
                            : 'Review your submitted permit information.',
    'approved' => $is_admin ? 'Review approved permit information.'
                            : 'Review your approved permit information.',
    'rejected' => $is_admin ? 'Review rejected permit information.'
                            : 'Review your rejected permit and update the required information.',
];
$dateConfig = [
    'pending'  => ['label' => 'Submitted Date', 'value' => fmt_date($permit['submitted_date']), 'expiry' => 'Not issued yet'],
    'approved' => ['label' => 'Approval Date',  'value' => fmt_date($permit['approved_date']),  'expiry' => fmt_date($permit['expiry_date'])],
    'rejected' => ['label' => 'Reviewed Date',  'value' => fmt_date($permit['reviewed_date']),  'expiry' => '—'],
];

$badgeLabel = ucfirst($status);
$title      = $titles[$status];
$note       = $is_admin ? $adminNotes[$status] : $userNotes[$status];
$dc         = $dateConfig[$status];

// Flash messages are now rendered as toasts via flash_attrs() on <body>
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Permit Details - <?php echo e($badgeLabel); ?></title>
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
      <?php if ($is_admin): ?>
        <a href="logout.php" class="logout">Log Out</a>
      <?php else: ?>
        <a href="user.php">Profile</a>
        <a href="my-permits.php" class="active">My Permits</a>
        <a href="reqprimt.php">Request a Permit</a>
        <a href="safety-guidelines.php">Safety Guidelines</a>
        <a href="authorities.php">View Authorities</a>
        <a href="logout.php" class="logout">Log Out</a>
      <?php endif; ?>
    </nav>
  </header>

  <div class="breadcrumb-container">
    <div class="breadcrumb">
      <?php if ($is_admin): ?>
        <a href="admin.php">Admin Dashboard</a><span>›</span>
        <span>Permit Details</span>
      <?php else: ?>
        <a href="user.php">User Dashboard</a><span>›</span>
        <a href="my-permits.php">My Permits</a><span>›</span>
        <span>Permit Details</span>
      <?php endif; ?>
    </div>
  </div>

  <main class="dashboard">

    <div class="page-title">
      <h1>Permit Details</h1>
      <p><?php echo e($title); ?></p>
    </div>

    <?php if ($error): ?>
      <script>document.addEventListener("DOMContentLoaded",function(){window.showToast && window.showToast(<?php echo json_encode($error); ?>,"error");});</script>
    <?php endif; ?>

    <div class="details-grid">
      <div class="panel">
        <h3>Permit Summary</h3>
        <div class="info-row"><span class="label">Permit ID</span><span class="value"><?php echo permit_code($permit['permit_id']); ?></span></div>
        <div class="info-row"><span class="label">Permit Type</span><span class="value"><?php echo e(type_label($permit['permit_type'])); ?></span></div>
        <div class="info-row"><span class="label"><?php echo e($dc['label']); ?></span><span class="value"><?php echo e($dc['value']); ?></span></div>
        <div class="info-row"><span class="label">Status</span><span class="value"><span class="badge <?php echo e($status); ?>"><?php echo e($badgeLabel); ?></span></span></div>
        <div class="info-row">
          <span class="label">Expiry Date</span>
          <span class="value">
            <?php echo e($dc['expiry']); ?>
            <?php if ($is_expired): ?><em style="color:#e0a74f;margin-left:6px;">(Expired)</em><?php endif; ?>
          </span>
        </div>
      </div>

      <div class="panel">
        <h3>Applicant Information</h3>
        <div class="info-row"><span class="label">Full Name</span><span class="value"><?php echo e($permit['first_name'].' '.$permit['last_name']); ?></span></div>
        <div class="info-row"><span class="label">Email</span><span class="value"><?php echo e($permit['email']); ?></span></div>
        <div class="info-row"><span class="label">Phone Number</span><span class="value"><?php echo e($permit['phone_number']); ?></span></div>
      </div>
    </div>

    <div class="panel">
      <h3>Permit Details</h3>
      <div class="two-panels permit-info-grid">
        <div>
        <?php if ($permit['permit_type'] === 'labor' && $sub): ?>
          <div class="info-row"><span class="label">Number of Workers</span><span class="value"><?php echo (int)$sub['number_of_workers']; ?></span></div>
          <div class="info-row"><span class="label">Job Title</span><span class="value"><?php echo e($sub['job_title']); ?></span></div>
          <div class="info-row"><span class="label">Supervisor Name</span><span class="value"><?php echo e($sub['supervisor_name']); ?></span></div>
          <div class="info-row"><span class="label">Employer Name</span><span class="value"><?php echo e($sub['employer_name']); ?></span></div>
        <?php elseif ($permit['permit_type'] === 'equipment' && $sub): ?>
          <div class="info-row"><span class="label">Equipment Type</span><span class="value"><?php echo e($sub['equipment_type']); ?></span></div>
          <div class="info-row"><span class="label">Serial Number</span><span class="value"><?php echo e($sub['serial_number']); ?></span></div>
          <div class="info-row"><span class="label">Operator Name</span><span class="value"><?php echo e($sub['operator_name']); ?></span></div>
          <div class="info-row"><span class="label">Operator License No.</span><span class="value"><?php echo e($sub['operator_license_number']); ?></span></div>
        <?php elseif ($permit['permit_type'] === 'medical' && $sub): ?>
          <div class="info-row"><span class="label">Device Name</span><span class="value"><?php echo e($sub['device_name']); ?></span></div>
          <div class="info-row"><span class="label">Manufacturer</span><span class="value"><?php echo e($sub['manufacturer']); ?></span></div>
          <div class="info-row"><span class="label">Facility Name</span><span class="value"><?php echo e($sub['facility_name']); ?></span></div>
        <?php elseif ($permit['permit_type'] === 'electronic' && $sub): ?>
          <div class="info-row"><span class="label">Device Type</span><span class="value"><?php echo e($sub['device_type']); ?></span></div>
          <div class="info-row"><span class="label">Manufacturer</span><span class="value"><?php echo e($sub['device_manufacturer']); ?></span></div>
          <div class="info-row"><span class="label">Model</span><span class="value"><?php echo e($sub['device_model']); ?></span></div>
          <div class="info-row"><span class="label">Quantity</span><span class="value"><?php echo (int)$sub['device_quantity']; ?></span></div>
          <div class="info-row"><span class="label">Use Type</span><span class="value"><?php echo e(ucfirst($sub['use_type'])); ?></span></div>
        <?php endif; ?>
        </div>

        <div>
          <?php if ($att): ?>
          <div class="info-row"><span class="label">Attachment</span><span class="value"><a href="<?php echo e($att['file_path']); ?>" class="table-link" target="_blank">View File</a></span></div>
          <?php endif; ?>
          <?php if ($status === 'approved'): ?>
            <div class="info-row"><span class="label">Approval Date</span><span class="value"><?php echo fmt_date($permit['approved_date']); ?></span></div>
          <?php else: ?>
            <div class="info-row"><span class="label">Last Updated</span><span class="value"><?php echo fmt_date($permit['last_updated']); ?></span></div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <?php if ($status === 'rejected'): ?>
    <div class="panel">
      <h3>Rejection Reason</h3>
      <div class="message-box">
        <?php echo e($permit['rejection_reason'] ?: 'No reason provided.'); ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="panel">
      <h3>Actions</h3>

      <?php if ($is_admin && $status === 'pending'): ?>
        <!-- Admin: pending permit - approve / reject -->
        <form action="" method="post" style="display:inline;">
          <input type="hidden" name="permit_id" value="<?php echo $permit_id; ?>">
          <input type="hidden" name="action" value="approve">
          <button type="submit" class="btn approve">Approve</button>
        </form>

        <button type="button" class="btn reject" onclick="document.getElementById('reject-box').style.display='block';">Reject</button>

        <div id="reject-box" style="display:none; margin-top:15px;">
          <form id="rejectForm" action="" method="post">
            <input type="hidden" name="permit_id" value="<?php echo $permit_id; ?>">
            <input type="hidden" name="action" value="reject">
            <textarea name="reject_reason" id="reject_reason" placeholder="Write reason for rejection..." required
                      style="width:100%;min-height:80px;"></textarea>
            <br><br>
            <button type="submit" class="btn reject">Confirm Reject</button>
          </form>
        </div>

      <?php elseif ($is_admin): ?>
        <!-- Admin viewing a decided permit -->
        <div class="action-row">
          <a href="admin.php" class="btn btn-admin">Back</a>
        </div>

      <?php else: ?>
        <!-- User actions vary by status -->
        <div class="action-row">
          <a href="my-permits.php" class="btn btn-admin">Back</a>
          <?php if ($status === 'pending'): ?>
            <a href="edit-permit-pending.php?id=<?php echo $permit_id; ?>" class="btn btn-primary">Edit Permit</a>
          <?php elseif ($status === 'approved'): ?>
            <a href="renew.php?id=<?php echo $permit_id; ?>" class="btn btn-primary">Renew Permit</a>
          <?php elseif ($status === 'rejected'): ?>
            <a href="reqprimt.php" class="btn btn-primary">Submit New Request</a>
          <?php endif; ?>
        </div>
      <?php endif; ?>

      <p class="action-note"><?php echo e($note); ?></p>
    </div>

  </main>

  <footer><p>&copy; 2026 CityBridge. Smart cities, seamless access.</p></footer>

<script src="script.js"></script>
<script>
  // Require rejection reason before submit (admin)
  (function(){
    var form = document.getElementById('rejectForm');
    if (!form) return;
    form.addEventListener('submit', function(e){
      var ta = document.getElementById('reject_reason');
      if (!ta || ta.value.trim() === '') {
        e.preventDefault();
        if (window.showToast) window.showToast('Please provide a reason for rejection.', 'error');
        else alert('Please provide a reason for rejection.');
        if (ta) ta.focus();
      }
    });
  })();
</script>
</body>
</html>
