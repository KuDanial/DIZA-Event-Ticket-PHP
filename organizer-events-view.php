<?php
require_once("connection.php");

// Security Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: organizer-events.php");
    exit();
}
$eventId = (int)$_GET['id'];
$query = "SELECT * FROM events WHERE id = ? AND organizer_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "ii", $eventId, $currentUser['id']);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$event) {
    header("Location: organizer-events.php");
    exit();
}

// Calculate tickets booked
$book_res = mysqli_query($conn, "SELECT SUM(quantity) as total FROM bookings WHERE event_id = $eventId AND status = 'confirmed'");
$booked = mysqli_fetch_assoc($book_res)['total'] ?? 0;
$formattedDate = date('d M Y - H:i', strtotime($event['event_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Event Details</title>
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
                <div class="page-title"><h1>Event Details</h1></div>
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
                        <h3>Event Overview</h3>
                        <a href="organizer-events.php" class="btn-cancel" style="font-size:12px;">&larr; Back to List</a>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Event ID</div>
                            <div class="view-value">#EVT-<?php echo $event['id']; ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Status</div>
                            <div class="view-value">
                                <?php if ($event['status'] === 'published'): ?>
                                    <span class="badge-published">Published</span>
                                <?php elseif ($event['status'] === 'draft'): ?>
                                    <span class="badge-draft">Draft</span>
                                <?php elseif ($event['status'] === 'cancelled'): ?>
                                    <span class="badge-cancelled">Cancelled</span>
                                <?php elseif ($event['status'] === 'completed'): ?>
                                    <span class="badge-past">Completed</span>
                                <?php else: ?>
                                    <span class="status <?php echo $event['status']; ?>"><?php echo htmlspecialchars($event['status']); ?></span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Event Title</div>
                            <div class="view-value"><?php echo htmlspecialchars($event['title']); ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Event Image</div>
                            <div class="view-value">
                                <?php if (!empty($event['image_path'])): ?>
                                    <img src="images/<?php echo htmlspecialchars($event['image_path']); ?>" alt="Event Image" style="max-width: 300px; height: auto; border-radius: 6px; border: 1px solid #ddd; display: block; margin-top: 5px;">
                                <?php else: ?>
                                    <span style="color:#999;">No image uploaded</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">Description</div>
                        <div class="view-value"><?php echo !empty($event['description']) ? nl2br(htmlspecialchars($event['description'])) : 'No description provided'; ?></div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Event Date & Time</div>
                            <div class="view-value"><?php echo $formattedDate; ?></div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Available Capacity</div>
                            <div class="view-value"><?php echo htmlspecialchars($event['capacity']); ?> Pax</div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Ticket Price</div>
                            <div class="view-value" style="color: #27ae60; font-weight: bold;">
                                <?php echo $event['price'] == 0 ? 'Free' : 'RM ' . number_format($event['price'], 2); ?>
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="view-label">Tickets Booked</div>
                            <div class="view-value"><?php echo $booked; ?> Tickets</div>
                        </div>
                    </div>

                    <div class="form-section-title" style="margin-top: 20px; border-bottom: 1px solid #eee; padding-bottom: 10px; margin-bottom: 15px; font-size: 16px; font-weight: 600;">Venue Details</div>

                    <div class="form-row">
                        <div class="form-group">
                            <div class="view-label">Venue Name</div>
                            <div class="view-value"><?php echo htmlspecialchars($event['venue_name']); ?></div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="view-label">Address</div>
                        <div class="view-value"><?php echo nl2br(htmlspecialchars($event['venue_address'])); ?></div>
                    </div>

                    <div class="form-actions">
                        <a href="organizer-events-edit.php?id=<?php echo $event['id']; ?>" class="btn-primary">
                            <i class="fas fa-pen"></i> Edit Event
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
