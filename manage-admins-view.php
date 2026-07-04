<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-admins.php");
    exit();
}
$adminId = (int)$_GET['id'];
$query = "SELECT * FROM users WHERE id = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$admin = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$admin) {
    header("Location: manage-admins.php?error=Admin+not+found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Admin Details</title>
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
                <li class="active"><a href="manage-admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>Admin Details</h1></div>
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
                        <a href="manage-admins.php" class="btn-cancel" style="font-size:12px;">&larr; Back to List</a>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Admin ID</div>
                            <div class="view-value">#ADM-<?php echo $admin['id']; ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">First Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($admin['first_name']); ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Last Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($admin['last_name']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Username</div>
                            <div class="view-value"><?php echo htmlspecialchars($admin['username']); ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Email Address</div>
                            <div class="view-value"><?php echo htmlspecialchars($admin['email']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Phone Number</div>
                            <div class="view-value"><?php echo !empty($admin['phone_number']) ? htmlspecialchars($admin['phone_number']) : 'N/A'; ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Gender</div>
                            <div class="view-value"><?php echo !empty($admin['gender']) ? htmlspecialchars($admin['gender']) : 'N/A'; ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Address</div>
                            <div class="view-value"><?php echo !empty($admin['address']) ? htmlspecialchars($admin['address']) : 'N/A'; ?></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">Status</div>
                        <div class="view-value"><span class="status <?php echo $admin['status'] == 'active' ? 'active' : 'banned'; ?>"><?php echo htmlspecialchars($admin['status']); ?></span></div>
                    </div>

                    <div class="form-actions">
                        <a href="manage-admins-edit.php?id=<?php echo $admin['id']; ?>" class="btn-primary">
                            <i class="fas fa-pen"></i> Edit Admin
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