<?php
require_once("connection.php");

// Session Check
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'attendee') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Retrieve Booking History
$userId = $currentUser['id'];
$query = "SELECT b.*, e.title, e.image_path, e.event_date, e.venue_name FROM bookings b 
          JOIN events e ON b.event_id = e.id 
          WHERE b.user_id = ? ORDER BY b.booking_date DESC";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$bookingList = [];
if ($result) {
    while ($row = mysqli_fetch_assoc($result)) {
        $bookingList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Ticket History | DIZA Event Ticket</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <div class="history-container">
        <div class="page-header">
            <h2>My Ticket History</h2>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" style="margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> Ticket purchased and confirmed successfully!
            </div>
        <?php endif; ?>

        <?php if (!empty($bookingList)): ?>
            <?php foreach ($bookingList as $booking): 
                $dt = new DateTime($booking['event_date']);
                $formattedDate = $dt->format('d M Y | H:i');
            ?>
                <div class="booking-section">
                    <div class="ticket-header">
                        <span class="booking-id">Booking ID: #BK-<?php echo $booking['id']; ?></span>
                        <span class="status-badge status-<?php echo $booking['status'] === 'confirmed' ? 'confirmed' : 'past'; ?>">
                            <?php echo htmlspecialchars(strtoupper($booking['status'])); ?>
                        </span>
                    </div>
                    
                    <div class="ticket-body">
                        <div class="ticket-details">
                            <h3><?php echo htmlspecialchars($booking['title']); ?></h3>
                            <div class="ticket-meta" style="margin-top: 8px;">
                                <i class="far fa-calendar-alt"></i> <?php echo $formattedDate; ?>
                            </div>
                            <div class="ticket-meta">
                                <i class="fas fa-ticket-alt"></i> <?php echo $booking['quantity']; ?>x Ticket<?php echo $booking['quantity'] > 1 ? 's' : ''; ?>
                            </div>
                        </div>
                        <div class="ticket-price-block" style="text-align: right;">
                            <div style="font-size: 12px; color: #888;">Total Paid</div>
                            <div style="font-size: 20px; font-weight: bold; color: #2e7d32;">RM <?php echo number_format($booking['total_price'], 2); ?></div>
                        </div>
                    </div>

                    <div class="ticket-footer">
                        <div style="font-size: 13px; color: #666;">
                            <i class="fas fa-check-circle" style="color: #2e7d32;"></i> Payment Successful via <?php echo strtoupper(htmlspecialchars($booking['payment_method'])); ?>
                        </div>
                        <div>
                            <button class="btn-buy" style="background: transparent; border: 1px solid #333;" onclick="alert('Invoice downloaded successfully.')">Download Invoice</button>
                            <button class="btn-primary" style="margin-left: 10px;" onclick="alert('Booking ID: BK-<?php echo $booking['id']; ?>. Present this booking code at registration.')">View QR Code</button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #666;">
                <i class="fas fa-ticket-alt" style="font-size: 48px; margin-bottom: 20px; opacity: 0.3;"></i>
                <p style="font-size: 18px;">No booking history found.</p>
                <a href="events.php" class="btn-primary" style="margin-top: 20px; display: inline-block; text-decoration:none;">Browse Events</a>
            </div>
        <?php endif; ?>

    </div>

    <footer>
        <div class="container footer-content">
            <div class="footer-col brand-col">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZA Event Ticket" class="footer-logo">
                <p>Your one-stop platform for all event ticketing needs.</p>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; 2026 DIZAZ Group. All Rights Reserved.</p>
        </div>
    </footer>
</body>
</html>