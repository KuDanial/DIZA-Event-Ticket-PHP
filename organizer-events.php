<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];
$orgId = $currentUser['id'];

// Handle Delete Event
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $eventId = (int)$_POST['eventId'];
    $del_query = "DELETE FROM events WHERE id = ? AND organizer_id = ?";
    $stmt_del = mysqli_prepare($conn, $del_query);
    mysqli_stmt_bind_param($stmt_del, "ii", $eventId, $orgId);
    if (mysqli_stmt_execute($stmt_del)) {
        header("Location: organizer-events.php?success=Event+deleted+successfully");
    } else {
        header("Location: organizer-events.php?error=Failed+to+delete+event");
    }
    exit();
}

// Handle Status Toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'updateStatus') {
    $eventId = (int)$_POST['id'];
    $newStatus = $_POST['status'];
    $up_query = "UPDATE events SET status = ? WHERE id = ? AND organizer_id = ?";
    $stmt_up = mysqli_prepare($conn, $up_query);
    mysqli_stmt_bind_param($stmt_up, "sii", $newStatus, $eventId, $orgId);
    if (mysqli_stmt_execute($stmt_up)) {
        header("Location: organizer-events.php?success=Status+updated");
    } else {
        header("Location: organizer-events.php?error=Failed+to+update+status");
    }
    exit();
}

// Fetch organizer's events
$query = "SELECT * FROM events WHERE organizer_id = ? ORDER BY id DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $orgId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
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
    <title>My Events | DIZA Organizer</title>
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
                <li class="active"><a href="organizer-events.php"><i class="fas fa-calendar-alt"></i> My Events List</a></li>
                <li class="menu-header">Account</li>
                <li><a href="organizer-profile-view.php"><i class="fas fa-user-cog"></i> Org Profile</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>My Created Events</h1></div>
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
                        <a href="organizer-events-addNew.php" class="btn-primary" style="text-decoration: none;">
                            <i class="fas fa-plus"></i> Create New Event
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Price</th>
                                    <th>Event Date</th>
                                    <th>Venue Name</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($eventList)): ?>
                                    <?php foreach ($eventList as $event): ?>
                                        <tr>
                                            <td>#E-<?php echo $event['id']; ?></td>
                                            <td><b><?php echo htmlspecialchars($event['title']); ?></b></td>
                                            <td>RM <?php echo number_format($event['price'], 2); ?></td>
                                            <td><?php echo htmlspecialchars($event['event_date']); ?></td>
                                            <td><?php echo htmlspecialchars($event['venue_name']); ?></td>
                                            <td>
                                                <span class="status <?php echo $event['status']; ?>"><?php echo htmlspecialchars($event['status']); ?></span>
                                                <form action="organizer-events.php" method="POST" style="display: inline; margin-left:5px;">
                                                    <input type="hidden" name="action" value="updateStatus">
                                                    <input type="hidden" name="id" value="<?php echo $event['id']; ?>">
                                                    <input type="hidden" name="status" value="<?php echo $event['status'] === 'published' ? 'draft' : 'published'; ?>">
                                                    <button type="submit" class="btn-action" style="padding:4px 8px; font-size:11px;">
                                                        <?php echo $event['status'] === 'published' ? 'Unpublish' : 'Publish'; ?>
                                                    </button>
                                                </form>
                                            </td>
                                            <td class="text-right">
                                                <a href="organizer-events-view.php?id=<?php echo $event['id']; ?>" class="btn-action view"><i class="fas fa-eye"></i></a>
                                                <a href="organizer-events-edit.php?id=<?php echo $event['id']; ?>" class="btn-action edit"><i class="fas fa-pen"></i></a>
                                                <form action="organizer-events.php" method="POST" style="display: inline;">
                                                    <input type="hidden" name="action" value="delete">
                                                    <input type="hidden" name="eventId" value="<?php echo $event['id']; ?>">
                                                    <button type="submit" class="btn-action delete" onclick="return confirm('Are you sure you want to delete this event?');"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr><td colspan="7" style="text-align: center; padding: 20px;">No events created yet.</td></tr>
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