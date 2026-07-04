<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = isset($_POST['action']) ? $_POST['action'] : '';
    
    if ($action === 'updateAdminProfile') {
        $first_name = trim($_POST['firstName']);
        $last_name = trim($_POST['lastName']);
        $phone_number = trim($_POST['phoneNumber']);
        $address = trim($_POST['address']);
        $gender = trim($_POST['gender']);

        // Update DB
        $query = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, gender = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "sssssi", $first_name, $last_name, $phone_number, $address, $gender, $currentUser['id']);
        
        if (mysqli_stmt_execute($stmt)) {
            // Update session
            $_SESSION['user']['first_name'] = $first_name;
            $_SESSION['user']['last_name'] = $last_name;
            $_SESSION['user']['phone_number'] = $phone_number;
            $_SESSION['user']['address'] = $address;
            $_SESSION['user']['gender'] = $gender;
            
            // Re-fetch current user
            $currentUser = $_SESSION['user'];
            $success = 'Profile updated successfully!';
        } else {
            $error = 'Failed to update database profile.';
        }
    } elseif ($action === 'updateAdminPassword') {
        $currentPassword = $_POST['currentPassword'];
        $newPassword = $_POST['newPassword'];
        $confirmPassword = $_POST['confirmPassword'];

        // Get current password hash from DB
        $pwd_query = "SELECT password_hash FROM users WHERE id = ? LIMIT 1";
        $stmt_pwd = mysqli_prepare($conn, $pwd_query);
        mysqli_stmt_bind_param($stmt_pwd, "i", $currentUser['id']);
        mysqli_stmt_execute($stmt_pwd);
        $res_pwd = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_pwd));

        if (!$res_pwd || md5($currentPassword) !== $res_pwd['password_hash']) {
            $error = 'Current password is incorrect.';
        } elseif ($newPassword !== $confirmPassword) {
            $error = 'New password and confirm password do not match.';
        } elseif (empty($newPassword)) {
            $error = 'New password cannot be empty.';
        } else {
            $new_hash = md5($newPassword);
            $up_query = "UPDATE users SET password_hash = ? WHERE id = ?";
            $stmt_up = mysqli_prepare($conn, $up_query);
            mysqli_stmt_bind_param($stmt_up, "si", $new_hash, $currentUser['id']);
            if (mysqli_stmt_execute($stmt_up)) {
                $success = 'Password updated successfully!';
            } else {
                $error = 'Failed to update password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin Panel</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/styledashboard.css?v=2">
</head>
<body class="admin-theme">

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="brand">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZAET Logo" class="brand-logo">
                <h2>EventApp <span class="badge">ADMIN</span></h2>
            </div>
            
            <ul class="nav-links">
                <li>
                    <a href="admin-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                
                <li class="menu-header">User Management</li>
                <li><a href="manage-attendees.php"><i class="fas fa-user"></i> Manage Attendees</a></li>
                <li><a href="manage-organizers.php"><i class="fas fa-briefcase"></i> Manage Organizers</a></li>
                <li><a href="manage-events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>

                <li class="menu-header">System</li>
                <li><a href="manage-admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
            </ul>
        </aside>

        <main class="main-content">
            
            <header class="top-bar">
                <div class="page-title">
                    <h1>My Profile</h1>
                </div>

                <div class="user-menu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api?name=<?php echo urlencode($currentUser['first_name'] . '+' . $currentUser['last_name']); ?>" alt="Admin" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    
                    <div class="dropdown-content" id="adminDropdown">
                        <div class="dropdown-header">
                            <p>Logged in as Admin</p>
                        </div>
                        <a href="admin-profile-view.php"><i class="fas fa-user-cog"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </header>

            <div class="profile-container">
                
                <div class="profile-left">
                    <div class="card profile-card card-padded">
                        <div class="profile-img-container">
                            <img src="images/stockPFP.png" alt="Admin Avatar">
                        </div>
                        <h2><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h2>
                        <p class="role-text">System Administrator</p>
                        <p class="email-text"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                        <div class="profile-meta">
                            <span><i class="fas fa-clock"></i> Joined: Jan 2023</span>
                        </div>
                    </div>
                </div>

                <div class="profile-right">
                    
                    <div class="card card-padded">
                        <div class="card-header">
                            <h3>General Information</h3>
                            <a href="admin-profile-view.php" class="btn-cancel" style="font-size:12px;">&larr; Back to Profile</a>
                        </div>
                        <form action="admin-profile-edit.php" method="POST" class="admin-form">
                            <input type="hidden" name="action" value="updateAdminProfile">
                            
                            <?php if ($error && $action === 'updateAdminProfile'): ?>
                                <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success && $action === 'updateAdminProfile'): ?>
                                <div style="background-color: #d4edda; color: #155724; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>First Name</label>
                                    <input type="text" name="firstName" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Last Name</label>
                                    <input type="text" name="lastName" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" class="form-input" required>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>Email Address</label>
                                <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" class="form-input" readonly style="background-color: #f9f9f9;">
                            </div>
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phoneNumber" value="<?php echo htmlspecialchars($currentUser['phone_number']); ?>" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($currentUser['address']); ?>" class="form-input">
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Gender</label>
                                    <select name="gender" class="form-select">
                                        <option value="">Not specified</option>
                                        <option value="Male" <?php echo $currentUser['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                        <option value="Female" <?php echo $currentUser['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                        <option value="Other" <?php echo $currentUser['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary">Save Changes</button>
                            </div>
                        </form>
                    </div>

                    <div class="card card-padded" style="margin-top: 25px;">
                        <div class="card-header">
                            <h3>Security Settings</h3>
                        </div>
                        <form action="admin-profile-edit.php" method="POST" class="admin-form">
                            <input type="hidden" name="action" value="updateAdminPassword">
                            
                            <?php if ($error && $action === 'updateAdminPassword'): ?>
                                <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($success && $action === 'updateAdminPassword'): ?>
                                <div style="background-color: #d4edda; color: #155724; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label>Current Password</label>
                                <input type="password" name="currentPassword" placeholder="Enter current password" class="form-input" required>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label>New Password</label>
                                    <input type="password" name="newPassword" placeholder="Enter new password" class="form-input" required>
                                </div>
                                <div class="form-group">
                                    <label>Confirm New Password</label>
                                    <input type="password" name="confirmPassword" placeholder="Confirm new password" class="form-input" required>
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn-primary outline">Update Password</button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>

        </main>
    </div>

    <script>
        function toggleDropdown() {
            document.getElementById("adminDropdown").classList.toggle("show");
        }
        window.onclick = function(event) {
            if (!event.target.closest('.user-btn')) {
                var dropdowns = document.getElementsByClassName("dropdown-content");
                for (var i = 0; i < dropdowns.length; i++) {
                    var openDropdown = dropdowns[i];
                    if (openDropdown.classList.contains('show')) {
                        openDropdown.classList.remove('show');
                    }
                }
            }
        }
    </script>
</body>
</html>