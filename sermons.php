<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Handle sermon creation/deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'create') {
            try {
                $stmt = $pdo->prepare("INSERT INTO daily_sermons (title, message, scheduled_date) 
                                     VALUES (:title, :message, :scheduled_date)");
                
                $stmt->execute([
                    ':title' => $_POST['title'],
                    ':message' => $_POST['message'],
                    ':scheduled_date' => $_POST['scheduled_date']
                ]);
                
                $success_message = "Daily sermon created successfully!";
            } catch(PDOException $e) {
                $error_message = "Error creating sermon: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['sermon_id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM daily_sermons WHERE sermon_id = ?");
                $stmt->execute([$_POST['sermon_id']]);
                $success_message = "Sermon deleted successfully!";
            } catch(PDOException $e) {
                $error_message = "Error deleting sermon: " . $e->getMessage();
            }
        }
    }
}

// Fetch all sermons
try {
    $stmt = $pdo->query("SELECT * FROM daily_sermons WHERE scheduled_date >= CURRENT_DATE ORDER BY scheduled_date ASC");
    $sermons = $stmt->fetchAll();
} catch(PDOException $e) {
    $error_message = "Error fetching sermons: " . $e->getMessage();
}

// Fetch statistics
try {
    // Total sermons count
    $stmt = $pdo->query("SELECT COUNT(*) FROM daily_sermons");
    $total_sermons = $stmt->fetchColumn();
    
    // Today's sermons
    $stmt = $pdo->query("SELECT COUNT(*) FROM daily_sermons WHERE scheduled_date = CURRENT_DATE");
    $today_sermons = $stmt->fetchColumn();
    
    // This week's sermons
    $stmt = $pdo->query("SELECT COUNT(*) FROM daily_sermons 
                         WHERE scheduled_date BETWEEN DATE_SUB(CURRENT_DATE, INTERVAL 7 DAY) 
                         AND CURRENT_DATE");
    $weekly_sermons = $stmt->fetchColumn();
    
    // Upcoming sermons
    $stmt = $pdo->query("SELECT COUNT(*) FROM daily_sermons WHERE scheduled_date > CURRENT_DATE");
    $upcoming_sermons = $stmt->fetchColumn();
    
} catch(PDOException $e) {
    $error_message = "Error fetching statistics: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Daily Sermons - Church Management System</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <h3>West-Side Church</h3>
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
            <a href="sermons.php" class="active">
                <i class="fas fa-book-open"></i> Daily Sermons
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
            <h2>Daily Sermons</h2>
        </div>
        
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="stats-container">
                <div class="stat-box">
                    <i class="fas fa-book-reader"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $total_sermons; ?></span>
                        <span class="stat-label">Total Sermons</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-calendar-day"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $today_sermons; ?></span>
                        <span class="stat-label">Today's Sermons</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-calendar-week"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $weekly_sermons; ?></span>
                        <span class="stat-label">This Week</span>
                    </div>
                </div>
                <div class="stat-box">
                    <i class="fas fa-calendar-alt"></i>
                    <div class="stat-info">
                        <span class="stat-value"><?php echo $upcoming_sermons; ?></span>
                        <span class="stat-label">Upcoming</span>
                    </div>
                </div>
            </div>

            <div class="sermons-layout">
                <!-- Create Sermon Form -->
                <div class="sermons-form-section">
                    <h3 class="section-title">Create New Sermon</h3>
                    <form method="POST" action="" class="sermon-form">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Title</label>
                            <input type="text" name="title" placeholder="Enter sermon title" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Scheduled Date</label>
                            <input type="date" name="scheduled_date" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Message</label>
                            <textarea name="message" placeholder="Enter sermon message" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">Create Sermon</button>
                    </form>
                </div>

                <!-- Sermons List -->
                <div class="sermons-list-section">
                    <h3 class="section-title">Upcoming Sermons</h3>
                    <div class="sermons-list">
                        <?php if (!empty($sermons)): ?>
                            <?php foreach ($sermons as $sermon): ?>
                                <div class="sermon-card">
                                    <div class="sermon-card-header">
                                        <h4><?php echo htmlspecialchars($sermon['title']); ?></h4>
                                        <div class="sermon-actions">
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="sermon_id" value="<?php echo $sermon['sermon_id']; ?>">
                                                <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this sermon?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="sermon-card-details">
                                        <p class="sermon-date">
                                            <i class="fas fa-calendar-day"></i>
                                            <?php echo date('F j, Y', strtotime($sermon['scheduled_date'])); ?>
                                        </p>
                                        <div class="sermon-message">
                                            <?php echo nl2br(htmlspecialchars($sermon['message'])); ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-sermons">No upcoming sermons scheduled.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 