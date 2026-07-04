<?php
require_once("connection.php");

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $eventDate = trim($_POST['eventDate']);
    $venueName = trim($_POST['venueName']);
    $venueAddress = trim($_POST['venueAddress']);
    $capacity = (int)$_POST['capacity'];
    $price = (float)$_POST['price'];
    $organizerId = (int)$_POST['organizerId'];
    
    // Format for DB insertion
    $eventDateFormatted = str_replace('T', ' ', $eventDate);

    // Image Upload
    $imagePath = 'event1.png';
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

    $ins_query = "INSERT INTO events (organizer_id, title, description, event_date, venue_name, venue_address, capacity, price, status, image_path) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'draft', ?)";
    $stmt_ins = mysqli_prepare($conn, $ins_query);
    mysqli_stmt_bind_param($stmt_ins, "isssssidss", $organizerId, $title, $description, $eventDateFormatted, $venueName, $venueAddress, $capacity, $price, $imagePath);
    
    if (mysqli_stmt_execute($stmt_ins)) {
        header("Location: manage-events.php?success=Event+created+successfully");
        exit();
    } else {
        $error = 'Failed to create new event.';
    }
}

// Fetch all organizers for dropdown
$org_query = "SELECT u.id, o.company_name, u.first_name, u.last_name FROM users u 
              LEFT JOIN organizer_details o ON u.id = o.user_id 
              WHERE u.role = 'organizer'";
$org_res = mysqli_query($conn, $org_query);
$organizerList = [];
if ($org_res) {
    while ($row = mysqli_fetch_assoc($org_res)) {
        $organizerList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add New Event</title>
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
                <div class="page-title"><h1>Add New Event</h1></div>
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
                        <h3>Event Details</h3>
                        <a href="manage-events.php" class="btn-cancel" style="font-size:12px;">&larr; Back to List</a>
                    </div>
                    
                    <form action="manage-events-addNew.php" method="POST" enctype="multipart/form-data" class="admin-form">
                        <?php if ($error): ?>
                            <div style="background-color: #f8d7da; color: #721c24; padding: 12px 20px; border-radius: 4px; margin-bottom: 20px;">
                                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                            </div>
                        <?php endif; ?>
                        
                        <div class="form-section-title">General Information</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Organizer <span>*</span></label>
                                <select name="organizerId" class="form-select" required>
                                    <option value="" disabled selected>Select Organizer</option>
                                    <?php foreach ($organizerList as $org): ?>
                                        <option value="<?php echo $org['id']; ?>">
                                            <?php echo htmlspecialchars($org['company_name'] ?: ($org['first_name'] . ' ' . $org['last_name'])); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Event Title <span>*</span></label>
                                <input type="text" name="title" class="form-input" placeholder="e.g. Tech Summit 2024" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Category <span>*</span></label>
                                <select name="category" class="form-select" required>
                                    <option value="" disabled selected>Select Category</option>
                                    <option value="Technology">Technology</option>
                                    <option value="Music">Music</option>
                                    <option value="Business">Business</option>
                                    <option value="Education">Education</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Event Banner Image</label>
                                <input type="file" name="eventImage" class="form-input" accept="image/*">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" class="form-textarea" placeholder="Describe the event..."></textarea>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Ticket Price (RM) <span>*</span></label>
                                <input type="number" name="price" class="form-input" step="0.01" placeholder="0.00" required>
                            </div>
                            <div class="form-group">
                                <label>Total Ticket Quantity <span>*</span></label>
                                <input type="number" name="capacity" class="form-input" placeholder="e.g. 100" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Status <span>*</span></label>
                                <select name="status" class="form-select">
                                    <option value="draft">Draft (Hidden)</option>
                                    <option value="published">Published (Visible)</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-section-title">Date & Time</div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Start Date & Time <span>*</span></label>
                                <input type="datetime-local" name="eventDate" class="form-input" required>
                            </div>
                            <div class="form-group">
                                <label>End Date & Time <span>*</span></label>
                                <input type="datetime-local" name="endDate" class="form-input" required>
                            </div>
                        </div>

                        <div class="form-section-title">Venue Information</div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Venue Name <span>*</span></label>
                                <input type="text" name="venueName" class="form-input" placeholder="e.g. Grand Hall" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Full Address <span>*</span></label>
                            <textarea name="venueAddress" class="form-textarea" placeholder="Enter venue address..." required></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="reset" class="btn-cancel">Reset</button>
                            <button type="submit" class="btn-primary"><i class="fas fa-save"></i> Create Event</button>
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
