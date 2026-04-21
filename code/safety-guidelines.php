<?php
include "db.php";
include "auth.php";

require_login("user");

// Group guidelines by category
$categories = ['general','labor','equipment','medical','electronic'];
$rules = [];
foreach ($categories as $c) $rules[$c] = [];

$res = $conn->query("SELECT category, level, rule_label, rule_text FROM safety_guideline ORDER BY guideline_id");
while ($row = $res->fetch_assoc()) {
    if (isset($rules[$row['category']])) {
        $rules[$row['category']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Safety Guidelines - CityBridge</title>
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
    <a href="my-permits.php">My Permits</a>
    <a href="reqprimt.php">Request a Permit</a>
    <a href="safety-guidelines.php" class="active">Safety Guidelines</a>
    <a href="authorities.php">View Authorities</a>
    <a href="logout.php" class="logout">Log Out</a>
  </nav>
</header>

<main>

  <div class="guidelines-hero">
    <div class="guidelines-hero-inner">
      <h1>Safety Guidelines <span class="hero-accent">&amp; Requirements</span></h1>
      <p>Review all rules before submitting your application. Rules are divided into general rules that apply to all permits, and specific rules per permit type.</p>
    </div>
  </div>

  <div class="permit-tabs">
    <button class="permit-tab active" onclick="show('general', this)">All Permits</button>
    <button class="permit-tab" onclick="show('labor', this)">Labor</button>
    <button class="permit-tab" onclick="show('equipment', this)">Construction Equipment</button>
    <button class="permit-tab" onclick="show('medical', this)">Medical Device</button>
    <button class="permit-tab" onclick="show('electronic', this)">Electronic Device</button>
  </div>

  <div class="guidelines-body">
    <?php
      $titles = [
        'general'    => 'General Rules — Apply to All Permit Types',
        'labor'      => 'Labor Permit — Specific Rules',
        'equipment'  => 'Construction Equipment Permit — Specific Rules',
        'medical'    => 'Medical Device Permit — Specific Rules',
        'electronic' => 'Electronic Device Permit — Specific Rules',
      ];
      foreach ($categories as $idx => $cat):
    ?>
      <div id="panel-<?php echo $cat; ?>" class="permit-panel <?php echo $idx === 0 ? 'active' : ''; ?>">
        <p class="section-label"><?php echo e($titles[$cat]); ?></p>
        <table class="rules-table">
          <thead>
            <tr>
              <th style="width:120px">Category</th>
              <th style="width:110px">Level</th>
              <th>Rule</th>
            </tr>
          </thead>
          <tbody>
          <?php if (empty($rules[$cat])): ?>
            <tr><td colspan="3" style="text-align:center;">No rules in this category.</td></tr>
          <?php else: foreach ($rules[$cat] as $r): ?>
            <tr>
              <td><?php echo e($r['rule_label']); ?></td>
              <td><span class="rule-badge <?php echo e($r['level']); ?>"><?php echo e(ucfirst($r['level'])); ?></span></td>
              <td><?php echo e($r['rule_text']); ?></td>
            </tr>
          <?php endforeach; endif; ?>
          </tbody>
        </table>
      </div>
    <?php endforeach; ?>
  </div>

</main>

<footer><p>&copy; 2026 CityBridge. Smart cities, seamless access.</p></footer>

<script>
function show(key, btn) {
  document.querySelectorAll('.permit-panel').forEach(p => p.classList.remove('active'));
  document.querySelectorAll('.permit-tab').forEach(b => b.classList.remove('active'));
  document.getElementById('panel-' + key).classList.add('active');
  btn.classList.add('active');
}
</script>
<script src="script.js"></script>
</body>
</html>
