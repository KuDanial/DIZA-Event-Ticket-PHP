<?php
require_once("connection.php");

// Security check: Must be organizer
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'organizer') {
    header("Location: login.php");
    exit();
}
$currentUser = $_SESSION['user'];
$orgId = $currentUser['id'];

// Get Organizer profile
$profile_query = "SELECT * FROM organizer_details WHERE user_id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $profile_query);
mysqli_stmt_bind_param($stmt, "i", $orgId);
mysqli_stmt_execute($stmt);
$orgProfile = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

// Gather organizer operational statistics
$event_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE organizer_id = $orgId");
$totalEvents = mysqli_fetch_assoc($event_res)['count'];

$pub_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE organizer_id = $orgId AND status = 'published'");
$publishedEvents = mysqli_fetch_assoc($pub_res)['count'];

$draft_res = mysqli_query($conn, "SELECT COUNT(*) as count FROM events WHERE organizer_id = $orgId AND status = 'draft'");
$draftEvents = mysqli_fetch_assoc($draft_res)['count'];

// Revenue Statistics
$rev_query = "SELECT SUM(b.total_price) as sum FROM bookings b 
              JOIN events e ON b.event_id = e.id 
              WHERE e.organizer_id = $orgId AND b.status = 'confirmed'";
$rev_res = mysqli_query($conn, $rev_query);
$totalRevenue = mysqli_fetch_assoc($rev_res)['sum'] ?? 0.00;

// Ticket Sales Volume
$tickets_query = "SELECT SUM(b.quantity) as sum FROM bookings b 
                  JOIN events e ON b.event_id = e.id 
                  WHERE e.organizer_id = $orgId AND b.status = 'confirmed'";
$tickets_res = mysqli_query($conn, $tickets_query);
$totalTicketsSold = mysqli_fetch_assoc($tickets_res)['sum'] ?? 0;

// Chart Analytics: Sales by Event
$chart_query = "SELECT e.title, SUM(b.quantity) as sold_count 
                FROM events e 
                LEFT JOIN bookings b ON e.id = b.event_id AND b.status = 'confirmed'
                WHERE e.organizer_id = $orgId 
                GROUP BY e.id";
$chart_res = mysqli_query($conn, $chart_query);
$chartLabels = [];
$chartData = [];
while ($row = mysqli_fetch_assoc($chart_res)) {
    $chartLabels[] = $row['title'];
    $chartData[] = (int)$row['sold_count'];
}

// Recent transaction feed
$recent_query = "SELECT b.*, e.title, u.first_name, u.last_name FROM bookings b 
                 JOIN events e ON b.event_id = e.id 
                 JOIN users u ON b.user_id = u.id 
                 WHERE e.organizer_id = $orgId 
                 ORDER BY b.booking_date DESC LIMIT 5";
$recent_res = mysqli_query($conn, $recent_query);

$company_name = !empty($orgProfile['company_name']) ? $orgProfile['company_name'] : $currentUser['first_name'] . ' ' . $currentUser['last_name'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Organizer Dashboard - UI/UX Analytics Edition</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/styledashboard.css?v=2">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .dashboard-header-block { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; }
        .dashboard-header-block h2 { font-size: 22px; font-weight: 700; color: #30336b; }
        .analytics-grid { display: grid; grid-template-columns: 1fr; gap: 25px; margin-bottom: 30px; }
        .chart-card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); }
        .recent-table-card { background: white; border-radius: 8px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-top: 30px; }
        .btn-print { background-color: #4834d4; color: white; border: none; padding: 10px 20px; border-radius: 6px; cursor: pointer; display: inline-flex; align-items: center; gap: 8px; font-weight: 600; box-shadow: 0 4px 10px rgba(72, 52, 212, 0.25); transition: 0.2s; }
        .btn-print:hover { background-color: #30336b; transform: translateY(-1px); }
        
        @media print {
            .sidebar, .top-bar, .btn-print, .user-menu, #orgDropdown, .welcome-banner { display: none !important; }
            .main-content { margin-left: 0 !important; padding: 0 !important; }
            .content-padding { padding: 20px !important; }
            .card { box-shadow: none !important; border: 1px solid #ddd !important; }
            body { background: white; color: black; }
        }
    </style>
</head>
<body class="org-theme">

    <div class="dashboard-container">
        
        <aside class="sidebar">
            <div class="brand">
                <img src="images/DIZAET_LogoWhite.png" alt="DIZAET Logo" class="brand-logo">
                <h2>EventApp <span class="badge">ORG</span></h2>
            </div>
            
            <ul class="nav-links">
                <li class="active">
                    <a href="organizer-dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                </li>
                
                <li class="menu-header">My Events</li>
                <li><a href="organizer-events-addNew.php"><i class="fas fa-plus-circle"></i> Create New Event</a></li>
                <li><a href="organizer-events.php"><i class="fas fa-calendar-alt"></i> My Events List</a></li>
                
                <li class="menu-header">Account</li>
                <li><a href="organizer-profile-view.php"><i class="fas fa-user-cog"></i> Org Profile</a></li>
            </ul>
        </aside>

        <main class="main-content">
            
            <header class="top-bar">
                <div class="page-title"><h1>Dashboard Overview</h1></div>
                <div class="user-menu">
                    <button class="user-btn" onclick="toggleDropdown()">
                        <img src="https://ui-avatars.com/api?name=<?php echo urlencode($company_name); ?>" alt="Org" class="avatar">
                        <span class="username"><?php echo htmlspecialchars($company_name); ?></span>
                        <i class="fas fa-chevron-down"></i>
                    </button>
                    <div class="dropdown-content" id="orgDropdown">
                        <div class="dropdown-header"><p>Logged in as Organizer</p></div>
                        <a href="organizer-profile-view.php"><i class="fas fa-user-cog"></i> My Profile</a>
                        <div class="divider"></div>
                        <a href="logout.php" class="logout"><i class="fas fa-sign-out-alt"></i> Log Out</a>
                    </div>
                </div>
            </header>

            <div class="content-padding">
                
                <div class="welcome-banner">
                    <h2>Welcome back, <?php echo htmlspecialchars($company_name); ?>! 👋</h2>
                    <p>Here's what's happening with your events today.</p>
                </div>

                <div class="dashboard-header-block">
                    <div>
                        <h2>Event Performance Intelligence</h2>
                        <p style="color: #7f8c8d; font-size: 14px;">Real-time attendee bookings & ticketing sales stats.</p>
                    </div>
                    <button onclick="window.print()" class="btn-print"><i class="fas fa-print"></i> Export Analytics Report</button>
                </div>

                <div class="stats-grid">
                    <div class="stat-card">
                        <div class="stat-icon icon-revenue" style="background-color:#e8f5e9; color:#2e7d32;"><i class="fas fa-money-bill-wave"></i></div>
                        <div class="stat-info">
                            <h3>RM <?php echo number_format($totalRevenue, 2); ?></h3>
                            <p>Ticket Sales Revenue</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-sales" style="background-color:#e3f2fd; color:#1565c0;"><i class="fas fa-ticket-alt"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $totalTicketsSold; ?></h3>
                            <p>Total Tickets Confirmed</p>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-icon icon-events" style="background-color:#f3e5f5; color:#7b1fa2;"><i class="fas fa-calendar-check"></i></div>
                        <div class="stat-info">
                            <h3><?php echo $publishedEvents; ?> / <?php echo $totalEvents; ?></h3>
                            <p>Published / Total Events</p>
                        </div>
                    </div>
                </div>

                <div class="analytics-grid">
                    <div class="chart-card">
                        <h3 style="margin-bottom:15px; font-size:16px; font-weight:600;"><i class="fas fa-chart-line" style="color:#4834d4; margin-right:8px;"></i>Confirmed Ticket Bookings per Event</h3>
                        <div style="height:320px; position:relative;">
                            <canvas id="salesChart"></canvas>
                        </div>
                    </div>
                </div>

                <div class="recent-table-card">
                    <h3 style="margin-bottom: 20px; font-size:16px; font-weight:600; border-bottom:1px solid #eee; padding-bottom: 10px;"><i class="fas fa-history" style="color:#6ab04c; margin-right:8px;"></i>Recent Bookings Feed</h3>
                    <div class="table-responsive">
                        <table class="styled-table" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Attendee Name</th>
                                    <th>Event Title</th>
                                    <th>Date</th>
                                    <th>Tickets Purchased</th>
                                    <th>Revenue Generated</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($recent_res && mysqli_num_rows($recent_res) > 0): ?>
                                    <?php while ($row = mysqli_fetch_assoc($recent_res)): ?>
                                        <tr>
                                            <td>#BK-<?php echo $row['id']; ?></td>
                                            <td style="font-weight:600;"><?php echo htmlspecialchars($row['first_name'] . ' ' . $row['last_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['title']); ?></td>
                                            <td><?php echo date('d M Y - H:i', strtotime($row['booking_date'])); ?></td>
                                            <td><?php echo $row['quantity']; ?></td>
                                            <td style="font-weight:bold; color:#2e7d32;">RM <?php echo number_format($row['total_price'], 2); ?></td>
                                            <td><span class="status active">Confirmed</span></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center; padding: 20px; color:#888;">No ticket bookings found for your events.</td>
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
            document.getElementById("orgDropdown").classList.toggle("show");
        }
        
        // Render Ticket Sales Chart
        const ctxSales = document.getElementById('salesChart').getContext('2d');
        const salesLabels = <?php echo json_encode($chartLabels); ?>;
        const salesCounts = <?php echo json_encode($chartData); ?>;
        new Chart(ctxSales, {
            type: 'bar',
            data: {
                labels: salesLabels,
                datasets: [{
                    label: 'Tickets Booked',
                    data: salesCounts,
                    backgroundColor: '#4834d4',
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
