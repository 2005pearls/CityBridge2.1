<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];
$permit_id  = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_POST['permit_id'] ?? 0);
$error   = "";

// Load permit (must be the user's AND pending)
$stmt = $conn->prepare(
  "SELECT * FROM permit WHERE permit_id = ? AND user_account_id = ?");
$stmt->bind_param("ii", $permit_id, $account_id);
$stmt->execute();
$permit = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$permit) {
    header("Location: my-permits.php");
    exit();
}
if ($permit['status'] !== 'pending') {
    $_SESSION['flash_error'] = "This permit cannot be edited because it has already been " . $permit['status'] . ".";
    header("Location: permit-details.php?id=" . $permit_id);
    exit();
}

// Load sub-type row
$sub = null;
switch ($permit['permit_type']) {
    case 'labor':      $sub = $conn->query("SELECT * FROM labor_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
    case 'equipment':  $sub = $conn->query("SELECT * FROM equipment_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
    case 'medical':    $sub = $conn->query("SELECT * FROM medical_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
    case 'electronic': $sub = $conn->query("SELECT * FROM electronic_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
}

//  Handle UPDATE 
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    try {
        $conn->begin_transaction();

        if ($permit['permit_type'] === 'labor') {
            $workers    = (int)($_POST["workers"] ?? 0);
            $job_title  = trim($_POST["job_title"]  ?? "");
            $supervisor = trim($_POST["supervisor"] ?? "");
            $employer   = trim($_POST["employer"]   ?? "");
            $s = $conn->prepare(
              "UPDATE labor_permit SET number_of_workers=?, job_title=?, supervisor_name=?, employer_name=?
               WHERE permit_id=?");
            $s->bind_param("isssi", $workers, $job_title, $supervisor, $employer, $permit_id);
            $s->execute();
            $s->close();

        } elseif ($permit['permit_type'] === 'equipment') {
            $eq_type = trim($_POST["equipment_type"]   ?? "");
            $serial  = trim($_POST["serial_number"]    ?? "");
            $op      = trim($_POST["operator"]         ?? "");
            $op_lic  = trim($_POST["operator_license"] ?? "");
            $s = $conn->prepare(
              "UPDATE equipment_permit SET equipment_type=?, serial_number=?, operator_name=?, operator_license_number=?
               WHERE permit_id=?");
            $s->bind_param("ssssi", $eq_type, $serial, $op, $op_lic, $permit_id);
            $s->execute();
            $s->close();

        } elseif ($permit['permit_type'] === 'medical') {
            $dev_name = trim($_POST["device_name"]   ?? "");
            $manuf    = trim($_POST["manufacturer"]  ?? "");
            $facility = trim($_POST["facility_name"] ?? "");
            $s = $conn->prepare(
              "UPDATE medical_permit SET device_name=?, manufacturer=?, facility_name=? WHERE permit_id=?");
            $s->bind_param("sssi", $dev_name, $manuf, $facility, $permit_id);
            $s->execute();
            $s->close();

        } elseif ($permit['permit_type'] === 'electronic') {
            $dev_type = trim($_POST["device_type"]         ?? "");
            $manuf    = trim($_POST["device_manufacturer"] ?? "");
            $model    = trim($_POST["device_model"]        ?? "");
            $qty      = (int)($_POST["device_quantity"]    ?? 0);
            $use_type = $_POST["use_type"]                 ?? "";
            if (!in_array($use_type, ["personal","commercial"], true)) {
                throw new Exception("Invalid use type.");
            }
            $s = $conn->prepare(
              "UPDATE electronic_permit SET device_type=?, device_manufacturer=?, device_model=?, device_quantity=?, use_type=?
               WHERE permit_id=?");
            $s->bind_param("sssisi", $dev_type, $manuf, $model, $qty, $use_type, $permit_id);
            $s->execute();
            $s->close();
        }

        // Touch parent so last_updated refreshes (the column auto-updates on any UPDATE, but we ensure a change here)
        $conn->query("UPDATE permit SET last_updated = NOW() WHERE permit_id = $permit_id");

        $conn->commit();
        $_SESSION['flash'] = "Permit updated successfully.";
        header("Location: permit-details.php?id=" . $permit_id);
        exit();

    } catch (Exception $ex) {
        $conn->rollback();
        $error = "Could not save changes: " . $ex->getMessage();
    }

    // Reload sub row after failed update so form shows latest DB values
    switch ($permit['permit_type']) {
        case 'labor':      $sub = $conn->query("SELECT * FROM labor_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
        case 'equipment':  $sub = $conn->query("SELECT * FROM equipment_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
        case 'medical':    $sub = $conn->query("SELECT * FROM medical_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
        case 'electronic': $sub = $conn->query("SELECT * FROM electronic_permit WHERE permit_id=$permit_id")->fetch_assoc(); break;
    }
}

// Applicant info (read-only on this page)!!
$stmt = $conn->prepare(
  "SELECT ua.first_name, ua.last_name, ua.phone_number, a.email
   FROM account a JOIN user_account ua ON ua.account_id = a.account_id
   WHERE a.account_id = ?");
$stmt->bind_param("i", $account_id);
$stmt->execute();
$me = $stmt->get_result()->fetch_assoc();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Edit Permit - CityBridge</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body <?php echo flash_attrs(); ?><?php echo !empty($error) ? ' data-flash-error="'.e($error).'"' : ''; ?>>

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
      <a href="user.php">User Dashboard</a><span>›</span>
      <a href="my-permits.php">My Permits</a><span>›</span>
      <a href="permit-details.php?id=<?php echo $permit_id; ?>">Permit Details</a><span>›</span>
      <span>Edit Permit</span>
    </div>
  </div>

  <main>
    <div class="form-card wide">
      <h2>Edit Permit</h2>
      <p class="subtitle">Update your permit information before final review.</p>

      <form action="" method="post">
        <input type="hidden" name="permit_id" value="<?php echo $permit_id; ?>">

        <p class="section-label">Permit Summary</p>
        <div class="two-col">
          <div class="field"><label>Permit ID</label><input type="text" value="<?php echo permit_code($permit_id); ?>" readonly /></div>
          <div class="field"><label>Permit Type</label><input type="text" value="<?php echo e(type_label($permit['permit_type'])); ?>" readonly /></div>
        </div>

        <p class="section-label">Applicant Information</p>
        <div class="two-col">
          <div class="field"><label>Full Name</label><input type="text" value="<?php echo e(($me['first_name'] ?? '').' '.($me['last_name'] ?? '')); ?>" readonly /></div>
          <div class="field"><label>Phone Number</label><input type="text" value="<?php echo e($me['phone_number'] ?? ''); ?>" readonly /></div>
        </div>
        <div class="field"><label>Email Address</label><input type="email" value="<?php echo e($me['email'] ?? ''); ?>" readonly /></div>

        <?php if ($permit['permit_type'] === 'labor' && $sub): ?>
          <p class="section-label">Labor Permit Details</p>
          <div class="two-col">
            <div class="field"><label>Number of Workers</label><input type="number" name="workers" value="<?php echo (int)$sub['number_of_workers']; ?>" required /></div>
            <div class="field"><label>Job Title</label><input type="text" name="job_title" value="<?php echo e($sub['job_title']); ?>" required /></div>
          </div>
          <div class="two-col">
            <div class="field"><label>Supervisor Name</label><input type="text" name="supervisor" value="<?php echo e($sub['supervisor_name']); ?>" required /></div>
            <div class="field"><label>Employer Name</label><input type="text" name="employer" value="<?php echo e($sub['employer_name']); ?>" /></div>
          </div>

        <?php elseif ($permit['permit_type'] === 'equipment' && $sub): ?>
          <p class="section-label">Equipment Permit Details</p>
          <div class="two-col">
            <div class="field"><label>Equipment Type</label><input type="text" name="equipment_type" value="<?php echo e($sub['equipment_type']); ?>" required /></div>
            <div class="field"><label>Serial Number</label><input type="text" name="serial_number" value="<?php echo e($sub['serial_number']); ?>" required /></div>
          </div>
          <div class="two-col">
            <div class="field"><label>Operator Name</label><input type="text" name="operator" value="<?php echo e($sub['operator_name']); ?>" required /></div>
            <div class="field"><label>Operator License No.</label><input type="text" name="operator_license" value="<?php echo e($sub['operator_license_number']); ?>" required /></div>
          </div>

        <?php elseif ($permit['permit_type'] === 'medical' && $sub): ?>
          <p class="section-label">Medical Device Details</p>
          <div class="two-col">
            <div class="field"><label>Device Name</label><input type="text" name="device_name" value="<?php echo e($sub['device_name']); ?>" required /></div>
            <div class="field"><label>Manufacturer</label><input type="text" name="manufacturer" value="<?php echo e($sub['manufacturer']); ?>" required /></div>
          </div>
          <div class="field"><label>Facility Name</label><input type="text" name="facility_name" value="<?php echo e($sub['facility_name']); ?>" required /></div>

        <?php elseif ($permit['permit_type'] === 'electronic' && $sub): ?>
          <p class="section-label">Electronic Device Details</p>
          <div class="two-col">
            <div class="field"><label>Device Type</label><input type="text" name="device_type" value="<?php echo e($sub['device_type']); ?>" required /></div>
            <div class="field"><label>Manufacturer</label><input type="text" name="device_manufacturer" value="<?php echo e($sub['device_manufacturer']); ?>" required /></div>
          </div>
          <div class="two-col">
            <div class="field"><label>Model</label><input type="text" name="device_model" value="<?php echo e($sub['device_model']); ?>" required /></div>
            <div class="field"><label>Quantity</label><input type="number" name="device_quantity" value="<?php echo (int)$sub['device_quantity']; ?>" required /></div>
          </div>
          <div class="field">
            <label>Use Type</label>
            <select name="use_type" required>
              <option value="personal"   <?php echo $sub['use_type']==='personal'?'selected':''; ?>>Personal Use</option>
              <option value="commercial" <?php echo $sub['use_type']==='commercial'?'selected':''; ?>>Commercial Deployment</option>
            </select>
          </div>
        <?php endif; ?>

        <div class="action-row" style="margin-top: 10px;">
          <a href="permit-details.php?id=<?php echo $permit_id; ?>" class="btn btn-admin">Cancel</a>
          <button type="submit" class="btn btn-primary">Save Changes</button>
        </div>
      </form>
    </div>
  </main>

  <footer><p>&copy; 2026 CityBridge. Smart cities, seamless access.</p></footer>
<script src="script.js"></script>
</body>
</html>
