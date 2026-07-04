<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Handle Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $eventId = (int)$_POST['eventId'];
    $del_query = "DELETE FROM events WHERE id = ?";
    $stmt_del = mysqli_prepare($conn, $del_query);
    mysqli_stmt_bind_param($stmt_del, "i", $eventId);
    if (mysqli_stmt_execute($stmt_del)) {
        header("Location: manage-events.php?success=Event+deleted+successfully");
    } else {
        header("Location: manage-events.php?error=Failed+to+delete+event");
    }
    exit();
}

// Handle Update Status (Publish/Unpublish)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    $eventId = (int)$_POST['id'];
    $newStatus = $_POST['status'];
    $up_query = "UPDATE events SET status = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($conn, $up_query);
    mysqli_stmt_bind_param($stmt_up, "si", $newStatus, $eventId);
    if (mysqli_stmt_execute($stmt_up)) {
        header("Location: manage-events.php?success=Event+status+updated+successfully");
    } else {
        header("Location: manage-events.php?error=Failed+to+update+status");
    }
    exit();
}

// Fetch all events
$query = "SELECT e.*, u.first_name, u.last_name, o.company_name FROM events e 
          JOIN users u ON e.organizer_id = u.id 
          LEFT JOIN organizer_details o ON u.id = o.user_id 
          ORDER BY e.id DESC";
$result = mysqli_query($conn, $query);
$eventList = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $eventList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Events - Admin Panel</title>
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
                <li class="active"><a href="manage-events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                <li class="menu-header">System</li>
                <li><a href="manage-admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>Manage Events</h1></div>
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
                            <input type="text" placeholder="Search events...">
                        </div>
                        <a href="manage-events-addNew.php" class="btn-primary" style="text-decoration: none;">
                            <i class="fas fa-plus"></i> Add New Event
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Venue Info</th>
                                    <th>Organizer</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($eventList)): ?>
                                    <?php foreach ($eventList as $event): 
                                        $dispOrg = !empty($event['company_name']) ? $event['company_name'] : ($event['first_name'] . ' ' . $event['last_name']);
                                    ?>
                                        <tr>
                                            <td>#E-<?php echo $event['id']; ?></td>
                                            <td><b><?php echo htmlspecialchars($event['title']); ?></b></td>
                                            <td>RM <?php echo number_format($event['price'], 2); ?></td>
                                            <td>
                                                <div class="venue-info">
                                                    <span class="venue-name"><?php echo htmlspecialchars($event['venue_name']); ?></span>
                                                    <span class="venue-addr"><?php echo htmlspecialchars($event['venue_address']); ?></span>
                                                </div>
                                            </td>
                                            <td><?php echo htmlspecialchars($dispOrg); ?></td>
                                            <td>
                                                <span class="status <?php echo $event['status']; ?>"><?php echo htmlspecialchars($event['status']); ?></span>
                                                <form action="manage-events.php" method="POST" style="display: inline; margin-left: 5px;">
                                                    <input type="hidden" name="action" value="updateStatus">
                                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $event['status'] === 'published' ? 'draft' : 'published'; ?>">
                                                    <button type="submit" class="btn-action" style="padding: 5px; font-size: 11px;">
                                                        <?php echo $event['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-right">
                                                <a href="manage-events-view.php?id=<?php echo $event['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                                <a href="manage-events-edit.php?id=<?php echo $event['id']; ?>" class="btn-action edit"><i class="fas fa-pen"></i></a>
                                                <form action="manage-events.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="eventId" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this event?');"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align: center; padding: 20px;">No events found.</td></tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>