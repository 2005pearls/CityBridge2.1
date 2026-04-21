<?php
session_start();
include "db.php";
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $company_name = trim($_POST["company_name"]);
    $industry = trim($_POST["industry"]);
    $first_name = trim($_POST["first_name"]);
    $last_name = trim($_POST["last_name"]);
    $job_title = trim($_POST["job_title"]);
    $email = trim($_POST["email"]);
    $phone = trim($_POST["phone"]);
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $confirm_password = trim($_POST["confirm_password"]);

    if (
        empty($company_name) || empty($industry) || empty($first_name) || empty($last_name) ||
        empty($email) || empty($phone) || empty($username) || empty($password) || empty($confirm_password)
    ) {
        $error = "Please fill in all required fields.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        try {
            $checkSql = "SELECT account_id FROM account WHERE email = ? OR username = ?";
            $checkStmt = $conn->prepare($checkSql);
            $checkStmt->bind_param("ss", $email, $username);
            $checkStmt->execute();
            $checkResult = $checkStmt->get_result();

            if ($checkResult->num_rows > 0) {
                $error = "Email or username already exists.";
            } else {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                $companySql = "INSERT INTO company (company_name, sector) VALUES (?, ?)";
                $companyStmt = $conn->prepare($companySql);
                $companyStmt->bind_param("ss", $company_name, $industry);
                $companyStmt->execute();
                $company_id = $conn->insert_id;

                $accountSql = "INSERT INTO account (username, email, password_hash, role) VALUES (?, ?, ?, 'user')";
                $accountStmt = $conn->prepare($accountSql);
                $accountStmt->bind_param("sss", $username, $email, $hashedPassword);
                $accountStmt->execute();
                $account_id = $conn->insert_id;

                $userSql = "INSERT INTO user_account (account_id, first_name, last_name, phone_number, job_title, company_id)
                            VALUES (?, ?, ?, ?, ?, ?)";
                $userStmt = $conn->prepare($userSql);
                $userStmt->bind_param("issssi", $account_id, $first_name, $last_name, $phone, $job_title, $company_id);
                $userStmt->execute();

                $_SESSION["account_id"] = $account_id;
                $_SESSION["role"] = "user";
                $_SESSION["email"] = $email;
                $_SESSION["username"] = $username;

                header("Location: user.php");
                exit();
            }
        } catch (Exception $e) {
            $error = $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sign Up - CityBridge</title>
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
    <span class="current">Sign Up</span>
  </nav>

  <main>
    <div class="form-card wide">

      <h2>Register Your Organization</h2>
      <p class="subtitle">Create a CityBridge account to start submitting entry permits</p>

      <?php if (!empty($error)) { ?>
        <p style="color:red; text-align:center; margin-bottom:15px;">
          <?php echo htmlspecialchars($error); ?>
        </p>
      <?php } ?>

      <form id="signupForm" action="" method="post" novalidate>

        <p class="section-label">Company Information</p>

        <div class="field">
          <label for="company">Company Name</label>
          <input type="text" id="company" name="company_name" placeholder="e.g. Al-Rashidi Construction Co."
                 value="<?php echo isset($_POST['company_name']) ? htmlspecialchars($_POST['company_name']) : ''; ?>" />
        </div>

        <div class="field">
          <label for="industry">Industry / Sector</label>
          <select id="industry" name="industry">
            <option value="" disabled <?php echo empty($_POST['industry']) ? 'selected' : ''; ?>>Select sector</option>
            <option value="construction" <?php echo (isset($_POST['industry']) && $_POST['industry'] == 'construction') ? 'selected' : ''; ?>>Construction</option>
            <option value="healthcare" <?php echo (isset($_POST['industry']) && $_POST['industry'] == 'healthcare') ? 'selected' : ''; ?>>Healthcare</option>
            <option value="telecommunications" <?php echo (isset($_POST['industry']) && $_POST['industry'] == 'telecommunications') ? 'selected' : ''; ?>>Telecommunications</option>
            <option value="logistics" <?php echo (isset($_POST['industry']) && $_POST['industry'] == 'logistics') ? 'selected' : ''; ?>>Logistics &amp; Transport</option>
            <option value="energy" <?php echo (isset($_POST['industry']) && $_POST['industry'] == 'energy') ? 'selected' : ''; ?>>Energy &amp; Utilities</option>
            <option value="other" <?php echo (isset($_POST['industry']) && $_POST['industry'] == 'other') ? 'selected' : ''; ?>>Other</option>
          </select>
        </div>

        <p class="section-label">Account Representative</p>

        <div class="two-col">
          <div class="field">
            <label for="firstName">First Name</label>
            <input type="text" id="firstName" name="first_name" placeholder="First name"
                   value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>" />
          </div>
          <div class="field">
            <label for="lastName">Last Name</label>
            <input type="text" id="lastName" name="last_name" placeholder="Last name"
                   value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>" />
          </div>
        </div>

        <div class="field">
          <label for="jobTitle">Job Title</label>
          <input type="text" id="jobTitle" name="job_title" placeholder="e.g. Operations Manager"
                 value="<?php echo isset($_POST['job_title']) ? htmlspecialchars($_POST['job_title']) : ''; ?>" />
        </div>

        <div class="two-col">
          <div class="field">
            <label for="email">Work Email</label>
            <input type="email" id="email" name="email" placeholder="name@company.com"
                   value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>" />
          </div>
          <div class="field">
            <label for="phone">Phone Number</label>
            <input type="tel" id="phone" name="phone" placeholder="+966 5X XXX XXXX"
                   value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>" />
          </div>
        </div>

        <p class="section-label">Login Credentials</p>

        <div class="field">
          <label for="username">Username</label>
          <input type="text" id="username" name="username" placeholder="Choose a username"
                 value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>" />
        </div>

        <div class="two-col">
          <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" placeholder="Min. 8 characters" />
          </div>
          <div class="field">
            <label for="confirmPassword">Confirm Password</label>
            <input type="password" id="confirmPassword" name="confirm_password" placeholder="Repeat password" />
          </div>
        </div>

        <p class="hint">Must include uppercase, lowercase, a number, and a special character.</p>

        <button type="submit" class="btn btn-primary btn-full">Create Account</button>
      </form>

      <div class="form-footer">
        Already have an account? <a href="login.php">Log in</a>
      </div>

    </div>
  </main>

  <footer>
    <p>&copy; 2026 CityBridge. Smart cities, seamless access.</p>
    <div class="social-links">
      <a href="https://instagram.com/citybridge" aria-label="Instagram">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
          <path d="M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm5 5a5 5 0 100 10 5 5 0 000-10zm6-.8a1 1 0 100 2 1 1 0 000-2z"/>
        </svg>
      </a>
      <a href="https://twitter.com/citybridge" aria-label="X">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
          <path d="M19.6 2.3h2.4l-5.2 6 6.1 8h-4.8l-3.6-4.8-4.1 4.8H3.8l5.6-6.4L3.6 2.3H8l3.2 4.4z"/>
        </svg>
      </a>
      <a href="#" aria-label="Email">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
          <path d="M4 6h16a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V8a2 2 0 012-2zm0 2l8 5 8-5"/>
        </svg>
      </a>
      <a href="tel:+966500000000" aria-label="Phone">
        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 24 24">
          <path d="M6.6 10.8a15 15 0 006.6 6.6l2.2-2.2a1 1 0 011.1-.2c1.2.5 2.5.8 3.9.8a1 1 0 011 1V20a1 1 0 01-1 1C12.4 21 3 11.6 3 1a1 1 0 011-1h3.2a1 1 0 011 1c0 1.4.3 2.7.8 3.9.1.4 0 .8-.2 1.1l-2.2 2.2z"/>
        </svg>
      </a>
    </div>
  </footer>
</body>
</html>