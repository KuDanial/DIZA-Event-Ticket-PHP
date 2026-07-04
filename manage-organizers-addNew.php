<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $email = trim($_POST['email']);
    $personal_phone = trim($_POST['phone']);
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);
    $gender = isset($_POST['gender']) ? $_POST['gender'] : '';
    $address = trim($_POST['address']);
    
    $company_name = trim($_POST['companyName']);
    $business_phone = trim($_POST['businessPhone']);
    $business_email = trim($_POST['businessEmail']);
    $website_url = trim($_POST['websiteUrl']);
    $about_description = trim($_POST['aboutDescription']);

    // Check exists
    $chk = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
    $stmt_chk = mysqli_prepare($conn, $chk);
    mysqli_stmt_bind_param($stmt_chk, "ss", $username, $email);
    mysqli_stmt_execute($stmt_chk);
    if (mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_chk))) {
        $error = 'Username or email already exists.';
    } else {
        $pwd_hash = md5($password);
        mysqli_begin_transaction($conn);
        try {
            $insert = "INSERT INTO users (username, password_hash, first_name, last_name, email, phone_number, gender, address, role, status) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'organizer', 'active')";
            $stmt_ins = mysqli_prepare($conn, $insert);
            mysqli_stmt_bind_param($stmt_ins, "ssssssss", $username, $pwd_hash, $first_name, $last_name, $email, $personal_phone, $gender, $address);
            mysqli_stmt_execute($stmt_ins);

            $user_id = mysqli_insert_id($conn);
            $stmt_det = mysqli_prepare($conn, "INSERT INTO organizer_details (user_id, company_name, business_phone, business_email, website_url, about_description) VALUES (?, ?, ?, ?, ?, ?)");
            mysqli_stmt_bind_param($stmt_det, "isssss", $user_id, $company_name, $business_phone, $business_email, $website_url, $about_description);
            mysqli_stmt_execute($stmt_det);

            mysqli_commit($conn);
            header("Location: manage-organizers.php?success=Organizer+created+successfully");
            exit();
        } catch (Exception $e) {
            mysqli_rollback($conn);
            $error = 'Failed to create organizer profile.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Organizer</title>
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
                <div class="page-title"><h1>Add New Organizer</h1></div>
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
                        <h3>Organizer Details</h3>
                        <a href="manage-organizers.php" class="btn-cancel" style="font-size:12px;">&larr; Back to List</a>
                    </div>
                    
                    <form action="manage-organizers-addNew.php" method="POST" class="admin-form">
                        <?php if ($error): ?>
                            <div class="alert alert-error" style="margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-section-title">Personal Information</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span>*</span></label>
                                <input type="text" name="firstName" class="form-input" placeholder="e.g. John" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span>*</span></label>
                                <input type="text" name="lastName" class="form-input" placeholder="e.g. Doe" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Username <span>*</span></label>
                                <input type="text" name="username" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Gender <span>*</span></label>
                                <select name="gender" class="form-select" required>
                                    <option value="" disabled selected>Select Gender</option>
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address <span>*</span></label>
                                <input type="email" name="email" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Phone Number <span>*</span></label>
                                <input type="tel" name="phone" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" class="form-input">
                        </div>

                        <div class="form-group">
                            <label>Password <span>*</span></label>
                            <input type="password" name="password" class="form-input" placeholder="Create a strong password" required>
                        </div>

                        <div class="form-section-title">Organization Details</div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Company Name <span>*</span></label>
                                <input type="text" name="companyName" class="form-input" placeholder="e.g. Tech Corp" required>
                            </div>
                            <div class="form-group">
                                <label>Business Phone <span>*</span></label>
                                <input type="tel" name="businessPhone" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Business Email</label>
                                <input type="email" name="businessEmail" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Website URL</label>
                                <input type="url" name="websiteUrl" class="form-input" placeholder="https://example.com">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>About Description</label>
                            <textarea name="aboutDescription" class="form-textarea" placeholder="Brief info about the company..."></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn-cancel">Reset</button>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Organizer</button>
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
