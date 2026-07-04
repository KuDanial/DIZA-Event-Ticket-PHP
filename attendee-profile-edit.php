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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $phone_number = trim($_POST['phoneNumber']);
    $address = trim($_POST['address']);
    $gender = trim($_POST['gender']);
    $student_id = trim($_POST['studentId']);
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';

    if (!empty($new_password)) {
        if ($new_password !== $confirm_password) {
            $error = 'Passwords do not match.';
        }
    }

    if (empty($error)) {
        mysqli_begin_transaction($conn);
        try {
            if (!empty($new_password)) {
                $password_hash = md5($new_password);
                $u_query = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, gender = ?, password_hash = ? WHERE id = ?";
                $stmt_u = mysqli_prepare($conn, $u_query);
                mysqli_stmt_bind_param($stmt_u, "ssssssi", $first_name, $last_name, $phone_number, $address, $gender, $password_hash, $currentUser['id']);
            } else {
                $u_query = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, gender = ? WHERE id = ?";
                $stmt_u = mysqli_prepare($conn, $u_query);
                mysqli_stmt_bind_param($stmt_u, "sssssi", $first_name, $last_name, $phone_number, $address, $gender, $currentUser['id']);
            }
            mysqli_stmt_execute($stmt_u);

            // Update attendee details table
            $a_query = "UPDATE attendee_details SET student_id = ? WHERE user_id = ?";
            $stmt_a = mysqli_prepare($conn, $a_query);
            mysqli_stmt_bind_param($stmt_a, "si", $student_id, $currentUser['id']);
            mysqli_stmt_execute($stmt_a);

            mysqli_commit($conn);

            // Update session
            $_SESSION['user']['first_name'] = $first_name;
            $_SESSION['user']['last_name'] = $last_name;
            $_SESSION['user']['phone_number'] = $phone_number;
            $_SESSION['user']['address'] = $address;
            $_SESSION['user']['gender'] = $gender;
            if (!empty($new_password)) {
                $_SESSION['user']['password_hash'] = $password_hash;
            }

            header("Location: attendee-profile-view.php?success=1");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Failed to update attendee profile.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile | DIZA Event Ticket</title>
    <link class="style-ref" rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <div class="container single-box-container">
        <div class="main-card">
            
            <div class="card-sidebar" style="justify-content: flex-start; padding-top: 50px;">
                <div style="font-size: 50px; color: #FFC107; margin-bottom: 15px;">
                    <i class="fas fa-user-edit"></i>
                </div>
                <div class="profile-username" style="font-size: 20px;"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></div>
                <p style="color: #888; font-size: 14px; margin-top: 5px;">Edit Mode</p>
            </div>

            <div class="card-content">
                
                <div class="form-body">
                    <div class="card-header-flex">
                        <h1>Edit Information</h1>
                        <div class="action-links">
                            <a href="attendee-profile-view.php" class="cancel-link">Cancel</a>
                        </div>
                    </div>

                    <form action="attendee-profile-edit.php" method="POST">
                        <?php if (!empty($error)): ?>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-grid">
                            <div class="form-group">
                                <label>First Name</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="firstName" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Last Name</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-user input-icon"></i>
                                    <input type="text" name="lastName" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Username (Read Only)</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-id-badge input-icon"></i>
                                    <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" readonly style="background-color: #eee; cursor: not-allowed;">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Student ID / Card Number</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-id-card input-icon"></i>
                                    <input type="text" name="studentId" value="<?php echo htmlspecialchars($attProfile['student_id'] ?? ''); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-grid">
                            <div class="form-group">
                                <label>Gender</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-venus-mars input-icon"></i>
                                    <select name="gender">
                                        <option value="Male" <?php echo ($currentUser['gender'] === 'Male') ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo ($currentUser['gender'] === 'Female') ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo ($currentUser['gender'] === 'Other') ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <div class="input-icon-wrapper">
                                    <i class="fas fa-phone input-icon"></i>
                                    <input type="tel" name="phoneNumber" value="<?php echo htmlspecialchars($currentUser['phone_number']); ?>">
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Email Address</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-envelope input-icon"></i>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" readonly style="background-color: #f9f9f9;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Registered Address</label>
                            <div class="input-icon-wrapper">
                                <i class="fas fa-map-marker-alt input-icon"></i>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($currentUser['address']); ?>">
                            </div>
                        </div>

                        <div style="margin: 30px 0 20px 0; border-top: 1px solid #eee; padding-top: 20px;">
                            <h3 style="font-size: 16px; margin-bottom: 15px; color: #333;"><i class="fas fa-lock"></i> Change Password</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-key input-icon"></i>
                                        <input type="password" name="new_password" placeholder="Leave blank to keep current">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label>Confirm Password</label>
                                    <div class="input-icon-wrapper">
                                        <i class="fas fa-check-circle input-icon"></i>
                                        <input type="password" name="confirm_password" placeholder="Confirm new password">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn-bottom-update">Save Changes</button>

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