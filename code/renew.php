<?php
include "db.php";
include "auth.php";

require_login("user");

$account_id = (int)$_SESSION["account_id"];
$permit_id  = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Only allow renewing an approved permit owned by this user.
$stmt = $conn->prepare(
  "UPDATE permit
   SET expiry_date = DATE_ADD(
         GREATEST(COALESCE(expiry_date, CURDATE()), CURDATE()),
         INTERVAL 12 MONTH)
   WHERE permit_id = ? AND user_account_id = ? AND status = 'approved'");
$stmt->bind_param("ii", $permit_id, $account_id);
$stmt->execute();
$stmt->close();

header("Location: permit-details-approved.php?id=" . $permit_id);
exit();
