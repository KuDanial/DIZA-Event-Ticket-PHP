<?php
require_once("connection.php");

// 1. Session Verification
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'attendee') {
    header("Location: login.php?error=Please+login+as+an+Attendee+to+purchase+tickets");
    exit();
}
$currentUser = $_SESSION['user'];

// 2. Event Retrieval
if (!isset($_GET['eventId']) || empty($_GET['eventId'])) {
    header("Location: events.php?error=noevent");
    exit();
}

$eventId = (int)$_GET['eventId'];
$query = "SELECT * FROM events WHERE id = ? AND status = 'published' LIMIT 1";
$stmt = mysqli_prepare($conn, $query);
mysqli_stmt_bind_param($stmt, "i", $eventId);
mysqli_stmt_execute($stmt);
$eventResult = mysqli_stmt_get_result($stmt);

if (!$event = mysqli_fetch_assoc($eventResult)) {
    header("Location: events.php?error=invalid");
    exit();
}

// 3. Handle POST Ticket Purchase Transaction
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $quantity = (int)$_POST['quantity'];
    $paymentMethod = $_POST['paymentMethod'];
    
    if ($quantity <= 0) {
        header("Location: booking.php?eventId=$eventId&error=invalid_quantity");
        exit();
    }
    
    // Check ticket capacity
    if ($event['capacity'] < $quantity) {
        header("Location: booking.php?eventId=$eventId&error=insufficient_capacity");
        exit();
    }
    
    $processing_fee = 5.00;
    $totalPrice = ($event['price'] * $quantity) + $processing_fee;
    
    // Start Transaction for accuracy
    mysqli_begin_transaction($conn);
    
    try {
        // Create booking
        $insertQuery = "INSERT INTO bookings (user_id, event_id, quantity, total_price, payment_method, status) VALUES (?, ?, ?, ?, ?, 'confirmed')";
        $stmt_book = mysqli_prepare($conn, $insertQuery);
        mysqli_stmt_bind_param($stmt_book, "iiids", $currentUser['id'], $eventId, $quantity, $totalPrice, $paymentMethod);
        mysqli_stmt_execute($stmt_book);
        
        // Decrement capacity
        $new_capacity = $event['capacity'] - $quantity;
        $updateQuery = "UPDATE events SET capacity = ? WHERE id = ?";
        $stmt_event = mysqli_prepare($conn, $updateQuery);
        mysqli_stmt_bind_param($stmt_event, "ii", $new_capacity, $eventId);
        mysqli_stmt_execute($stmt_event);
        
        mysqli_commit($conn);
        header("Location: tickets.php?success=ticket_booked");
        exit();
    } catch (Exception $e) {
        mysqli_rollback($conn);
        header("Location: booking.php?eventId=$eventId&error=transaction_failed");
        exit();
    }
}

$dt = new DateTime($event['event_date']);
$formattedDate = $dt->format('d M Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DIZA Event Ticket | Checkout</title>
    <link rel="stylesheet" href="css/style.css?v=2">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>

    <?php include("includes/header.php"); ?>

    <div class="booking-container">
        
        <main class="booking-main">
            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error" style="margin-bottom: 20px;">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($_GET['error']); ?>
                </div>
            <?php endif; ?>

            <form id="paymentForm" action="booking.php?eventId=<?php echo $eventId; ?>" method="POST">
                <input type="hidden" name="eventId" value="<?php echo $event['id']; ?>">
                <input type="hidden" name="totalPrice" id="hiddenTotalPrice" value="<?php echo $event['price']; ?>">
                
                <div class="booking-section">
                    <h2>1. Select Tickets</h2>
                    <div class="ticket-row">
                        <div class="ticket-info">
                            <h4>Entry Ticket</h4>
                            <p class="ticket-price">RM <?php echo number_format($event['price'], 2); ?> / pax</p>
                        </div>
                        <div class="ticket-quantity">
                            <button type="button" class="qty-btn" onclick="updateQty(-1)">-</button>
                            <input type="text" id="qty-general" name="quantity" class="qty-input" value="1" readonly>
                            <button type="button" class="qty-btn" onclick="updateQty(1)">+</button>
                        </div>
                    </div>
                </div>

                <div class="booking-section">
                    <h2>2. Attendee Details</h2>
                    <div class="payment-input-group">
                        <label>Full Name</label>
                        <input type="text" value="<?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?>" style="background:#f9f9f9;" readonly>
                    </div>
                    <div class="payment-input-group">
                        <label>Email Address</label>
                        <input type="email" value="<?php echo htmlspecialchars($currentUser['email']); ?>" style="background:#f9f9f9;" readonly>
                    </div>
                    <div class="payment-input-group">
                        <label>Phone Number</label>
                        <input type="text" name="phoneNumber" value="<?php echo htmlspecialchars($currentUser['phone_number']); ?>" readonly style="background:#f9f9f9;">
                    </div>
                </div>

                <div class="booking-section">
                    <h2>3. Payment Method</h2>
                    
                    <div class="payment-options">
                        <label class="payment-card active" onclick="selectMethod('card')">
                            <input type="radio" name="paymentMethod" value="card" checked>
                            <i class="fas fa-credit-card"></i>
                            <span>Credit / Debit Card</span>
                        </label>

                        <label class="payment-card" onclick="selectMethod('fpx')">
                            <input type="radio" name="paymentMethod" value="fpx">
                            <i class="fas fa-university"></i>
                            <span>FPX Online Banking</span>
                        </label>

                        <label class="payment-card" onclick="selectMethod('ewallet')">
                            <input type="radio" name="paymentMethod" value="ewallet">
                            <i class="fas fa-wallet"></i>
                            <span>GrabPay / TnG E-Wallet</span>
                        </label>
                    </div>

                    <div id="card-fields">
                        <div class="payment-input-group">
                            <label>Card Number <span style="color:red">*</span></label>
                            <input type="text" class="card-required" placeholder="0000 0000 0000 0000" required>
                        </div>
                        <div style="display: flex; gap: 15px;">
                            <div class="payment-input-group" style="flex:1;">
                                <label>Expiry <span style="color:red">*</span></label>
                                <input type="text" class="card-required" placeholder="MM/YY" required>
                            </div>
                            <div class="payment-input-group" style="flex:1;">
                                <label>CVV <span style="color:red">*</span></label>
                                <input type="text" class="card-required" placeholder="123" required>
                            </div>
                        </div>
                        <div class="payment-input-group">
                            <label>Cardholder Name <span style="color:red">*</span></label>
                            <input type="text" class="card-required" placeholder="Name on Card" required>
                        </div>
                    </div>

                    <div id="fpx-fields" class="hidden" style="display:none;">
                        <div class="payment-input-group">
                            <label>Select Bank <span style="color:red">*</span></label>
                            <select id="fpx-bank">
                                <option value="" disabled selected>Select your bank</option>
                                <option>Maybank2u</option>
                                <option>CIMB Clicks</option>
                                <option>Bank Islam</option>
                                <option>RHB Online</option>
                                <option>Public Bank</option>
                            </select>
                        </div>
                        <p class="info-text">You will be redirected to your bank's website to complete the secure transaction.</p>
                    </div>

                    <div id="ewallet-fields" class="hidden" style="display:none;">
                        <div class="payment-input-group">
                            <label>Select Wallet <span style="color:red">*</span></label>
                            <select id="ewallet-wallet">
                                <option value="" disabled selected>Select E-Wallet</option>
                                <option>GrabPay</option>
                                <option>Touch 'n Go eWallet</option>
                                <option>Boost</option>
                            </select>
                        </div>
                        <p class="info-text">
                            <i class="fas fa-qrcode" style="font-size: 24px; margin-bottom: 10px; display:block;"></i>
                            A QR Code will be generated for you to scan and pay.
                        </p>
                    </div>
                </div>
            </form>
        </main>

        <aside class="booking-sidebar">
            <h2>Order Summary</h2>
            
            <div class="event-summary-header">
                <div class="summary-details">
                    <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                    <p><i class="far fa-calendar-alt"></i> <?php echo $formattedDate; ?></p>
                    <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['venue_name']); ?></p>
                </div>
            </div>

            <div class="price-breakdown">
                <div class="price-row">
                    <span>Tickets (x<span id="summary-qty">1</span>)</span>
                    <span>RM <span id="summary-price"><?php echo number_format($event['price'], 2); ?></span></span>
                </div>
                <div class="price-row">
                    <span>Processing Fee</span>
                    <span>RM 5.00</span>
                </div>
                
                <div class="price-row total">
                    <span>Total Amount</span>
                    <span style="color: var(--primary-yellow); font-size: 22px;">RM <span id="total-amount"><?php echo number_format($event['price'] + 5, 2); ?></span></span>
                </div>
            </div>

            <button type="button" class="btn-checkout" onclick="document.getElementById('paymentForm').submit()">Pay Now</button>
            <p style="text-align: center; font-size: 12px; color: #888; margin-top: 15px;">
                <i class="fas fa-lock"></i> Secure Payment Encrypted
            </p>
        </aside>

    </div>

    <footer>
        <div class="container footer-content">
            <div class="footer-col brand-col">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZA Event Ticket" class="footer-logo">
                <p>Your one-stop platform for all event ticketing needs.</p>
            </div>
        </div>
    </footer>

    <script>
    var pricePerPax = <?php echo $event['price']; ?>;
    
    function updateQty(delta) {
        var qtyInput = document.getElementById("qty-general");
        var val = parseInt(qtyInput.value) + delta;
        if (val < 1) val = 1;
        if (val > <?php echo $event['capacity']; ?>) {
            alert("Cannot exceed available capacity: <?php echo $event['capacity']; ?>");
            return;
        }
        qtyInput.value = val;
        
        // Update summary
        document.getElementById("summary-qty").innerText = val;
        var ticketPrice = pricePerPax * val;
        document.getElementById("summary-price").innerText = ticketPrice.toFixed(2);
        document.getElementById("total-amount").innerText = (ticketPrice + 5).toFixed(2);
        document.getElementById("hiddenTotalPrice").value = (ticketPrice + 5).toFixed(2);
    }
    
    function selectMethod(method) {
        // Toggle active styling
        var cards = document.querySelectorAll(".payment-card");
        cards.forEach(c => c.classList.remove("active"));
        event.currentTarget.classList.add("active");
        
        // Hide all fields
        document.getElementById("card-fields").style.display = "none";
        document.getElementById("fpx-fields").style.display = "none";
        document.getElementById("ewallet-fields").style.display = "none";
        
        // Disable requirements on other fields
        document.querySelectorAll(".card-required").forEach(input => input.required = false);
        document.getElementById("fpx-bank").required = false;
        document.getElementById("ewallet-wallet").required = false;
        
        if (method === 'card') {
            document.getElementById("card-fields").style.display = "block";
            document.querySelectorAll(".card-required").forEach(input => input.required = true);
        } else if (method === 'fpx') {
            document.getElementById("fpx-fields").style.display = "block";
            document.getElementById("fpx-bank").required = true;
        } else if (method === 'ewallet') {
            document.getElementById("ewallet-fields").style.display = "block";
            document.getElementById("ewallet-wallet").required = true;
        }
    }
    </script>
</body>
</html>