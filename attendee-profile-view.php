<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'attendee') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Fetch attendee profile
$att_query = "SELECT * FROM attendee_details WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $att_query);
mysqli_stmt_bind_param($stmt, "i", $currentUser['id']);
mysqli_stmt_execute($stmt);
$attProfile = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | DIZA Event Ticket</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <div class="container single-box-container">
        <div class="main-card">
            
            <div class="card-sidebar" style="justify-content: flex-start; padding-top: 50px;">
                <div style="font-size: 50px; color: #FFC107; margin-bottom: 15px;">
                    <i class="fas fa-user-circle"></i>
                </div>
                <div class="profile-username" style="font-size: 20px;"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                <div style="color: #888; font-size: 14px; margin-top: 5px; background: #eee; padding: 4px 12px; border-radius: 15px;">Attendee</div>
            </div>

            <div class="card-content">
                <div class="form-body">
                    <div class="card-header-flex">
                        <h1>My Profile</h1>
                        <div class="action-links">
                            <a href="attendee-profile-edit.php" class="update-link"><i class="fas fa-pen"></i> Edit Profile</a>
                        </div>
                    </div>

                    <?php if (isset($_GET['success'])): ?>
                        <div class="alert alert-success" style="margin-bottom: 20px;">
                            <i class="fas fa-check-circle"></i> Profile updated successfully!
                        </div>
                    <?php endif; ?>

                    <form>
                        <div class="form-grid">
                            <div class="form-group">
                                <label>First Name</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Username</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-id-badge input-icon"></i>
                                    <input type="text" value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Student ID / Card Number</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-id-card input-icon"></i>
                                    <input type="text" value="<?php echo htmlspecialchars($attProfile['student_id'] ?? 'Not specified'); ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Gender</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-venus-mars input-icon"></i>
                                    <input type="text" value="<?php echo !empty($currentUser['gender']) ? htmlspecialchars($currentUser['gender']) : 'Not specified'; ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="text" value="<?php echo !empty($currentUser['phone_number']) ? htmlspecialchars($currentUser['phone_number']) : 'Not specified'; ?>" readonly style="background-color: #f9f9f9;">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="text" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly style="background-color: #f9f9f9;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Password</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-lock input-icon"></i>
                                <input type="password" value="********" readonly style="background-color: #f9f9f9;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Registered Address</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input type="text" value="<?php echo !empty($currentUser['address']) ? htmlspecialchars($currentUser['address']) : 'Not specified'; ?>" readonly style="background-color: #f9f9f9;">
                            </div>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

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

</body>
</html>