<?php
session_start();

// Function to get time-based greeting
function getGreeting() {
    // Set timezone to East Africa Time
    date_default_timezone_set('Africa/Nairobi');

    $hour = date('H');
    if ($hour < 12) {
        return 'Good Morning';
    } elseif ($hour < 17) {
        return 'Good Afternoon';
    } else {
        return 'Good Evening';
    }
}

// Check if user is logged in and is a member
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'member') {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Fetch member details and relevant information
try {
    // Fetch member details
    $stmt = $pdo->prepare("
        SELECT m.*, u.username 
        FROM members m 
        JOIN users u ON m.member_id = u.member_id 
        WHERE u.user_id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $member = $stmt->fetch();

    // Fetch upcoming events
    $stmt = $pdo->query("
        SELECT * FROM events 
        WHERE event_date >= CURRENT_DATE() 
        ORDER BY event_date ASC LIMIT 3
    ");
    $upcoming_events = $stmt->fetchAll();

    // Fetch recent sermons
    $stmt = $pdo->query("
        SELECT * FROM daily_sermons 
        WHERE scheduled_date >= CURRENT_DATE() - INTERVAL 7 DAY
        ORDER BY scheduled_date DESC LIMIT 3
    ");
    $recent_sermons = $stmt->fetchAll();

} catch(PDOException $e) {
    $error_message = "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Member Dashboard - Kabarak University</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="landing-page">
    <!-- Navigation -->
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-logo">
                <a href="index.php" class="logo-link">
                    <h1><?php echo getGreeting(); ?>, <?php echo htmlspecialchars($member['first_name']); ?></h1>
                </a>
            </div>
            <div class="nav-links">
                <a href="member_dashboard.php" class="active">Dashboard</a>
                <a href="member_profile.php">My Profile</a>
                <a href="member_events.php">Events</a>
                <a href="member_sermons.php">Sermons</a>
                <a href="logout.php" class="btn-login">Logout</a>
            </div>
        </div>
    </nav>

    <div class="member-content">
        <div class="section-container">
            <div class="member-dashboard">
                <!-- Profile Summary Card -->
                <div class="dashboard-card profile-card">
                    <div class="card-header">
                        <h3><i class="fas fa-user"></i> Profile Summary</h3>
                    </div>
                    <div class="card-content">
                        <div class="profile-info">
                            <p><strong>Name:</strong> <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?></p>
                            <p><strong>Email:</strong> <?php echo htmlspecialchars($member['email']); ?></p>
                            <p><strong>Phone:</strong> <?php echo htmlspecialchars($member['phone_number']); ?></p>
                            <p><strong>Member Since:</strong> <?php echo date('F j, Y', strtotime($member['registration_date'])); ?></p>
                        </div>
                    </div>
                </div>

                <!-- Upcoming Events Card -->
                <div class="dashboard-card events-card">
                    <div class="card-header">
                        <h3><i class="fas fa-calendar"></i> Upcoming Events</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($upcoming_events)): ?>
                            <?php foreach ($upcoming_events as $event): ?>
                                <div class="event-item">
                                    <div class="event-date">
                                        <?php echo date('M d', strtotime($event['event_date'])); ?>
                                    </div>
                                    <div class="event-details">
                                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($event['location']); ?></p>
                                        <p><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($event['event_date'])); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No upcoming events</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Recent Sermons Card -->
                <div class="dashboard-card sermons-card">
                    <div class="card-header">
                        <h3><i class="fas fa-bible"></i> Recent Sermons</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recent_sermons)): ?>
                            <?php foreach ($recent_sermons as $sermon): ?>
                                <div class="sermon-item">
                                    <h4><?php echo htmlspecialchars($sermon['title']); ?></h4>
                                    <p class="sermon-date"><?php echo date('F j, Y', strtotime($sermon['scheduled_date'])); ?></p>
                                    <p class="sermon-excerpt"><?php echo substr(htmlspecialchars($sermon['message']), 0, 100) . '...'; ?></p>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-data">No recent sermons</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
