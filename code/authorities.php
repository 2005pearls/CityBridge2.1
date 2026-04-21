<?php
include "db.php";
include "auth.php";

require_login("user");

// Group authorities by category
$categories = ['labor','equipment','medical','electronic'];
$authorities = [];
foreach ($categories as $c) $authorities[$c] = [];

$res = $conn->query("SELECT authority_name, category, website FROM authority ORDER BY authority_name");
while ($row = $res->fetch_assoc()) {
    if (isset($authorities[$row['category']])) {
        $authorities[$row['category']][] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<title>Authorities - CityBridge</title>
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
    <a href="safety-guidelines.php">Safety Guidelines</a>
    <a href="authorities.php" class="active">View Authorities</a>
    <a href="logout.php" class="logout">Log Out</a>
  </nav>
</header>

<main>

  <div class="guidelines-hero">
    <div class="guidelines-hero-inner">
      <h1>Authorities <span class="hero-accent">&amp; Resources</span></h1>
      <p>Authorized companies providing labor, equipment, medical, and electronic resources for smart city projects.</p>
    </div>
  </div>

  <div class="permit-tabs">
    <button class="permit-tab active" onclick="show('labor', this)">Labor</button>
    <button class="permit-tab" onclick="show('equipment', this)">Construction</button>
    <button class="permit-tab" onclick="show('medical', this)">Medical Devices</button>
    <button class="permit-tab" onclick="show('electronic', this)">Electronic Devices</button>
  </div>

  <div class="guidelines-body">
    <?php foreach ($categories as $idx => $cat): ?>
      <div id="panel-<?php echo $cat; ?>" class="permit-panel <?php echo $idx === 0 ? 'active' : ''; ?>">
        <p class="section-label">
          <?php
            $labels = [
              'labor'      => 'Authorized Labor Companies',
              'equipment'  => 'Authorized Construction Equipment Providers',
              'medical'    => 'Authorized Medical Device Suppliers',
              'electronic' => 'Authorized Electronic Device Suppliers',
            ];
            echo e($labels[$cat]);
          ?>
        </p>
        <div class="company-list">
        <?php if (empty($authorities[$cat])): ?>
          <p style="padding:20px;">No authorities listed yet.</p>
        <?php else: foreach ($authorities[$cat] as $a): ?>
          <div class="company-card">
            <div>
              <h4><?php echo e($a['authority_name']); ?></h4>
              <p>Official authorized provider in the <?php echo e($cat); ?> category.</p>
            </div>
            <a href="<?php echo e($a['website']); ?>" class="card-link" target="_blank" rel="noopener">Visit Website →</a>
          </div>
        <?php endforeach; endif; ?>
        </div>
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
