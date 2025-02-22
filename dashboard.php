<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Fetch statistics
try {
    // Total members
    $stmt = $pdo->query("SELECT COUNT(*) FROM members WHERE status = 'active'");
    $total_members = $stmt->fetchColumn();

    // Upcoming events (next 30 days)
    $stmt = $pdo->query("SELECT COUNT(*) FROM events 
                         WHERE event_date BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)");
    $upcoming_events = $stmt->fetchColumn();

    // Today's sermons
    $stmt = $pdo->query("SELECT COUNT(*) FROM daily_sermons 
                         WHERE scheduled_date = CURRENT_DATE()");
    $today_sermons = $stmt->fetchColumn();

    // Pending notifications
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications 
                         WHERE status = 'pending'");
    $pending_notifications = $stmt->fetchColumn();

} catch(PDOException $e) {
    echo "Error fetching statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Dashboard - Church Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
</head>
<body class="admin-page">
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Kabarak University</h3>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php" class="active">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="register_member.php">
                <i class="fas fa-user-plus"></i> Register Member
            </a>
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            <a href="sermons.php">
                <i class="fas fa-book-open"></i> Daily sermons
            </a>
            <a href="notifications.php">
                <i class="fas fa-bell"></i> Notifications
            </a>
            <a href="logout.php" class="logout-link">
                <i class="fas fa-sign-out-alt"></i> Logout
            </a>
        </div>
    </div>

    <!-- Main content -->
    <div class="main-content">
        <div class="top-bar">
            <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h2>
        </div>
        
        <div class="container">
            <div class="dashboard-menu">
                <h3>Dashboard Overview</h3>
                <div class="menu-grid">
                    <a href="register_member.php" class="menu-item stat-card">
                        <i class="fas fa-users"></i>
                        <h4>Total Members</h4>
                        <div class="stat-number"><?php echo $total_members; ?></div>
                        <p>Active members</p>
                    </a>
                    <a href="events.php" class="menu-item stat-card">
                        <i class="fas fa-calendar-alt"></i>
                        <h4>Upcoming Events</h4>
                        <div class="stat-number"><?php echo $upcoming_events; ?></div>
                        <p>Next 30 days</p>
                    </a>
                    <a href="sermons.php" class="menu-item stat-card">
                        <i class="fas fa-book-open"></i>
                        <h4>Today's sermons</h4>
                        <div class="stat-number"><?php echo $today_sermons; ?></div>
                        <p>Daily messages</p>
                    </a>
                    <a href="notifications.php" class="menu-item stat-card">
                        <i class="fas fa-bell"></i>
                        <h4>Notifications</h4>
                        <div class="stat-number"><?php echo $pending_notifications; ?></div>
                        <p>Pending alerts</p>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
        document.querySelector('.main-content').classList.toggle('shifted');
    }
    </script>
</body>
</html> 