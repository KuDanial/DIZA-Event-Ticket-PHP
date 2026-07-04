<?php
require_once("connection.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: manage-events.php");
    exit();
}
$eventId = (int)$_GET['id'];

// Get current event details
$query = "SELECT * FROM events WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $eventId);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

if (!$event) {
    header("Location: manage-events.php");
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $eventDate = trim($_POST['eventDate']);
    $venueName = trim($_POST['venueName']);
    $venueAddress = trim($_POST['venueAddress']);
    $capacity = (int)$_POST['capacity'];
    $price = (float)$_POST['price'];
    $status = $_POST['status'];
    
    // Check if event date is ISO compatible
    $eventDateFormatted = str_replace('T', ' ', $eventDate);

    // Image Upload
    $imagePath = $event['image_path'];
    if (isset($_FILES['eventImage']) && $_FILES['eventImage']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['eventImage']['tmp_name'];
        $fileName = $_FILES['eventImage']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExtensions = array('jpg', 'jpeg', 'png', 'gif', 'webp');
        if (in_array($fileExtension, $allowedExtensions)) {
            $newFileName = time() . '_' . preg_replace("/[^a-zA-Z0-9\._-]/", "", basename($fileName));
            $uploadFileDir = 'images/';
            if (!is_dir($uploadFileDir)) {
                mkdir($uploadFileDir, 0755, true);
            }
            $dest_path = $uploadFileDir . $newFileName;
            if (move_uploaded_file($fileTmpPath, $dest_path)) {
                $imagePath = $newFileName;
            }
        }
    }

    $up_query = "UPDATE events SET title = ?, description = ?, event_date = ?, venue_name = ?, venue_address = ?, capacity = ?, price = ?, status = ?, image_path = ? WHERE id = ?";
    $stmt_up = mysqli_prepare($conn, $up_query);
    mysqli_stmt_bind_param($stmt_up, "sssssidssi", $title, $description, $eventDateFormatted, $venueName, $venueAddress, $capacity, $price, $status, $imagePath, $eventId);
    if (mysqli_stmt_execute($stmt_up)) {
        header("Location: manage-events.php?success=Event+updated+successfully");
        exit();
    } else {
        $error = 'Failed to update event details.';
    }
}

// Refresh event details
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $eventId);
mysqli_stmt_execute($stmt);
$event = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
$eventDateVal = date('Y-m-d\TH:i', strtotime($event['event_date']));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Event</title>
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
                <div class="page-title"><h1>Edit Event</h1></div>
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
                        <h3>Update Event</h3>
                        <span class="optional-tag">ID: #EVT-<?php echo $event['id']; ?></span>
                    </div>
                    
                    <form action="manage-events-edit.php?id=<?php echo $eventId; ?>" method="POST" enctype="multipart/form-data" class="admin-form">
                        <?php if ($error): ?>
                            <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-section-title">General Information</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Event Title <span>*</span></label>
                                <input type="text" name="title" value="<?php echo htmlspecialchars($event['title']); ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>Category <span>*</span></label>
                                <select name="category" class="form-select">
                                    <option value="Technology">Technology</option>
                                    <option value="Music">Music</option>
                                    <option value="Business">Business</option>
                                    <option value="Education">Education</option>
                                    <option value="Sports">Sports</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-textarea"><?php echo htmlspecialchars($event['description']); ?></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ticket Price (RM) <span>*</span></label>
                                <input type="number" name="price" value="<?php echo htmlspecialchars($event['price']); ?>" class="form-input" step="0.01" required>
                            </div>
                            <div class="form-group">
                                <label>Total Ticket Quantity <span>*</span></label>
                                <input type="number" name="capacity" value="<?php echo htmlspecialchars($event['capacity']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Status</label>
                                <select name="status" class="form-select">
                                    <option value="draft" <?php echo $event['status'] === 'draft' ? 'selected' : ''; ?>>Draft</option>
                                    <option value="published" <?php echo $event['status'] === 'published' ? 'selected' : ''; ?>>Published</option>
                                    <option value="cancelled" <?php echo $event['status'] === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
                                    <option value="completed" <?php echo $event['status'] === 'completed' ? 'selected' : ''; ?>>Completed</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Current Event Image</label>
                                <div style="margin-bottom: 10px;">
                                    <?php if (!empty($event['image_path'])): ?>
                                        <img src="images/<?php echo htmlspecialchars($event['image_path']); ?>" alt="Current Image" style="max-width: 150px; height: auto; border-radius: 6px; border: 1px solid #ddd; display: block;">
                                    <?php else: ?>
                                        <span style="color: #999;">No image uploaded</span>
                                    <?php endif; ?>
                                </div>
                                <label>Replace Event Image</label>
                                <input type="file" name="eventImage" class="form-input" accept="image/*">
                            </div>
                        </div>

                        <div class="form-section-title">Date & Time</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date & Time</label>
                                <input type="datetime-local" name="eventDate" value="<?php echo $eventDateVal; ?>" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>End Date & Time</label>
                                <input type="datetime-local" name="endDate" value="<?php echo $eventDateVal; ?>" class="form-input">
                            </div>
                        </div>

                        <div class="form-section-title">Venue Information</div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Venue Name</label>
                                <input type="text" name="venueName" value="<?php echo htmlspecialchars($event['venue_name']); ?>" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Full Address</label>
                            <textarea name="venueAddress" class="form-textarea" required><?php echo htmlspecialchars($event['venue_address']); ?></textarea>
                        </div>

                        <div class="form-actions">
                            <a href="manage-events.php" class="btn-cancel">Cancel</a>
                            <button type="submit" class="btn-primary"><i class="fas fa-check"></i> Update Event</button>
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
