<?php
session_start();
include "db.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $userPassword = trim($_POST["password"]);

    $sql = "SELECT * FROM account WHERE email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();

    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();

        if (password_verify($userPassword, $row["password_hash"])) {
            $_SESSION["account_id"] = $row["account_id"];
            $_SESSION["role"]       = $row["role"];
            $_SESSION["email"]      = $row["email"];
            $_SESSION["username"]   = $row["username"];

            if ($row["role"] === "admin") {
                header("Location: admin.php");
                exit();
            } else {
                header("Location: user.php");
                exit();
            }
        } else {
            $error = "Incorrect password.";
        }
    } else {
        $error = "No account found with this email.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login - CityBridge</title>
  <link rel="stylesheet" href="style.css" />
</head>
<body>

  <header>
    <svg viewBox="0 0 500 72" width="320" xmlns="http://www.w3.org/2000/svg">
      <text x="0" y="44" font-family="'Helvetica Neue', 'Segoe UI', Arial, sans-serif" font-size="42" font-weight="700" letter-spacing="-1">
        <tspan fill="#e8f0fb">City</tspan><tspan fill="#4baee8">Bridge</tspan>
      </text>
      <line x1="0" y1="52" x2="220" y2="52" stroke="#4baee8" stroke-width="1.6" opacity="0.4" />
      <text x="0" y="67" font-family="'DM Sans', 'Helvetica Neue', Arial, sans-serif" font-size="10.5" font-weight="300" fill="#7dcef8" letter-spacing="4" opacity="0.82">CONNECTING COMMUNITIES</text>
    </svg>
  </header>

  <nav class="breadcrumb">
    <a href="Home.html">Home</a>
    <span class="sep">›</span>
    <span class="current">Login</span>
  </nav>

  <main>
    <div class="form-card">
      <h2>Welcome Back</h2>
      <p class="subtitle">Log in to your CityBridge account</p>

      <?php if (!empty($error)) { ?>
        <p style="color:red; text-align:center; margin-bottom:15px;"><?php echo htmlspecialchars($error); ?></p>
      <?php } ?>

      <form id="loginForm" action="" method="post" novalidate>
        <div class="field">
          <label for="email">Email Address</label>
          <input type="email" id="email" name="email" placeholder="name@example.com" required />
        </div>

        <div class="field">
          <label for="password">Password</label>
          <input type="password" id="password" name="password" placeholder="Enter your password" required />
        </div>

        <div class="login-buttons">
          <button type="submit" class="btn btn-primary btn-full">Login</button>
        </div>
      </form>

      <div class="form-footer">
        Don't have an account? <a href="signup.php">Sign Up</a>
      </div>
    </div>
  </main>

  <footer>
    <p>&copy; 2026 CityBridge. Smart cities, seamless access.</p>
  </footer>
<script src="script.js"></script>
</body>
</html>
