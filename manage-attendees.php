<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Handle Delete Attendee
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $deleteId = (int)$_POST['attendeeId'];
    $del_query = "DELETE FROM users WHERE id = ? AND role = 'attendee'";
    $stmt_del = mysqli_prepare($conn, $del_query);
    mysqli_stmt_bind_param($stmt_del, "i", $deleteId);
    if (mysqli_stmt_execute($stmt_del)) {
        header("Location: manage-attendees.php?success=Attendee+deleted+successfully");
    } else {
        header("Location: manage-attendees.php?error=Failed+to+delete+attendee");
    }
    exit();
}

// Fetch attendees
$query = "SELECT * FROM users WHERE role = 'attendee' ORDER BY id DESC";
$result = mysqli_query($conn, $query);
$attendeeList = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $attendeeList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Attendees | DIZA Admin</title>
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
                <div class="page-title"><h1>Manage Attendees</h1></div>
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
                            <input type="text" placeholder="Search attendee name...">
                        </div>
                        <a href="manage-attendees-addNew.php" class="btn-primary" style="text-decoration: none;">
                            <i class="fas fa-plus"></i> Add New Attendee
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Attendee Name</th>
                                    <th>Username</th>
                                    <th>Phone</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($attendeeList)): ?>
                                    <?php foreach ($attendeeList as $att): ?>
                                        <tr>
                                            <td>#ATT-<?php echo $att['id']; ?></td>
                                            <td>
                                                <div class="user-cell">
                                                    <img src="https://ui-avatars.com/api?name=<?php echo urlencode($att['first_name'] . '+' . $att['last_name']); ?>" alt="Avatar">
                                                    <div class="user-info">
                                                        <span class="name"><?php echo htmlspecialchars($att['first_name'] . ' ' . $att['last_name']); ?></span>
                                                        <span class="email"><?php echo htmlspecialchars($att['email']); ?></span>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($att['username']); ?></td>
                                            <td><?php echo htmlspecialchars($att['phone_number'] ?? 'N/A'); ?></td>
                                            <td><span class="status active"><?php echo htmlspecialchars($att['status']); ?></span></td>
                                            <td class="text-right">
                                                <a href="manage-attendees-view.php?id=<?php echo $att['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                                <a href="manage-attendees-edit.php?id=<?php echo $att['id']; ?>" class="btn-action edit"><i class="fas fa-pen"></i></a>
                                                <form action="manage-attendees.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="attendeeId" value="<?php echo $att['id']; ?>">
                                                    <button type="submit" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this attendee?');"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="6" style="text-align: center; padding: 20px;">No attendees found.</td></tr>
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