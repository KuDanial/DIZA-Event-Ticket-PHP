<?php
require_once("connection.php");
$currentUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;

// Fetch trending events (limit 4 published events)
$query = "SELECT * FROM events WHERE status = 'published' ORDER BY event_date ASC LIMIT 4";
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
    <title>DIZA Event Ticket | Home</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <section class="hero" style="background: linear-gradient(rgba(0,0,0,0.6), rgba(0,0,0,0.6)), url('images/background1.jpg'); background-size: cover; background-repeat: no-repeat; background-repeat: no-repeat; background-position: center">
        <div class="hero-overlay">
            <h1>Discover Best Events in Malaysia</h1>
            <p>Concerts, Marathons, Seminars & More.</p>
            <a href="#events" class="btn-primary" style="text-decoration: none;">Browse Events</a>
        </div>
    </section>

    <!-- Search / Filter Widget (submits to events.php) -->
    <div class="container filter-section">
        <form action="events.php" method="GET" class="search-widget">
            <div class="input-group">
                <label>Looking For</label>
                <input type="text" name="search" placeholder="Event name or keyword">
            </div>
            <div class="input-group">
                <label>Location</label>
                <select name="location">
                    <option value="">All Locations</option>
                    <option value="Kuala Lumpur">Kuala Lumpur</option>
                    <option value="UiTM Machang">UiTM Machang</option>
                    <option value="Kelantan">Kelantan</option>
                    <option value="Penang">Penang</option>
                    <option value="Online">Online</option>
                </select>
            </div>
            <div class="input-group">
                <label>Category</label>
                <select name="category">
                    <option value="">All Categories</option>
                    <option value="Concert">Concert</option>
                    <option value="Sports">Sports</option>
                    <option value="Seminar">Seminar</option>
                    <option value="Workshop">Workshop</option>
                </select>
            </div>
            <button type="submit" class="btn-search-widget">Search</button>
        </form>
    </div>

    <section id="events" class="container events-section">
        <div class="section-header">
            <h2>Trending Events Right Now</h2>
            <a href="events.php" class="view-all">View All <i class="fas fa-arrow-right"></i></a>
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
                                <span class="price">From <strong>RM <?php echo number_format($event['price'], 2); ?></strong></span>
                                <a href="booking.php?eventId=<?php echo $event['id']; ?>" class="btn-buy" style="text-decoration: none; display: inline-block;">
                                    <?php echo $event['price'] == 0 ? 'Register' : 'Buy Now'; ?>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1 / -1; text-align: center; padding: 40px; color: #666;">No events available at the moment.</p>
            <?php endif; ?>
        </div>
    </section>

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