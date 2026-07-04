<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-attendees.php");
    exit();
}
$attendeeId = (int)$_GET['id'];
$query = "SELECT u.*, a.student_id, a.preferred_category FROM users u 
          LEFT JOIN attendee_details a ON u.id = a.user_id 
          WHERE u.id = ? AND u.role = 'attendee' LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $attendeeId);
mysqli_stmt_execute($stmt);
$attendee = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$attendee) {
    header("Location: manage-attendees.php?error=Attendee+not+found");
    exit();
}

// Get booking count: SUM of quantity for confirmed bookings representing actual tickets purchased
$book_res = mysqli_query($conn, "SELECT SUM(quantity) as total FROM bookings WHERE user_id = $attendeeId AND status = 'confirmed'");
$bookingCount = mysqli_fetch_assoc($book_res)['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Attendee Details</title>
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
                <li class="active"><a href="manage-attendees.php"><i class="fas fa-user"></i> Manage Attendees</a></li>
                <li><a href="manage-organizers.php"><i class="fas fa-briefcase"></i> Manage Organizers</a></li>
                <li><a href="manage-events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                <li class="menu-header">System</li>
                <li><a href="manage-admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>Attendee Details</h1></div>
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
                        <a href="manage-attendees.php" class="btn-cancel" style="font-size:12px;">&larr; Back to List</a>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">User ID</div>
                            <div class="view-value">#ATT-<?php echo $attendee['id']; ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Account Status</div>
                            <div class="view-value"><span class="status <?php echo $attendee['status'] == 'active' ? 'active' : 'banned'; ?>"><?php echo htmlspecialchars($attendee['status']); ?></span></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">First Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($attendee['first_name']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Last Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($attendee['last_name']); ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Email Address</div>
                            <div class="view-value"><?php echo htmlspecialchars($attendee['email']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Phone</div>
                            <div class="view-value"><?php echo !empty($attendee['phone_number']) ? htmlspecialchars($attendee['phone_number']) : 'Not provided'; ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Username</div>
                            <div class="view-value"><?php echo htmlspecialchars($attendee['username']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Gender</div>
                            <div class="view-value"><?php echo !empty($attendee['gender']) ? htmlspecialchars($attendee['gender']) : 'Not specified'; ?></div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Student ID / Card Number</div>
                            <div class="view-value"><?php echo htmlspecialchars($attendee['student_id'] ?? 'Not specified'); ?></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">Registered Address</div>
                        <div class="view-value"><?php echo !empty($attendee['address']) ? nl2br(htmlspecialchars($attendee['address'])) : 'Not provided'; ?></div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">Activity Stats</div>
                        <div class="view-value">
                            <span class="stat-badge"><i class="fas fa-ticket-alt"></i> <?php echo $bookingCount; ?> Ticket<?php echo $bookingCount != 1 ? 's' : ''; ?> Bought</span>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="manage-attendees-edit.php?id=<?php echo $attendee['id']; ?>" class="btn-primary">
                            <i class="fas fa-pen"></i> Edit Attendee
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
