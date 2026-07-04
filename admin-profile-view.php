<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Admin</title>
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
                <li><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
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
                <div class="page-title"><h1>My Profile</h1></div>
            </header>

            <div class="content-padding">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> Profile updated successfully!
                    </div>
                <?php endif; ?>
                
                <div class="profile-container">
                    
                    <div class="card profile-card card-padded">
                        <div class="profile-img-container">
                            <img src="images/stockPFP.png" alt="Admin Avatar">
                        </div>
                        <h2><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></h2>
                        <p class="role-text">System Administrator</p>
                        <p class="email-text"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    </div>

                    <div class="card card-padded">
                        <div class="card-header">
                            <h3>Personal Information</h3>
                            <a href="admin-profile-edit.php" class="btn-primary" style="font-size:12px; text-decoration: none;">
                                <i class="fas fa-pen"></i> Edit Profile
                            </a>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <div class="view-label">First Name</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['first_name']); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Last Name</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['last_name']); ?></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <div class="view-label">Username</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['username']); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Gender</div>
                                <div class="view-value"><?php echo !empty($currentUser['gender']) ? htmlspecialchars($currentUser['gender']) : 'Not specified'; ?></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <div class="view-label">Email Address</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['email']); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Phone Number</div>
                                <div class="view-value"><?php echo !empty($currentUser['phone_number']) ? htmlspecialchars($currentUser['phone_number']) : 'Not specified'; ?></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="view-label">Address</div>
                            <div class="view-value"><?php echo !empty($currentUser['address']) ? htmlspecialchars($currentUser['address']) : 'Not specified'; ?></div>
                        </div>

                        <div class="form-group">
                            <div class="view-label">Role Level</div>
                            <div class="view-value"><span class="role-badge super">Super Admin</span></div>
                        </div>

                    </div>

                </div>
            </div>
        </main>
    </div>
</body>
</html>