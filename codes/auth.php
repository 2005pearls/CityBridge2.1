<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Require a logged-in user. Optionally require a specific role ('user' or 'admin')
 * If the check fails, redirect to login and stop.
 */
function require_login($required_role = null) {
    // Prevent caching of protected pages so the browser "Back" button
    header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
    header("Pragma: no-cache");
    header("Expires: 0");

    if (!isset($_SESSION["account_id"])) {
        header("Location: login.php");
        exit();
    }
    if ($required_role !== null && ($_SESSION["role"] ?? "") !== $required_role) {
        // Wrong role — send them to their own dashboard
        if (($_SESSION["role"] ?? "") === "admin") {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit();
    }
}

/** Format a DATE/DATETIME string as "Mar 5, 2026". Returns "—" if empty */
function fmt_date($d) {
    if (!$d) return "—";
    $t = strtotime($d);
    return $t ? date("M j, Y", $t) : "—";
}

/** Human-readable permit type label */
function type_label($t) {
    switch ($t) {
        case 'labor':      return 'Labor Permit';
        case 'equipment':  return 'Construction Equipment Permit';
        case 'medical':    return 'Medical Device Permit';
        case 'electronic': return 'Electronic Device Permit';
        default:           return ucfirst($t) . ' Permit';
    }
}

/** Short "#CB-0001" style permit id */
function permit_code($id) {
    return "#CB-" . str_pad((string)$id, 4, "0", STR_PAD_LEFT);
}

function e($v) {
    return htmlspecialchars((string)($v ?? ""), ENT_QUOTES, "UTF-8");
}

/**
 * Output body tag attributes that JS reads to display toast messages.
 * Pulls $_SESSION['flash'] and $_SESSION['flash_error'] and clears them.
 * Usage in HTML: <body <?php echo flash_attrs(); ?>>
 */
function flash_attrs() {
    $out = "";
    if (!empty($_SESSION['flash'])) {
        $out .= ' data-flash="' . e($_SESSION['flash']) . '" data-flash-type="success"';
        unset($_SESSION['flash']);
    }
    if (!empty($_SESSION['flash_error'])) {
        $out .= ' data-flash-error="' . e($_SESSION['flash_error']) . '"';
        unset($_SESSION['flash_error']);
    }
    return $out;
}
