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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phoneNumber']);
    $gender = trim($_POST['gender']);
    $address = trim($_POST['address']);
    
    // Check if email already exists for another user
    $chk = "SELECT id FROM users WHERE email = ? AND id != ? LIMIT 1";
    $stmt_chk = mysqli_prepare($conn, $chk);
    mysqli_stmt_bind_param($stmt_chk, "si", $email, $adminId);
    mysqli_stmt_execute($stmt_chk);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_chk))) {
        $error = 'Email address already in use by another account.';
    } else {
        $query_up = "UPDATE users SET first_name = ?, last_name = ?, email = ?, phone_number = ?, gender = ?, address = ? WHERE id = ? AND role = 'admin'";
        $stmt_up = mysqli_prepare($conn, $query_up);
        mysqli_stmt_bind_param($stmt_up, "ssssssi", $first_name, $last_name, $email, $phone, $gender, $address, $adminId);
        
        if (mysqli_stmt_execute($stmt_up)) {
            header("Location: manage-admins.php?success=Admin+updated+successfully");
            exit();
        } else {
            $error = 'Failed to update administrator profile.';
        }
    }
}

$query = "SELECT * FROM users WHERE id = ? AND role = 'admin' LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $adminId);
mysqli_stmt_execute($stmt);
$adminToEdit = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$adminToEdit) {
    header("Location: manage-admins.php?error=Admin+not+found");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Admin</title>
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
                <div class="page-title"><h1>Edit Admin</h1></div>
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
                        <h3>Update Information</h3>
                        <span class="optional-tag">ID: #ADM-<?php echo $adminToEdit['id']; ?></span>
                    </div>
                    
                    <form action="manage-admins-edit.php?id=<?php echo $adminId; ?>" method="POST" class="admin-form">
                        <?php if ($error): ?>
                            <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span>*</span></label>
                                <input type="text" name="firstName" value="<?php echo htmlspecialchars($adminToEdit['first_name']); ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span>*</span></label>
                                <input type="text" name="lastName" value="<?php echo htmlspecialchars($adminToEdit['last_name']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Username (Read Only)</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($adminToEdit['username']); ?>" class="form-input" readonly style="background:#f9f9f9; color:#777;">
                            </div>
                            <div class="form-group">
                                <label>Email Address <span>*</span></label>
                                <input type="email" name="email" value="<?php echo htmlspecialchars($adminToEdit['email']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Phone Number</label>
                                <input type="tel" name="phoneNumber" value="<?php echo htmlspecialchars($adminToEdit['phone_number'] ?? ''); ?>" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Gender</label>
                                <select name="gender" class="form-select">
                                    <option value="" <?php echo empty($adminToEdit['gender']) ? 'selected' : ''; ?>>Select Gender</option>
                                    <option value="Male" <?php echo $adminToEdit['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $adminToEdit['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $adminToEdit['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Address</label>
                                <input type="text" name="address" value="<?php echo htmlspecialchars($adminToEdit['address'] ?? ''); ?>" class="form-input">
                            </div>
                        </div>

                        <div class="form-actions">
                            <a href="manage-admins.php" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Update Admin</button>
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