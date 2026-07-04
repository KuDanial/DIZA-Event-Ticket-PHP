<?php
require_once("connection.php");

// If already logged in, redirect
if (isset($_SESSION['user'])) {
    $role = $_SESSION['user']['role'];
    if ($role === 'admin') header("Location: admin-dashboard.php");
    elseif ($role === 'organizer') header("Location: organizer-dashboard.php");
    else header("Location: index.php");
    exit();
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $role = isset($_POST['role']) ? trim($_POST['role']) : 'attendee';
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $gender = isset($_POST['gender']) ? trim($_POST['gender']) : '';
    $address = trim($_POST['address']);
    $password = trim($_POST['password']);

    if (empty($first_name) || empty($last_name) || empty($email) || empty($username) || empty($password)) {
        header("Location: register.php?error=required");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: register.php?error=email");
        exit();
    }

    // Check if username/email already exists
    $check_query = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt = mysqli_prepare($conn, $check_query);
    mysqli_stmt_bind_param($stmt, "ss", $username, $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_fetch_assoc($result)) {
        header("Location: register.php?error=exists");
        exit();
    }

    // Insert user
    $password_hash = md5($password);
    $status = 'active';
    
    $insert_query = "INSERT INTO users (username, password_hash, first_name, last_name, email, phone_number, gender, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insert_query);
    mysqli_stmt_bind_param($stmt, "ssssssssss", $username, $password_hash, $first_name, $last_name, $email, $phone, $gender, $address, $role, $status);
    
    if (mysqli_stmt_execute($stmt)) {
        $user_id = mysqli_insert_id($conn);
        
        if ($role === 'organizer') {
            $company_name = trim($_POST['companyName'] ?? $first_name . ' Organization');
            $org_query = "INSERT INTO organizer_details (user_id, company_name, business_phone, business_email) VALUES (?, ?, ?, ?)";
            $stmt_org = mysqli_prepare($conn, $org_query);
            mysqli_stmt_bind_param($stmt_org, "isss", $user_id, $company_name, $phone, $email);
            mysqli_stmt_execute($stmt_org);
        } else {
            $stud_query = "INSERT INTO attendee_details (user_id) VALUES (?)";
            $stmt_stud = mysqli_prepare($conn, $stud_query);
            mysqli_stmt_bind_param($stmt_stud, "i", $user_id);
            mysqli_stmt_execute($stmt_stud);
        }

        header("Location: login.php?status=success");
        exit();
    } else {
        header("Location: register.php?error=generic");
        exit();
    }
}

if (isset($_GET['error'])) {
    $err = $_GET['error'];
    if ($err === 'exists') $error = 'Username or email already registered.';
    elseif ($err === 'required') $error = 'Please fill in all required fields.';
    elseif ($err === 'email') $error = 'Please enter a valid email address.';
    else $error = 'Registration failed. Please try again.';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIZA Event Ticket | Create Account</title>
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
        <div class="auth-container wide-container">
            <div class="auth-card register-card-wide">
                <div class="auth-header">
                    <h2>CREATE ACCOUNT</h2>
                    <p>Join us to book your favorite events!</p>
                </div>

                <!-- Error Messages -->
                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                    </div>
                <?php endif; ?>

                <form action="register.php" method="POST">
                    <div class="user-type-selector">
                        <span class="role-label">I am registering as:</span>
                        <div class="radio-group">
                            <label class="radio-option">
                                <input type="radio" name="role" value="attendee" checked onclick="toggleOrgFields(false)">
                                Attendee
                            </label>
                            <label class="radio-option">
                                <input type="radio" name="role" value="organizer" onclick="toggleOrgFields(true)">
                                Organizer
                            </label>
                        </div>
                    </div>

                    <!-- Organizer Specific Fields (dynamically shown) -->
                    <div class="form-group" id="orgCompanyGroup" style="display:none; margin-bottom:15px;">
                        <label for="companyName">Company Name</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-building input-icon"></i>
                            <input type="text" id="companyName" name="companyName" placeholder="Enter Company Name">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="firstName">First Name</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="firstName" name="firstName" placeholder="First Name" required>
                            </div>
                        </div>

                        <div class="form-group half-width">
                            <label for="lastName">Last Name</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-user input-icon"></i>
                                <input type="text" id="lastName" name="lastName" placeholder="Last Name" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="email">Email Address</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" id="email" name="email" placeholder="example@email.com" required>
                            </div>
                        </div>

                        <div class="form-group half-width">
                            <label for="phone">Phone Number</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-phone input-icon"></i>
                                <input type="tel" id="phone" name="phone" placeholder="012-3456789" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group half-width">
                            <label for="username">Username</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-id-badge input-icon"></i>
                                <input type="text" id="username" name="username" placeholder="Choose a username" required>
                            </div>
                        </div>

                        <div class="form-group half-width">
                            <label for="gender">Gender</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-venus-mars input-icon"></i>
                                <select id="gender" name="gender" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="address">Street Address</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-map-marker-alt input-icon"></i>
                            <input type="text" id="address" name="address" placeholder="Unit, Building, Street Name" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <div class="input-icon-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" placeholder="Create a strong password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn-auth-submit">REGISTER</button>
                </form>

                <div class="auth-footer">
                    <p>Already have an account? <a href="login.php" class="link-highlight">Login Here</a></p>
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
                    <p><b>Hotline:</b> <br> +60 16-262 4834 / +60 11-6551 3006</p>
                    <p><b>Email:</b> <br> support@diza.uitm.edu.my</p>
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
    function toggleOrgFields(show) {
        var group = document.getElementById("orgCompanyGroup");
        var input = document.getElementById("companyName");
        if (show) {
            group.style.display = "block";
            input.required = true;
        } else {
            group.style.display = "none";
            input.required = false;
        }
    }
    </script>
</body>
</html>