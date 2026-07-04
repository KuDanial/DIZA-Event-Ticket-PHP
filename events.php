<?php
require_once("connection.php");
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Search & Filter queries
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$location = isset($_GET['location']) ? trim($_GET['location']) : '';
$category = isset($_GET['category']) ? trim($_GET['category']) : '';
$price_range = isset($_GET['price']) ? trim($_GET['price']) : '';

$query = "SELECT * FROM events WHERE status = 'published'";
$params = [];
$types = "";

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR description LIKE ? OR venue_name LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= "sss";
}

if (!empty($location)) {
    $query .= " AND (venue_name LIKE ? OR venue_address LIKE ?)";
    $loc_param = "%$location%";
    $params[] = $loc_param;
    $params[] = $loc_param;
    $types .= "ss";
}

if (!empty($category)) {
    $query .= " AND (title LIKE ? OR description LIKE ?)";
    $cat_param = "%$category%";
    $params[] = $cat_param;
    $params[] = $cat_param;
    $types .= "ss";
}

if ($price_range === 'under50') {
    $query .= " AND price < 50";
} elseif ($price_range === '50to150') {
    $query .= " AND price BETWEEN 50 AND 150";
} elseif ($price_range === 'over150') {
    $query .= " AND price > 150";
}

$query .= " ORDER BY event_date ASC";

$stmt = mysqli_prepare($conn, $query);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
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
    <title>DIZA Event Ticket | Browse Events</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <section class="hero" style="height: 250px; background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/background1.jpg'); background-size: cover; background-repeat: no-repeat; background-repeat: no-repeat; background-position: center">
        <div class="hero-overlay">
            <h1>All Events</h1>
            <p>Explore concerts, workshops, and sports happening near you.</p>
        </div>
    </section>

    <div class="events-page-container">
        
        <aside class="filter-sidebar">
            <form action="events.php" method="GET">
                <input type="hidden" name="search" value="<?php echo htmlspecialchars($search); ?>">
                
                <div class="filter-group">
                    <h4>Categories / Keyword</h4>
                    <label class="filter-option">
                        <input type="radio" name="category" value="" <?php echo empty($category) ? 'checked' : ''; ?>> All
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="category" value="Concert" <?php echo $category === 'Concert' ? 'checked' : ''; ?>> Concerts
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="category" value="Sports" <?php echo $category === 'Sports' ? 'checked' : ''; ?>> Sports
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="category" value="Workshop" <?php echo $category === 'Workshop' ? 'checked' : ''; ?>> Workshops
                    </label>
                </div>

                <div class="filter-group">
                    <h4>Location</h4>
                    <label class="filter-option">
                        <input type="radio" name="location" value="" <?php echo empty($location) ? 'checked' : ''; ?>> All Locations
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="location" value="Kuala Lumpur" <?php echo $location === 'Kuala Lumpur' ? 'checked' : ''; ?>> Kuala Lumpur
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="location" value="Machang" <?php echo $location === 'Machang' ? 'checked' : ''; ?>> UiTM Machang
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="location" value="Penang" <?php echo $location === 'Penang' ? 'checked' : ''; ?>> Penang
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="location" value="Online" <?php echo $location === 'Online' ? 'checked' : ''; ?>> Online
                    </label>
                </div>

                <div class="filter-group">
                    <h4>Price Range</h4>
                    <label class="filter-option">
                        <input type="radio" name="price" value="" <?php echo empty($price_range) ? 'checked' : ''; ?>> Any Price
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="price" value="under50" <?php echo $price_range === 'under50' ? 'checked' : ''; ?>> Under RM 50
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="price" value="50to150" <?php echo $price_range === '50to150' ? 'checked' : ''; ?>> RM 50 - RM 150
                    </label>
                    <label class="filter-option">
                        <input type="radio" name="price" value="over150" <?php echo $price_range === 'over150' ? 'checked' : ''; ?>> RM 150+
                    </label>
                </div>
                
                <button type="submit" class="btn-primary" style="width: 100%; font-size: 14px;">Apply Filters</button>
            </form>
        </aside>

        <main class="events-main-content">
            <div class="events-header-bar">
                <h3>Showing <?php echo count($eventList); ?> Events</h3>
            </div>

            <div class="event-grid">
                <?php if (!empty($eventList)): ?>
                    <?php foreach ($eventList as $event): 
                        $dt = new DateTime($event['event_date']);
                        $formattedDate = $dt->format('d M Y');
                    ?>
                        <div class="event-card">
                        <div class="card-image">
                            <img src="images/<?php echo htmlspecialchars($event['image_path'] ?? 'event1.png'); ?>" alt="<?php echo htmlspecialchars($event['title']); ?>">
                        </div>
                            <div class="card-content">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <div class="event-meta">
                                    <p><i class="far fa-calendar-alt"></i> <?php echo $formattedDate; ?></p>
                                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue_name']); ?></p>
                                </div>
                                <div class="card-footer">
                                    <span class="price">
                                        <?php if ($event['price'] == 0): ?>
                                            <strong>Free</strong>
                                        <?php else: ?>
                                            From <strong>RM <?php echo number_format($event['price'], 2); ?></strong>
                                        <?php endif; ?>
                                    </span>
                                    <a href="booking.php?eventId=<?php echo $event['id']; ?>" class="btn-buy" style="text-decoration: none; display: inline-block;">
                                        <?php echo $event['price'] == 0 ? 'Register' : 'Buy Now'; ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">No events match your criteria.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <footer>
        <div class="container footer-content">
            <div class="footer-col brand-col">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZA Event Ticket" class="footer-logo">
                <p style="font-size: 12px; text-transform: uppercase; letter-spacing: 1px; color: #666; margin-bottom: 8px;"> In Collaboration With </p>
                <div style="display: flex; align-items: center; gap: 15px;">
                        <img src="images/LOGO UiTM OUTLINE 3 (WHITE).png" alt="UiTM" style="height: 40px; opacity: 0.8;">
                        <span style="color:#555;">|</span>
                        <img src="images/Google-Gemini-White-Logo.png" alt="Gemini" style="height: 40px; opacity: 0.8;">
                </div>
                <p>Your one-stop platform for all event ticketing needs.</p>
            </div>
            <div class="footer-col contact-col">
                <h4>Contact Us</h4>
                <div class="contact-details">
                    <p><b>Hotline:</b> <br> +60 16-262 4834 / +60 11-6551 3006</p>
                    <p><b>Email:</b> <br> support@diza.uitm.edu.my</p>
                </div>
            </div>
            <div class="footer-col">
                <h4>Support</h4>
                <ul class="footer-links-list">
                    <li><a href="about.php">About Us</a></li>
                    <li><a href="help.php">Contact</a></li>
                    <li><a href="help.php">FAQ</a></li>
                </ul>
            </div>
            <div class="footer-col">
                <h4>Follow Us</h4>
                <div class="footer-social">
                    <a href="https://www.facebook.com/modannie"><i class="fab fa-facebook"></i></a>
                    <a href="https://x.com/modannyyy"><i class="fab fa-twitter"></i></a>
                    <a href="https://www.instagram.com/tadanny._"><i class="fab fa-instagram"></i></a>
                    <a href="https://discord.gg/95K89KDhTm"><i class="fab fa-discord"></i></a>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 DIZAZ Group. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>