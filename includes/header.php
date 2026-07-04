<?php
// Check if user is logged in
$headerUser = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<header class="navbar">
    <div class="container nav-container">
        <a href="index.php" class="logo">
            <img src="images/DIZAET_Logo.png" alt="DIZA Event Ticket">
        </a>

        <div class="search-bar" style="margin: 0 20px;">
            <form action="events.php" method="GET" style="display: flex; width: 100%;">
                <input type="text" name="search" placeholder="Search for events, concerts, activities..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
        </div>

        <nav class="nav-links">
            <a href="index.php">Home</a>
            <a href="events.php">Events</a>
            <a href="about.php">About Us</a>
            <a href="help.php">Help</a>
        </nav>

        <div class="auth-buttons">
            <?php if ($headerUser) { ?>
                <!-- User is logged in - show dropdown -->
                <div class="user-profile-menu" style="position: relative;">
                    <button class="user-btn" onclick="toggleHeaderDropdown(event)" style="display: flex; align-items: center; gap: 8px; background: none; border: none; cursor: pointer; padding: 8px 12px;">
                        <div class="user-avatar" style="width: 32px; height: 32px; border-radius: 50%; background: #e0e0e0; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                            <?php 
                            $first_char = isset($headerUser['first_name'][0]) ? $headerUser['first_name'][0] : '';
                            $last_char = isset($headerUser['last_name'][0]) ? $headerUser['last_name'][0] : '';
                            echo htmlspecialchars(strtoupper($first_char . $last_char)); 
                            ?>
                        </div>
                        <span style="color: #333; font-weight: 500; font-size: 14px;"><?php echo htmlspecialchars($headerUser['first_name']); ?></span>
                        <i class="fas fa-chevron-down" style="font-size: 12px; color: #888;"></i>
                    </button>
                    <div class="dropdown-menu" id="headerDropdown" style="display: none; position: absolute; top: 100%; right: 0; background: white; border: 1px solid #ddd; border-radius: 4px; min-width: 180px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); z-index: 1000;">
                        <div style="padding: 12px 16px; border-bottom: 1px solid #eee; font-size: 12px; color: #666;">
                            Logged in as <?php echo strtoupper($headerUser['role']); ?>
                        </div>
                        <a href="<?php echo ($headerUser['role'] === 'admin') ? 'admin-profile-view.php' : (($headerUser['role'] === 'organizer') ? 'organizer-profile-view.php' : 'attendee-profile-view.php'); ?>" 
                           style="display: block; padding: 10px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;">
                            <i class="fas fa-user"></i> My Profile
                        </a>
                        <?php if ($headerUser['role'] === 'admin') { ?>
                            <a href="admin-dashboard.php" style="display: block; padding: 10px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;">
                                <i class="fas fa-tachometer-alt"></i> Admin Dashboard
                            </a>
                        <?php } elseif ($headerUser['role'] === 'organizer') { ?>
                            <a href="organizer-dashboard.php" style="display: block; padding: 10px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;">
                                <i class="fas fa-tachometer-alt"></i> Organizer Dashboard
                            </a>
                        <?php } ?>
                        
                        <?php if ($headerUser['role'] !== 'admin') { ?>
                        <a href="tickets.php" style="display: block; padding: 10px 16px; color: #333; text-decoration: none; border-bottom: 1px solid #eee; transition: background 0.2s;">
                            <i class="fas fa-ticket-alt"></i> My Tickets
                        </a>
                        <?php } ?>
                        <a href="logout.php" style="display: block; padding: 10px 16px; color: #d9534f; text-decoration: none; transition: background 0.2s;">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </div>
                </div>
            <?php } else { ?>
                <!-- User is not logged in -->
                <a href="login.php" class="btn-login">Log In</a>
                <a href="register.php" class="btn-register">Register</a>
            <?php } ?>
        </div>
    </div>
</header>

<script>
function toggleHeaderDropdown(event) {
    event.stopPropagation();
    var dropdown = document.getElementById('headerDropdown');
    if (dropdown) {
        dropdown.style.display = dropdown.style.display === 'none' ? 'block' : 'none';
    }
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    var dropdown = document.getElementById('headerDropdown');
    if (dropdown && !event.target.closest('.user-profile-menu')) {
        dropdown.style.display = 'none';
    }
});
</script>
