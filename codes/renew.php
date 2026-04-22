<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];
$permit_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// First look up the permit to check eligibility so we can give a proper message
$stmt = $conn->prepare(
  "SELECT status, expiry_date FROM permit WHERE permit_id = ? AND user_account_id = ?");
$stmt->bind_param("ii", $permit_id, $account_id);
$stmt->execute();
$p = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$p) {
    // Not the user's permit (or doesn't exist)
    $_SESSION['flash_error'] = "Permit not found.";
    header("Location: my-permits.php");
    exit();
}

// Renewal is allowed only for approved permits.
// (Expiry is informational — users can renew approved permits at any time,
//  but "Cannot Renew a Non-Approved Permit" must be enforced.)
if ($p['status'] !== 'approved') {
    $_SESSION['flash_error'] = "Renewal is only available for approved permits.";
    header("Location: permit-details.php?id=" . $permit_id);
    exit();
}

// Reset the permit back through review.
$stmt = $conn->prepare(
  "UPDATE permit
   SET status = 'pending',
       submitted_date = NOW(),
       reviewed_date = NULL,
       approved_date = NULL,
       reviewed_by_admin_id = NULL,
       rejection_reason = NULL
   WHERE permit_id = ? AND user_account_id = ? AND status = 'approved'");
$stmt->bind_param("ii", $permit_id, $account_id);
$stmt->execute();
$affected = $stmt->affected_rows;
$stmt->close();

if ($affected > 0) {
    $_SESSION['flash'] = "Renewal request submitted successfully. Your permit status is now Pending.";
    header("Location: permit-details.php?id=" . $permit_id);
} else {
    $_SESSION['flash_error'] = "Could not renew this permit.";
    header("Location: my-permits.php");
}
exit();
