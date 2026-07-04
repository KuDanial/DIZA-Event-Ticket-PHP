<?php
require_once("connection.php");

// If already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') header("Location: admin-dashboard.php");
    elseif ($role === 'organizer') header("Location: organizer-dashboard.php");
    else header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if (isset($_GET['error'])) {
    $err_code = $_GET['error'];
    if ($err_code === 'invalid') $error = 'Invalid username or password.';
    elseif ($err_code === 'required') $error = 'Please fill in all required fields.';
    elseif ($err_code === 'role') $error = 'Please select a login role.';
    else $error = htmlspecialchars($_GET['error']);
}
if (isset($_GET['success'])) {
    $success = htmlspecialchars($_GET['success']);
}
if (isset($_GET['status']) && $_GET['status'] === 'success') {
    $success = 'Account created successfully! Please login.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $selected_role = isset($_POST['role']) ? trim($_POST['role']) : '';

    if (empty($username) || empty($password) || empty($selected_role)) {
        header("Location: login.php?error=required");
        exit();
    }

    $password_hash = md5($password);

    // Query DB
    $query = "SELECT * FROM users WHERE username = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $username);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($row = mysqli_fetch_assoc($result)) {
        if ($row['password_hash'] === $password_hash) {
            if ($row['role'] === $selected_role) {
                if ($row['status'] === 'banned') {
                    header("Location: login.php?error=Your+account+has+been+banned");
                    exit();
                }

                // Success! Set session
                $_SESSION['user'] = $row;
                $_SESSION['role'] = $row['role'];

                // Update last login if admin
                if ($row['role'] === 'admin') {
                    mysqli_query($conn, "INSERT INTO admin_details (user_id, last_login) VALUES ({$row['id']}, NOW()) ON DUPLICATE KEY UPDATE last_login = NOW()");
                }

                if ($row['role'] === 'admin') header("Location: admin-dashboard.php");
                elseif ($row['role'] === 'organizer') header("Location: organizer-dashboard.php");
                else header("Location: index.php");
                exit();
            } else {
                header("Location: login.php?error=Incorrect+role+selected");
                exit();
            }
        } else {
            header("Location: login.php?error=invalid");
            exit();
        }
    } else {
        header("Location: login.php?error=invalid");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIZA Event Ticket | Login</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="navbar">
        <div class="container nav-container">
            <a href="index.php" class="logo">
                <img src="images/DIZAET_Logo.png" alt="DIZA Event Ticket">
            </a>
            <nav class="nav-links">
                <a href="index.php">Home</a>
                <a href="events.php">Events</a>
                <a href="about.php">About Us</a>
                <a href="help.php">Help</a>
            </nav>
            <div class="auth-buttons">
                <a href="login.php" class="btn-login">Log In</a>
                <a href="register.php" class="btn-register">Register</a>
            </div>
        </div>
    </header>

    <section class="auth-section">
        <div class="auth-container">
            <div class="auth-card">
                <div class="auth-header">
                    <h2>WELCOME BACK</h2>
                    <p>Please log in to access your dashboard.</p>
                </div>

                <!-- Error/Success Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                <?php if ($success): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                    </div>
                <?php endif; ?>

                <form action="login.php" method="POST">
                    <div class="user-type-selector">
                        <label class="role-label">Login As:</label>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="role" value="attendee" checked>
                                <span>Attendee</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="role" value="organizer">
                                <span>Organizer</span>
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="role" value="admin">
                                <span>Admin</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="username">Username</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="username" name="username" placeholder="Enter Username" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-icon-wrapper password-wrapper">
                            <i class="fas fa-lock input-icon"></i> 
                            <input type="password" id="password" name="password" placeholder="Enter Password" required>
                            <i class="fas fa-eye toggle-password" onclick="togglePasswordVisibility()"></i> 
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="help.php" class="forgot-password">Forgot Password?</a>
                    </div>

                    <button type="submit" class="btn-auth-submit">LOGIN</button>
                </form>

                <div class="auth-footer">
                    <p>Don't have an account? <a href="register.php" class="link-highlight">Create New Account</a></p>
                </div>
            </div>
        </div>
    </section>

    <footer>
        <div class="container footer-content">
            <div class="footer-col brand-col">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZA Event Ticket" class="footer-logo">
                <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 8px;"> In Collaboration With </p>
                <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="images/LOGO UiTM OUTLINE 3 (WHITE).png" alt="UiTM" style="height: 40px; opacity: 0.8;">
                        <span style="color:#555;">|</span>
                        <img src="images/Google-Gemini-White-Logo.png" alt="Gemini" style="height: 40px; opacity: 0.8;">
                </div>
                <p>Your one-stop platform for all event ticketing needs.</p>
            </div>
            <div class="footer-col contact-col">
                <h4>Contact Us</h4>
                <div class="contact-details">
                    <p><b>Hotline:</b> <br>+60 16-262 4834 / +60 11-6551 3006</p>
                    <p><b>Email:</b> <br>support@diza.uitm.edu.my</p>
                </div>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul class="footer-links-list">
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="help.php">Contact</a></li>
                    <li><a href="help.php">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Follow Us</h4>
                <div class="footer-social">
                    <a href="https://www.facebook.com/modannie"><i class="fab fa-facebook"></i></a>
                    <a href="https://x.com/modannyyy"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/tadanny._"><i class="fab fa-instagram"></i></a>
                    <a href="https://discord.gg/95K89KDhTm"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 DIZAZ Group. All Rights Reserved.</p>
        </div>
    </footer>

    <script>
    function togglePasswordVisibility() {
        var pwd = document.getElementById("password");
        if (pwd.type === "password") {
            pwd.type = "text";
        } else {
            pwd.type = "password";
        }
    }
    </script>
</body>
</html>