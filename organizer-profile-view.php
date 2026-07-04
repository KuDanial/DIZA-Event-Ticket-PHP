<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Fetch organizer profile
$org_query = "SELECT * FROM organizer_details WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $org_query);
mysqli_stmt_bind_param($stmt, "i", $currentUser['id']);
mysqli_stmt_execute($stmt);
$orgProfile = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$company_name = !empty($orgProfile['company_name']) ? $orgProfile['company_name'] : $currentUser['first_name'] . ' ' . $currentUser['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Profile | DIZA Event Ticket</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/styledashboard.css?v=2">
</head>
<body class="org-theme">
    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="brand">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZAET Logo" class="brand-logo">
                <h2>EventApp <span class="badge">ORG</span></h2>
            </div>
            
            <ul class="nav-links">
                <li><a href="organizer-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="menu-header">My Events</li>
                <li><a href="organizer-events-addNew.php"><i class="fas fa-plus-circle"></i> Create New Event</a></li>
                <li><a href="organizer-events.php"><i class="fas fa-calendar-alt"></i> My Events List</a></li>
                <li class="menu-header">Account</li>
                <li class="active"><a href="organizer-profile-view.php"><i class="fas fa-user-cog"></i> Org Profile</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>Organizer Profile</h1></div>
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
                            <img src="images/stockPFP.png" alt="Avatar">
                        </div>
                        <h2><?php echo htmlspecialchars($company_name); ?></h2>
                        <p class="role-text">Event Organizer</p>
                        <p class="email-text"><?php echo htmlspecialchars($currentUser['email']); ?></p>
                    </div>

                    <div class="card card-padded">
                        <div class="card-header">
                            <h3>Organization Details</h3>
                            <a href="organizer-profile-edit.php" class="btn-primary" style="font-size:12px; text-decoration: none;">
                                <i class="fas fa-pen"></i> Edit Profile
                            </a>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <div class="view-label">Company Name</div>
                                <div class="view-value"><?php echo htmlspecialchars($orgProfile['company_name'] ?? 'Not specified'); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Website URL</div>
                                <div class="view-value"><?php echo htmlspecialchars($orgProfile['website_url'] ?? 'Not specified'); ?></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <div class="view-label">Business Phone</div>
                                <div class="view-value"><?php echo htmlspecialchars($orgProfile['business_phone'] ?? 'Not specified'); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Business Email</div>
                                <div class="view-value"><?php echo htmlspecialchars($orgProfile['business_email'] ?? 'Not specified'); ?></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="view-label">About Organization</div>
                            <div class="view-value"><?php echo nl2br(htmlspecialchars($orgProfile['about_description'] ?? 'No description provided.')); ?></div>
                        </div>

                        <div class="form-row" style="margin-top:20px; border-top:1px solid #eee; padding-top:20px;">
                            <div class="form-group">
                                <div class="view-label">Contact Person (First Name)</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['first_name']); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Contact Person (Last Name)</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['last_name']); ?></div>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <div class="view-label">Gender</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['gender']); ?></div>
                            </div>
                            <div class="form-group">
                                <div class="view-label">Street Address</div>
                                <div class="view-value"><?php echo htmlspecialchars($currentUser['address']); ?></div>
                            </div>
                        </div>

                    </div>

                </div>
            </div>
        </main>
    </div>
</body>
</html>