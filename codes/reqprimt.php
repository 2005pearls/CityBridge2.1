<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];
$error   = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $permit_type = $_POST["permit_type"] ?? "";

    if (!in_array($permit_type, ["labor","equipment","medical","electronic"], true)) {
        $error = "Please select a valid permit type.";
    } else {
        // Use a transaction so if sub-type insert fails we rollback the parent permit row.
        $conn->begin_transaction();

        try {
            // ---- 1) Insert parent permit ----
            $insPermit = $conn->prepare(
              "INSERT INTO permit (user_account_id, permit_type, status) VALUES (?, ?, 'pending')"
            );
            $insPermit->bind_param("is", $account_id, $permit_type);
            $insPermit->execute();
            $permit_id = $conn->insert_id;
            $insPermit->close();

            // ---- 2) Insert sub-type row ----
            if ($permit_type === "labor") {
                $workers    = (int)($_POST["workers"] ?? 0);
                $job_title  = trim($_POST["job_title"] ?? "");
                $supervisor = trim($_POST["supervisor"] ?? "");
                $employer   = trim($_POST["employer"]   ?? "");
                if ($workers <= 0 || $job_title === "" || $supervisor === "") {
                    throw new Exception("All labor fields are required.");
                }
                $s = $conn->prepare(
                  "INSERT INTO labor_permit (permit_id, number_of_workers, job_title, supervisor_name, employer_name)
                   VALUES (?, ?, ?, ?, ?)");
                $s->bind_param("iisss", $permit_id, $workers, $job_title, $supervisor, $employer);
                $s->execute();
                $s->close();

            } elseif ($permit_type === "equipment") {
                $eq_type = trim($_POST["equipment_type"]   ?? "");
                $serial  = trim($_POST["serial_number"]    ?? "");
                $op      = trim($_POST["operator"]         ?? "");
                $op_lic  = trim($_POST["operator_license"] ?? "");
                if ($eq_type === "" || $serial === "" || $op === "" || $op_lic === "") {
                    throw new Exception("All equipment fields are required.");
                }
                $s = $conn->prepare(
                  "INSERT INTO equipment_permit (permit_id, equipment_type, serial_number, operator_name, operator_license_number)
                   VALUES (?, ?, ?, ?, ?)");
                $s->bind_param("issss", $permit_id, $eq_type, $serial, $op, $op_lic);
                $s->execute();
                $s->close();

            } elseif ($permit_type === "medical") {
                $dev_name     = trim($_POST["device_name"]   ?? "");
                $manufacturer = trim($_POST["manufacturer"]  ?? "");
                $facility     = trim($_POST["facility_name"] ?? "");
                if ($dev_name === "" || $manufacturer === "" || $facility === "") {
                    throw new Exception("All medical fields are required.");
                }
                $s = $conn->prepare(
                  "INSERT INTO medical_permit (permit_id, device_name, manufacturer, facility_name)
                   VALUES (?, ?, ?, ?)");
                $s->bind_param("isss", $permit_id, $dev_name, $manufacturer, $facility);
                $s->execute();
                $s->close();

            } elseif ($permit_type === "electronic") {
                $dev_type = trim($_POST["device_type"]         ?? "");
                $manuf    = trim($_POST["device_manufacturer"] ?? "");
                $model    = trim($_POST["device_model"]        ?? "");
                $qty      = (int)($_POST["device_quantity"]    ?? 0);
                $use_type = $_POST["use_type"]                 ?? "";
                if ($dev_type === "" || $manuf === "" || $model === "" || $qty <= 0
                    || !in_array($use_type, ["personal","commercial"], true)) {
                    throw new Exception("All electronic fields are required.");
                }
                $s = $conn->prepare(
                  "INSERT INTO electronic_permit (permit_id, device_type, device_manufacturer, device_model, device_quantity, use_type)
                   VALUES (?, ?, ?, ?, ?, ?)");
                $s->bind_param("isssis", $permit_id, $dev_type, $manuf, $model, $qty, $use_type);
                $s->execute();
                $s->close();
            }

            // ---- 3) Attachment !optional ----
            // Map each permit type to the file input name defined in the form.
            $fileFieldByType = [
                "labor"      => "labor_contract",
                "equipment"  => "equipment_docs",
                "medical"    => "device_cert",
                "electronic" => "tech_spec",
            ];
            $fileField = $fileFieldByType[$permit_type] ?? null;

            if ($fileField && isset($_FILES[$fileField]) && $_FILES[$fileField]["error"] === UPLOAD_ERR_OK) {
              $uploadDir = __DIR__ . "/uploads/";
                if (!is_dir($uploadDir)) @mkdir($uploadDir, 0777, true);

                $origName  = basename($_FILES[$fileField]["name"]);
                $ext       = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
                $allowed   = ["pdf","jpg","jpeg","png"];
                if (!in_array($ext, $allowed, true)) {
                    throw new Exception("Only PDF, JPG or PNG files are allowed.");
                }
                $safeName  = "p{$permit_id}_" . time() . "." . $ext;
               $destPath  = $uploadDir . $safeName;

                if (!move_uploaded_file($_FILES[$fileField]["tmp_name"], $destPath)) {
                    throw new Exception("Failed to save uploaded file.");
                }
                $dbPath = "uploads/" . $safeName;
                $a = $conn->prepare(
                  "INSERT INTO attachment (permit_id, file_name, file_type, file_path)
                   VALUES (?, ?, ?, ?)");
                $a->bind_param("isss", $permit_id, $origName, $ext, $dbPath);
                $a->execute();
                $a->close();
            }

            $conn->commit();
            $_SESSION['flash'] = "Permit request submitted successfully! Your permit ID is " . permit_code($permit_id) . ".";
            header("Location: permit-details.php?id=" . $permit_id);
            exit();

        } catch (Exception $ex) {
            $conn->rollback();
            $error = "Could not submit permit: " . $ex->getMessage();
        }
    }
}

// Load applicant info to pre-fill the form
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
  <title>Request a Permit - CityBridge</title>
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
      <a href="my-permits.php">My Permits</a>
      <a href="reqprimt.php" class="active">Request a Permit</a>
      <a href="safety-guidelines.php">Safety Guidelines</a>
      <a href="authorities.php">View Authorities</a>
      <a href="logout.php" class="logout">Log Out</a>
    </nav>
  </header>

  <main>
    <div class="form-card wide">

      <h2>Permit Request</h2>
      <p class="subtitle">Submit a permit request through the CityBridge platform</p>

      <form id="permitForm" action="" method="post" enctype="multipart/form-data" novalidate>

        <p class="section-label">Applicant Information</p>

        <div class="field">
          <label for="fullname">Full Name</label>
          <input type="text" id="fullname" name="fullname"
                 value="<?php echo e(trim(($me['first_name'] ?? '').' '.($me['last_name'] ?? ''))); ?>"
                 readonly />
        </div>

        <div class="two-col">
          <div class="field">
            <label for="email">Email</label>
            <input type="email" id="email" name="email"
                   value="<?php echo e($me['email'] ?? ''); ?>" readonly />
          </div>
          <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone"
                   value="<?php echo e($me['phone_number'] ?? ''); ?>" readonly />
          </div>
        </div>

        <p class="section-label">Permit Type</p>
        <div class="field">
          <label for="permitType">Permit Type</label>
          <select id="permitType" name="permit_type" required>
            <option value="">Select Permit Type</option>
            <option value="labor">Labor Permit</option>
            <option value="equipment">Construction Equipment Permit</option>
            <option value="medical">Medical Device Permit</option>
            <option value="electronic">Electronic Device Permit</option>
          </select>
        </div>

        <!-- LABOR -->
        <div id="laborFields" class="hidden">
          <p class="section-label">Labor Details</p>
          <div class="two-col">
            <div class="field"><label>Number of Workers</label><input type="number" id="workers" name="workers" placeholder="e.g. 5" /></div>
            <div class="field"><label>Job Title</label><input type="text" id="job_title" name="job_title" placeholder="e.g. Site Technician" /></div>
          </div>
          <div class="two-col">
            <div class="field"><label>Supervisor Name</label><input type="text" id="supervisor" name="supervisor" placeholder="On-site supervisor" /></div>
            <div class="field"><label>Employer Name</label><input type="text" id="employer" name="employer" placeholder="Employer / company" /></div>
          </div>
          <p class="section-label">Attachments</p>
          <div class="field"><label>Labor Contract</label><input type="file" id="labor_contract" name="labor_contract" accept=".pdf,.jpg,.png" /></div>
        </div>

        <!-- EQUIPMENT -->
        <div id="equipmentFields" class="hidden">
          <p class="section-label">Equipment Details</p>
          <div class="two-col">
            <div class="field"><label>Equipment Type</label><input type="text" id="equipment_type" name="equipment_type" placeholder="e.g. Tower Crane" /></div>
            <div class="field"><label>Serial Number</label><input type="text" id="serial_number" name="serial_number" placeholder="Equipment serial" /></div>
          </div>
          <div class="two-col">
            <div class="field"><label>Operator Name</label><input type="text" id="operator" name="operator" placeholder="Licensed operator" /></div>
            <div class="field"><label>Operator License Number</label><input type="text" id="operator_license" name="operator_license" placeholder="License number" /></div>
          </div>
          <p class="section-label">Attachments</p>
          <div class="field"><label>Equipment Registration &amp; Insurance</label><input type="file" id="equipment_docs" name="equipment_docs" accept=".pdf,.jpg,.png" /></div>
        </div>

        <!-- MEDICAL -->
        <div id="medicalFields" class="hidden">
          <p class="section-label">Device Details</p>
          <div class="two-col">
            <div class="field"><label>Device Name</label><input type="text" id="device_name" name="device_name" placeholder="Official device name" /></div>
            <div class="field"><label>Manufacturer</label><input type="text" id="manufacturer" name="manufacturer" placeholder="Device manufacturer" /></div>
          </div>
          <div class="field"><label>Facility Name</label><input type="text" id="facility_name" name="facility_name" placeholder="Hospital or clinic name" /></div>
          <p class="section-label">Attachments</p>
          <div class="field"><label>Device Certification Document</label><input type="file" id="device_cert" name="device_cert" accept=".pdf,.jpg,.png" /></div>
        </div>

        <!-- ELECTRONIC -->
        <div id="electronicFields" class="hidden">
          <p class="section-label">Device Details</p>
          <div class="two-col">
            <div class="field"><label>Device Type</label><input type="text" id="device_type" name="device_type" placeholder="e.g. IoT Sensor" /></div>
            <div class="field"><label>Manufacturer</label><input type="text" id="device_manufacturer" name="device_manufacturer" placeholder="Manufacturer name" /></div>
          </div>
          <div class="two-col">
            <div class="field"><label>Model</label><input type="text" id="device_model" name="device_model" placeholder="Model number" /></div>
            <div class="field"><label>Quantity</label><input type="number" id="device_quantity" name="device_quantity" placeholder="Number of units" /></div>
          </div>
          <div class="field">
            <label>Use Type</label>
            <select id="use_type" name="use_type">
              <option value="">Select</option>
              <option value="personal">Personal Use</option>
              <option value="commercial">Commercial Deployment</option>
            </select>
          </div>
          <p class="section-label">Attachments</p>
          <div class="field"><label>Technical Specification Sheet</label><input type="file" id="tech_spec" name="tech_spec" accept=".pdf,.jpg,.png" /></div>
        </div>

        <button type="submit" class="btn btn-primary btn-full">Submit Permit Request</button>
      </form>
    </div>
  </main>

  <footer><p>&copy; 2026 CityBridge. Smart cities, seamless access.</p></footer>

<script>
const permitType = document.getElementById("permitType");
const labor      = document.getElementById("laborFields");
const equipment  = document.getElementById("equipmentFields");
const medical    = document.getElementById("medicalFields");
const electronic = document.getElementById("electronicFields");

permitType.addEventListener("change", function () {
  labor.classList.add("hidden");
  equipment.classList.add("hidden");
  medical.classList.add("hidden");
  electronic.classList.add("hidden");
  if (this.value === "labor")      labor.classList.remove("hidden");
  if (this.value === "equipment")  equipment.classList.remove("hidden");
  if (this.value === "medical")    medical.classList.remove("hidden");
  if (this.value === "electronic") electronic.classList.remove("hidden");
});
</script>
<script src="script.js"></script>
</body>
</html>
