<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];
$permit_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// ===== Load permit (must belong to this user, must be pending)!! =====
$sql = "SELECT p.*, ua.first_name, ua.last_name, ua.phone_number, a.email
        FROM permit p
        JOIN user_account ua ON ua.account_id = p.user_account_id
        JOIN account a       ON a.account_id  = p.user_account_id
        WHERE p.permit_id = ? AND p.user_account_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $permit_id, $account_id);
$stmt->execute();
$permit = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$permit || $permit['status'] !== 'pending') {
    header("Location: my-permits.php");
    exit();
}

// ===== Load sub-type row based on permit_type =====
$sub = null;
switch ($permit['permit_type']) {
    case 'labor':
        $sub = $conn->query("SELECT * FROM labor_permit WHERE permit_id=$permit_id")->fetch_assoc();
        break;
    case 'equipment':
        $sub = $conn->query("SELECT * FROM equipment_permit WHERE permit_id=$permit_id")->fetch_assoc();
        break;
    case 'medical':
        $sub = $conn->query("SELECT * FROM medical_permit WHERE permit_id=$permit_id")->fetch_assoc();
        break;
    case 'electronic':
        $sub = $conn->query("SELECT * FROM electronic_permit WHERE permit_id=$permit_id")->fetch_assoc();
        break;
}

// ===== Attachment (just first one, if any) =====
$att = $conn->query("SELECT * FROM attachment WHERE permit_id=$permit_id LIMIT 1")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Permit Details - Pending</title>
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

  <div class="breadcrumb-container">
    <div class="breadcrumb">
      <a href="user.php">User Dashboard</a>
      <span>›</span>
      <a href="my-permits.php">My Permits</a>
      <span>›</span>
      <span>Permit Details</span>
    </div>
  </div>

  <main class="dashboard">

    <div class="page-title">
      <h1>Permit Details</h1>
      <p>Review your submitted permit information.</p>
    </div>

    <div class="details-grid">
      <div class="panel">
        <h3>Permit Summary</h3>
        <div class="info-row"><span class="label">Permit ID</span><span class="value"><?php echo permit_code($permit['permit_id']); ?></span></div>
        <div class="info-row"><span class="label">Permit Type</span><span class="value"><?php echo e(type_label($permit['permit_type'])); ?></span></div>
        <div class="info-row"><span class="label">Submitted Date</span><span class="value"><?php echo fmt_date($permit['submitted_date']); ?></span></div>
        <div class="info-row"><span class="label">Status</span><span class="value"><span class="badge pending">Pending</span></span></div>
        <div class="info-row"><span class="label">Expiry Date</span><span class="value">Not issued yet</span></div>
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
          <div class="info-row"><span class="label">Last Updated</span><span class="value"><?php echo fmt_date($permit['last_updated']); ?></span></div>
        </div>
      </div>
    </div>

    <div class="panel">
      <h3>Actions</h3>
      <div class="action-row">
        <a href="my-permits.php" class="btn btn-admin">Back</a>
        <a href="edit-permit-pending.php?id=<?php echo $permit_id; ?>" class="btn btn-primary">Edit Permit</a>
      </div>
      <p class="action-note">This permit is currently under review and can still be edited.</p>
    </div>

  </main>

  <footer><p>&copy; 2026 CityBridge. Smart cities, seamless access.</p></footer>
<script src="script.js"></script>
</body>
</html>
