<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-organizers.php");
    exit();
}
$orgId = (int)$_GET['id'];
$query = "SELECT u.*, o.company_name, o.business_phone, o.business_email, o.website_url, o.about_description 
          FROM users u 
          LEFT JOIN organizer_details o ON u.id = o.user_id 
          WHERE u.id = ? AND u.role = 'organizer' LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $orgId);
mysqli_stmt_execute($stmt);
$org = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$org) {
    header("Location: manage-organizers.php?error=Organizer+not+found");
    exit();
}

// Get event count
$evt_res = mysqli_query($conn, "SELECT COUNT(*) as total FROM events WHERE organizer_id = $orgId");
$eventCount = mysqli_fetch_assoc($evt_res)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Organizer Details</title>
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
                <li class="active"><a href="manage-organizers.php"><i class="fas fa-briefcase"></i> Manage Organizers</a></li>
                <li><a href="manage-events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                <li class="menu-header">System</li>
                <li><a href="manage-admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>Organizer Details</h1></div>
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api?name=<?php echo urlencode($currentUser['first_name'] . '+' . $currentUser['last_name']); ?>" alt="Admin" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="dropdown-header">
                            <p>Logged in as Admin</p>
                        </div>
                        <a href="admin-profile-view.php"><i class="fas fa-user-cog"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </header>

            <div class="content-padding">
                <div class="card card-padded">
                    <div class="card-header">
                        <h3>Profile Overview</h3>
                        <a href="manage-organizers.php" class="btn-cancel" style="font-size:12px;">&larr; Back to List</a>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">User ID</div>
                            <div class="view-value">#ORG-<?php echo $org['id']; ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Account Status</div>
                            <div class="view-value"><span class="status <?php echo $org['status'] == 'active' ? 'active' : 'banned'; ?>"><?php echo htmlspecialchars($org['status']); ?></span></div>
                        </div>
                    </div>

                    <div class="form-section-title" style="margin-top: 10px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Personal Info</div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">First Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['first_name']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Last Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['last_name']); ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Username</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['username']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Email</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['email']); ?></div>
                        </div>
                    </div>

                    <div class="form-section-title" style="margin-top: 10px; margin-bottom: 20px; border-bottom: 1px solid #eee; padding-bottom: 5px;">Organization Info</div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Company Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['company_name'] ?? 'Not set'); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Business Phone</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['business_phone'] ?? 'Not set'); ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Business Email</div>
                            <div class="view-value"><?php echo htmlspecialchars($org['business_email'] ?? 'Not set'); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Website</div>
                            <div class="view-value">
                                <?php if (!empty($org['website_url'])): ?>
                                    <a href="<?php echo htmlspecialchars($org['website_url']); ?>" target="_blank" style="color:#3498db;"><?php echo htmlspecialchars($org['website_url']); ?></a>
                                <?php else: ?>
                                    Not set
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">About Description</div>
                        <div class="view-value"><?php echo !empty($org['about_description']) ? nl2br(htmlspecialchars($org['about_description'])) : 'Not set'; ?></div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">Performance</div>
                        <div class="view-value">
                            <span class="stat-badge"><i class="fas fa-calendar-check"></i> <?php echo $eventCount; ?> Event<?php echo $eventCount != 1 ? 's' : ''; ?> Created</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="manage-organizers-edit.php?id=<?php echo $org['id']; ?>" class="btn-primary">
                            <i class="fas fa-pen"></i> Edit Organizer
                        </a>
                    </div>

                </div>
            </div>
        </main>
    </div>
    <script>
        function toggleDropdown() {
            document.getElementById("userDropdown").classList.toggle("show");
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
