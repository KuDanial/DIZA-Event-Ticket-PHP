<?php
require_once("connection.php");

// Security check: Must be admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];

// Gather operational statistics
$user_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role IN ('attendee', 'organizer')");
$totalUsers = mysqli_fetch_assoc($user_res)['count'];

$event_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM events");
$totalEvents = mysqli_fetch_assoc($event_res)['count'];

$pub_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE status = 'published'");
$publishedEvents = mysqli_fetch_assoc($pub_res)['count'];

// Financial Metrics
$revenue_res = mysqli_query($conn, "SELECT SUM(total_price) as sum FROM bookings WHERE status = 'confirmed'");
$totalRevenue = mysqli_fetch_assoc($revenue_res)['sum'] ?? 0.00;

$sales_res = mysqli_query($conn, "SELECT SUM(quantity) as count FROM bookings WHERE status = 'confirmed'");
$totalTicketsSold = mysqli_fetch_assoc($sales_res)['count'] ?? 0;

// User Demographics
$att_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'attendee'");
$totalAttendees = mysqli_fetch_assoc($att_res)['count'];

$org_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM users WHERE role = 'organizer'");
$totalOrganizers = mysqli_fetch_assoc($org_res)['count'];

// Payment method analytics
$payment_res = mysqli_query($conn, "SELECT payment_method, COUNT(*) as count FROM bookings GROUP BY payment_method");
$paymentData = [];
while ($row = mysqli_fetch_assoc($payment_res)) {
    $paymentData[] = $row;
}

// Recent transactions list
$recent_query = "SELECT b.*, e.title as event_title, u.username FROM bookings b 
                 JOIN events e ON b.event_id = e.id 
                 JOIN users u ON b.user_id = u.id 
                 ORDER BY b.booking_date DESC LIMIT 5";
$recent_res = mysqli_query($conn, $recent_query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - UI/UX Analytics Edition</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/styledashboard.css?v=2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-header-block { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .dashboard-header-block h2 { font-size: 22px; font-weight: 700; color: #2c3e50; }
        .analytics-grid { display: grid; grid-template-columns: 2fr 1fr; gap: 25px; margin-bottom: 30px; }
        @media (max-width: 992px) { .analytics-grid { grid-template-columns: 1fr; } }
        .chart-card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .recent-table-card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-top: 30px; }
        .btn-print { background-color: #27ae60; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; box-shadow: 0 4px 10px rgba(39, 174, 96, 0.25); transition: 0.2s; }
        .btn-print:hover { background-color: #219653; transform: translateY(-1px); }
        
        @media print {
            .sidebar, .top-bar, .btn-print, .user-menu, #userDropdown { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .content-padding { padding: 20px !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            body { background: white; color: black; }
        }
    </style>
</head>
<body class="admin-theme">

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="brand">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZAET Logo" class="brand-logo">
                <h2>EventApp <span class="badge">ADMIN</span></h2>
            </div>
            <ul class="nav-links">
                <li class="active"><a href="admin-dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="menu-header">User Management</li>
                <li><a href="manage-attendees.php"><i class="fas fa-user"></i> Manage Attendees</a></li>
                <li><a href="manage-organizers.php"><i class="fas fa-briefcase"></i> Manage Organizers</a></li>
                <li><a href="manage-events.php"><i class="fas fa-calendar-alt"></i> Manage Events</a></li>
                <li class="menu-header">System</li>
                <li><a href="manage-admins.php"><i class="fas fa-user-shield"></i> Manage Admins</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="top-bar">
                <div class="page-title"><h1>Analytics Hub</h1></div>
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api?name=<?php echo urlencode($currentUser['first_name'] . '+' . $currentUser['last_name']); ?>" alt="Admin" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($currentUser['first_name'] . ' ' . $currentUser['last_name']); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="userDropdown">
                        <div class="dropdown-header"><p>Logged in as Admin</p></div>
                        <a href="admin-profile-view.php"><i class="fas fa-user-cog"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </header>

            <div class="content-padding">
                
                <div class="dashboard-header-block">
                    <div>
                        <h2>Operational Performance Analytics</h2>
                        <p style="color: #7f8c8d; font-size: 14px;">Real-time intelligence report & statistics.</p>
                    </div>
                    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Export & Print Report</button>
                </div>

                <div class="stats-grid">
                    <div class="card stat-card">
                        <div class="stat-icon purple" style="background-color:#ffe0b2; color:#fb8c00;"><i class="fas fa-dollar-sign"></i></div>
                        <div class="stat-info">
                            <h3>RM <?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Total Confirmed Revenue</p>
                        </div>
                    </div>
                    <div class="card stat-card">
                        <div class="stat-icon blue" style="background-color:#e3f2fd; color:#1e88e5;"><i class="fas fa-ticket-alt"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $totalTicketsSold; ?></h3>
                            <p>Total Tickets Sold</p>
                        </div>
                    </div>
                    <div class="card stat-card">
                        <div class="stat-icon green" style="background-color:#e8f5e9; color:#43a047;"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $publishedEvents; ?> / <?php echo $totalEvents; ?></h3>
                            <p>Active / Total Events</p>
                        </div>
                    </div>
                </div>

                <div class="analytics-grid">
                    <div class="chart-card">
                        <h3 style="margin-bottom:15px; font-size: 16px; font-weight:600;"><i class="fas fa-chart-bar" style="margin-right:8px; color:#3498db;"></i>User Roles Demographic Distribution</h3>
                        <div style="height: 300px; position: relative;">
                            <canvas id="demographicsChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-card">
                        <h3 style="margin-bottom:15px; font-size: 16px; font-weight:600;"><i class="fas fa-chart-pie" style="margin-right:8px; color:#9b59b6;"></i>Payment Method Preference</h3>
                        <div style="height: 300px; position: relative;">
                            <canvas id="paymentChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="recent-table-card">
                    <h3 style="margin-bottom: 20px; font-size:16px; font-weight:600; border-bottom: 1px solid #eee; padding-bottom: 10px;"><i class="fas fa-list" style="margin-right:8px; color:#27ae60;"></i>Recent Purchase Transactions</h3>
                    <div class="table-responsive">
                        <table class="styled-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Attendee Username</th>
                                    <th>Event Title</th>
                                    <th>Date</th>
                                    <th>Qty</th>
                                    <th>Total Paid</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_res && mysqli_num_rows($recent_res) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_res)): ?>
                                        <tr>
                                            <td>#BK-<?php echo $row['id']; ?></td>
                                            <td style="font-weight: 600;"><?php echo htmlspecialchars($row['username']); ?></td>
                                            <td><?php echo htmlspecialchars($row['event_title']); ?></td>
                                            <td><?php echo date('d M Y - H:i', strtotime($row['booking_date'])); ?></td>
                                            <td><?php echo $row['quantity']; ?></td>
                                            <td style="font-weight: bold; color: #27ae60;">RM <?php echo number_format($row['total_price'], 2); ?></td>
                                            <td><span class="status active">Confirmed</span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; color: #888;">No recent bookings found.</td>
                                    </tr>
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
            document.getElementById("userDropdown").classList.toggle("show");
        }
        
        // 1. Demographics Chart
        const ctxDemo = document.getElementById('demographicsChart').getContext('2d');
        new Chart(ctxDemo, {
            type: 'bar',
            data: {
                labels: ['Attendees', 'Organizers', 'Total System Users'],
                datasets: [{
                    label: 'Registrations',
                    data: [<?php echo $totalAttendees; ?>, <?php echo $totalOrganizers; ?>, <?php echo $totalUsers; ?>],
                    backgroundColor: ['#3498db', '#f39c12', '#2ecc71'],
                    borderRadius: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true } }
            }
        });

        // 2. Payments Chart
        const ctxPay = document.getElementById('paymentChart').getContext('2d');
        const paymentLabels = <?php echo json_encode(array_column($paymentData, 'payment_method')); ?>;
        const paymentCounts = <?php echo json_encode(array_column($paymentData, 'count')); ?>;
        new Chart(ctxPay, {
            type: 'doughnut',
            data: {
                labels: paymentLabels.map(s => s.toUpperCase()),
                datasets: [{
                    data: paymentCounts,
                    backgroundColor: ['#9b59b6', '#3498db', '#e74c3c', '#2ecc71', '#f1c40f']
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { position: 'bottom' } }
            }
        });

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
