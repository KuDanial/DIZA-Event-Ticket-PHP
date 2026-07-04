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

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = trim($_POST['firstName']);
    $last_name = trim($_POST['lastName']);
    $phone_number = trim($_POST['phoneNumber']);
    $address = trim($_POST['address']);
    $gender = trim($_POST['gender']);
    
    $company_name = trim($_POST['companyName']);
    $business_phone = trim($_POST['businessPhone']);
    $business_email = trim($_POST['businessEmail']);
    $website_url = trim($_POST['websiteUrl']);
    $about_description = trim($_POST['aboutDescription']);

    mysqli_begin_transaction($conn);
    try {
        // Update user
        $u_query = "UPDATE users SET first_name = ?, last_name = ?, phone_number = ?, address = ?, gender = ? WHERE id = ?";
        $stmt_u = mysqli_prepare($conn, $u_query);
        mysqli_stmt_bind_param($stmt_u, "sssssi", $first_name, $last_name, $phone_number, $address, $gender, $currentUser['id']);
        mysqli_stmt_execute($stmt_u);

        // Update organizer details
        $o_query = "UPDATE organizer_details SET company_name = ?, business_phone = ?, business_email = ?, website_url = ?, about_description = ? WHERE user_id = ?";
        $stmt_o = mysqli_prepare($conn, $o_query);
        mysqli_stmt_bind_param($stmt_o, "sssssi", $company_name, $business_phone, $business_email, $website_url, $about_description, $currentUser['id']);
        mysqli_stmt_execute($stmt_o);

        mysqli_commit($conn);
        
        // Update session
        $_SESSION['user']['first_name'] = $first_name;
        $_SESSION['user']['last_name'] = $last_name;
        $_SESSION['user']['phone_number'] = $phone_number;
        $_SESSION['user']['address'] = $address;
        $_SESSION['user']['gender'] = $gender;

        header("Location: organizer-profile-view.php?success=1");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        $error = 'Failed to update profile details.';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Organizer</title>
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
                <div class="page-title"><h1>Edit Profile</h1></div>
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api?name=<?php echo urlencode($currentUser['first_name'] . '+' . $currentUser['last_name']); ?>" alt="Org" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="dropdown-header">
                            <p>Logged in as Organizer</p>
                        </div>
                        <a href="organizer-profile-view.php"><i class="fas fa-user-cog"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </header>

            <div class="content-padding">
                <div class="card card-padded">
                    <div class="card-header">
                        <h3>Update Information</h3>
                    </div>
                    
                    <form action="organizer-profile-edit.php" method="POST" class="org-form">
                        <?php if ($error): ?>
                            <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-section-title">Personal Information</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>First Name <span>*</span></label>
                                <input type="text" name="firstName" value="<?php echo htmlspecialchars($currentUser['first_name']); ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Last Name <span>*</span></label>
                                <input type="text" name="lastName" value="<?php echo htmlspecialchars($currentUser['last_name']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Username (Read Only)</label>
                                <input type="text" name="username" value="<?php echo htmlspecialchars($currentUser['username']); ?>" class="form-input" readonly style="background:#f9f9f9; color:#777;">
                            </div>
                            <div class="form-group">
                                <label>Gender <span>*</span></label>
                                <select name="gender" class="form-select">
                                    <option value="Male" <?php echo $currentUser['gender'] === 'Male' ? 'selected' : ''; ?>>Male</option>
                                    <option value="Female" <?php echo $currentUser['gender'] === 'Female' ? 'selected' : ''; ?>>Female</option>
                                    <option value="Other" <?php echo $currentUser['gender'] === 'Other' ? 'selected' : ''; ?>>Other</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Email Address <span>*</span></label>
                                <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" class="form-input" readonly style="background-color: #f9f9f9;">
                            </div>
                            <div class="form-group">
                                <label>Phone Number <span>*</span></label>
                                <input type="tel" name="phoneNumber" value="<?php echo htmlspecialchars($currentUser['phone_number']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address</label>
                            <input type="text" name="address" value="<?php echo htmlspecialchars($currentUser['address']); ?>" class="form-input">
                        </div>

                        <div class="form-section-title">Organization Details</div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Company Name <span>*</span></label>
                                <input type="text" name="companyName" value="<?php echo htmlspecialchars($orgProfile['company_name'] ?? ''); ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Business Phone <span>*</span></label>
                                <input type="tel" name="businessPhone" value="<?php echo htmlspecialchars($orgProfile['business_phone'] ?? ''); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Business Email</label>
                                <input type="email" name="businessEmail" value="<?php echo htmlspecialchars($orgProfile['business_email'] ?? ''); ?>" class="form-input">
                            </div>
                            <div class="form-group">
                                <label>Website URL</label>
                                <input type="url" name="websiteUrl" value="<?php echo htmlspecialchars($orgProfile['website_url'] ?? ''); ?>" class="form-input">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>About Description</label>
                            <textarea name="aboutDescription" class="form-textarea"><?php echo htmlspecialchars($orgProfile['about_description'] ?? ''); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="organizer-profile-view.php" class="btn-cancel">Cancel</a>
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
