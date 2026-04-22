<?php
// Backwards-compat shim. The permit details page is now unified.
// Forward everything (including ?id=...) to permit-details.php
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
header("Location: permit-details.php" . ($id ? "?id={$id}" : ""));
exit();
