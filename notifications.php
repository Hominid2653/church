<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Handle notification creation/deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'create') {
            try {
                $stmt = $pdo->prepare("INSERT INTO notifications (member_id, type, content) 
                                     VALUES (:member_id, :type, :content)");
                
                $stmt->execute([
                    ':member_id' => $_POST['member_id'],
                    ':type' => $_POST['type'],
                    ':content' => $_POST['content']
                ]);
                
                $success_message = "Notification created successfully!";
            } catch(PDOException $e) {
                $error_message = "Error creating notification: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['notification_id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM notifications WHERE notification_id = ?");
                $stmt->execute([$_POST['notification_id']]);
                $success_message = "Notification deleted successfully!";
            } catch(PDOException $e) {
                $error_message = "Error deleting notification: " . $e->getMessage();
            }
        }
    }
}

// Fetch all members for the dropdown
try {
    $stmt = $pdo->query("SELECT member_id, first_name, last_name FROM members WHERE status = 'active' ORDER BY first_name, last_name");
    $members = $stmt->fetchAll();
} catch(PDOException $e) {
    $error_message = "Error fetching members: " . $e->getMessage();
}

// Fetch notifications with member names
try {
    $stmt = $pdo->query("SELECT n.*, CONCAT(m.first_name, ' ', m.last_name) as member_name 
                         FROM notifications n 
                         LEFT JOIN members m ON n.member_id = m.member_id 
                         ORDER BY n.sent_at DESC");
    $notifications = $stmt->fetchAll();

    // Fetch statistics
    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE status = 'pending'");
    $pending_count = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE status = 'sent'");
    $sent_count = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications WHERE status = 'failed'");
    $failed_count = $stmt->fetchColumn();

    $stmt = $pdo->query("SELECT COUNT(*) FROM notifications");
    $total_count = $stmt->fetchColumn();

} catch(PDOException $e) {
    $error_message = "Error fetching notifications: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Notifications - Kabarak University</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>Kabarak University</h3>
        </div>
        <div class="sidebar-menu">
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="register_member.php">
                <i class="fas fa-user-plus"></i> Register Member
            </a>
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            <a href="sermons.php">
                <i class="fas fa-book-open"></i> Daily Sermons
            </a>
            <a href="notifications.php" class="active">
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
            <h2>Notifications</h2>
        </div>
        
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <!-- Statistics -->
            <div class="stats-container">
                <div class="stat-box">
                    <i class="fas fa-bell"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $total_count; ?></span>
                        <span class="stat-label">Total Notifications</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-clock"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $pending_count; ?></span>
                        <span class="stat-label">Pending</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-check-circle"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $sent_count; ?></span>
                        <span class="stat-label">Sent</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-exclamation-circle"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $failed_count; ?></span>
                        <span class="stat-label">Failed</span>
                    </div>
                </div>
            </div>

            <div class="notifications-layout">
                <!-- Create Notification Form -->
                <div class="notifications-form-section">
                    <h3 class="section-title">Create New Notification</h3>
                    <form method="POST" action="" class="notification-form">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Member</label>
                            <select name="member_id" required>
                                <option value="">Select Member</option>
                                <?php foreach ($members as $member): ?>
                                    <option value="<?php echo $member['member_id']; ?>">
                                        <?php echo htmlspecialchars($member['first_name'] . ' ' . $member['last_name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Type</label>
                            <select name="type" required>
                                <option value="event">Event</option>
                                <option value="sermon">Sermon</option>
                                <option value="general">General</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="content" placeholder="Enter notification message" required></textarea>
                        </div>
                        
                        <div class="form-group">
                            <label>Delivery Channels</label>
                            <div class="checkbox-group">
                                <label>
                                    <input type="checkbox" name="channels[]" value="email" checked> 
                                    Email
                                </label>
                                <label>
                                    <input type="checkbox" name="channels[]" value="sms"> 
                                    SMS
                                </label>
                                <label>
                                    <input type="checkbox" name="channels[]" value="whatsapp"> 
                                    WhatsApp
                                </label>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn-submit">Send Notification</button>
                    </form>
                </div>

                <!-- Notifications List -->
                <div class="notifications-list-section">
                    <h3 class="section-title">Recent Notifications</h3>
                    <div class="notifications-list">
                        <?php if (!empty($notifications)): ?>
                            <?php foreach ($notifications as $notification): ?>
                                <div class="notification-card">
                                    <div class="notification-card-header">
                                        <div class="notification-info">
                                            <span class="notification-type <?php echo $notification['type']; ?>">
                                                <?php echo ucfirst($notification['type']); ?>
                                            </span>
                                            <span class="notification-status <?php echo $notification['status']; ?>">
                                                <?php echo ucfirst($notification['status']); ?>
                                            </span>
                                        </div>
                                        <div class="notification-actions">
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="notification_id" value="<?php echo $notification['notification_id']; ?>">
                                                <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this notification?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="notification-card-details">
                                        <p class="notification-recipient">
                                            <i class="fas fa-user"></i>
                                            <?php echo htmlspecialchars($notification['member_name'] ?? 'All Members'); ?>
                                        </p>
                                        <p class="notification-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('F j, Y g:i A', strtotime($notification['sent_at'])); ?>
                                        </p>
                                        <div class="notification-content">
                                            <?php echo nl2br(htmlspecialchars($notification['content'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-notifications">No notifications found.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 