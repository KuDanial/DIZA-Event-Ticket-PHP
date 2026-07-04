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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    $gender = trim($_POST['gender']);
    $student_id = trim($_POST['studentId']);

    mysqli_begin_transaction($conn);
    try {
        $query_up = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, gender = ? WHERE id = ? AND role = 'attendee'";
        $stmt_up = mysqli_prepare($conn, $query_up);
        mysqli_stmt_bind_param($stmt_up, "sssssi", $first_name, $last_name, $phone, $address, $gender, $attendeeId);
        mysqli_stmt_execute($stmt_up);

        $query_att = "UPDATE attendee_details SET student_id = ? WHERE user_id = ?";
        $stmt_att = mysqli_prepare($conn, $query_att);
        mysqli_stmt_bind_param($stmt_att, "si", $student_id, $attendeeId);
        mysqli_stmt_execute($stmt_att);

        mysqli_commit($conn);
        header("Location: manage-attendees.php?success=Attendee+updated+successfully");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = 'Failed to update attendee profile.';
    }
}

$query = "SELECT u.*, a.student_id FROM users u 
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Attendee Profile</title>
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
                <div class="page-title"><h1>Edit Attendee</h1></div>
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
                        <h3>Update Attendee Details</h3>
                    </div>
                    
                    <form action="manage-attendees-edit.php?id=<?php echo $attendeeId; ?>" method="POST" class="org-form">
                        <?php if ($error): ?>
                            <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-section-title">Personal Information</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span>*</span></label>
                                <input type="text" name="firstName" value="<?php echo htmlspecialchars($attendee['first_name']); ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span>*</span></label>
                                <input type="text" name="lastName" value="<?php echo htmlspecialchars($attendee['last_name']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Username (Read Only)</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($attendee['username']); ?>" class="form-input" readonly style="background:#f9f9f9; color:#777;">
                            </div>
                            <div class="form-group">
                                <label>Student ID / Card Number</label>
                                <input type="text" name="studentId" value="<?php echo htmlspecialchars($attendee['student_id'] ?? ''); ?>" class="form-input">
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Gender <span>*</span></label>
                                <select name="gender" class="form-select">
                                    <option value="Male" <?php echo $attendee['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $attendee['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $attendee['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Phone Number <span>*</span></label>
                                <input type="tel" name="phone" value="<?php echo htmlspecialchars($attendee['phone_number']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address <span>*</span></label>
                                <input type="email" value="<?php echo htmlspecialchars($attendee['email']); ?>" class="form-input" readonly style="background-color: #f9f9f9;">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($attendee['address']); ?>" class="form-input">
                        </div>

                        <div class="form-actions">
                            <a href="manage-attendees.php" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Save Changes</button>
                        </div>
                    </form>
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
