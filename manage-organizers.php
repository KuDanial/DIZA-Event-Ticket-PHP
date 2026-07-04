<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Handle Delete Organizer
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $deleteId = (int)$_POST['organizerId'];
    mysqli_begin_transaction($conn);
    try {
        // Cascades handle delete organizer_details & events
        mysqli_query($conn, "DELETE FROM users WHERE id = $deleteId AND role = 'organizer'");
        mysqli_commit($conn);
        header("Location: manage-organizers.php?success=Organizer+deleted+successfully");
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: manage-organizers.php?error=Failed+to+delete+organizer");
    }
    exit();
}

// Fetch organizers
$query = "SELECT u.*, o.company_name, o.business_email, 
          (SELECT COUNT(*) FROM events WHERE organizer_id = u.id) as event_count 
          FROM users u 
          LEFT JOIN organizer_details o ON u.id = o.user_id 
          WHERE u.role = 'organizer' ORDER BY u.id DESC";
$result = mysqli_query($conn, $query);
$organizerList = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $organizerList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Organizers | DIZA Admin</title>
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
                <div class="page-title"><h1>Manage Organizers</h1></div>
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api?name=<?php echo urlencode($currentUser['first_name'] . '+' . $currentUser['last_name']); ?>" alt="Admin" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="adminDropdown">
                        <div class="dropdown-header"><p>Logged in as Admin</p></div>
                        <a href="admin-profile-view.php"><i class="fas fa-user-cog"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </header>

            <div class="content-padding">
                <?php if (isset($_GET['success'])): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($_GET['success']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                    </div>
                <?php endif; ?>

                <div class="card card-padded">
                    <div class="table-controls">
                        <div class="search-wrapper">
                            <i class="fas fa-search"></i>
                            <input type="text" placeholder="Search organization name...">
                        </div>
                        <a href="manage-organizers-addNew.php" class="btn-primary" style="text-decoration: none;">
                            <i class="fas fa-plus"></i> Add New Organizer
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Organization</th>
                                    <th>Events Created</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($organizerList)): ?>
                                    <?php foreach ($organizerList as $org): 
                                        $dispName = !empty($org['company_name']) ? $org['company_name'] : ($org['first_name'] . ' ' . $org['last_name']);
                                        $dispEmail = !empty($org['business_email']) ? $org['business_email'] : $org['email'];
                                    ?>
                                        <tr>
                                            <td>#ORG-<?php echo $org['id']; ?></td>
                                            <td>
                                                <div class="user-cell">
                                                    <img src="https://ui-avatars.com/api?name=<?php echo urlencode($dispName); ?>" alt="Org Logo">
                                                    <div class="user-info">
                                                        <span class="name"><?php echo htmlspecialchars($dispName); ?></span>
                                                        <span class="email"><?php echo htmlspecialchars($dispEmail); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><strong><?php echo $org['event_count']; ?></strong></td>
                                            <td><span class="status active"><?php echo htmlspecialchars($org['status']); ?></span></td>
                                            <td class="text-right">
                                                <a href="manage-organizers-view.php?id=<?php echo $org['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                                <a href="manage-organizers-edit.php?id=<?php echo $org['id']; ?>" class="btn-action edit"><i class="fas fa-pen"></i></a>
                                                <form action="manage-organizers.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="organizerId" value="<?php echo $org['id']; ?>">
                                                    <button type="submit" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this organizer? This will delete all their events.');"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="5" style="text-align: center; padding: 20px;">No organizers found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <script>
        function toggleDropdown() {
            document.getElementById("adminDropdown").classList.toggle("show");
        }
    </script>
</body>
</html>