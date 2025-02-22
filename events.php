<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

// Handle event creation/deletion
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'create') {
            try {
                $stmt = $pdo->prepare("INSERT INTO events (title, description, event_date, location) 
                                     VALUES (:title, :description, :event_date, :location)");
                
                $stmt->execute([
                    ':title' => $_POST['title'],
                    ':description' => $_POST['description'],
                    ':event_date' => $_POST['event_date'],
                    ':location' => $_POST['location']
                ]);
                
                $success_message = "Event created successfully!";
            } catch(PDOException $e) {
                $error_message = "Error creating event: " . $e->getMessage();
            }
        } elseif ($_POST['action'] == 'delete' && isset($_POST['event_id'])) {
            try {
                $stmt = $pdo->prepare("DELETE FROM events WHERE event_id = ?");
                $stmt->execute([$_POST['event_id']]);
                $success_message = "Event deleted successfully!";
            } catch(PDOException $e) {
                $error_message = "Error deleting event: " . $e->getMessage();
            }
        }
    }
}

// Fetch all events
try {
    $stmt = $pdo->query("SELECT * FROM events WHERE event_date >= CURRENT_DATE ORDER BY event_date ASC");
    $events = $stmt->fetchAll();
} catch(PDOException $e) {
    $error_message = "Error fetching events: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Events - Kabarak University</title>
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
            <a href="events.php" class="active">
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
            <h2>Events Management</h2>
        </div>
        
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <div class="events-layout">
                <!-- Create Event Form -->
                <div class="events-form-section">
                    <h3 class="section-title">Create New Event</h3>
                    <form method="POST" action="" class="event-form">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="form-group">
                            <label>Event Title</label>
                            <input type="text" name="title" placeholder="Enter event title" required>
                        </div>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Date & Time</label>
                                <input type="datetime-local" name="event_date" required>
                            </div>
                            
                            <div class="form-group">
                                <label>Location</label>
                                <input type="text" name="location" placeholder="Enter event location" required>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label>Description</label>
                            <textarea name="description" placeholder="Enter event description" required></textarea>
                        </div>
                        
                        <button type="submit" class="btn-submit">Create Event</button>
                    </form>
                </div>

                <!-- Events List -->
                <div class="events-list-section">
                    <h3 class="section-title">Upcoming Events</h3>
                    <div class="events-list">
                        <?php if (!empty($events)): ?>
                            <?php foreach ($events as $event): ?>
                                <div class="event-card">
                                    <div class="event-card-header">
                                        <h4><?php echo htmlspecialchars($event['title']); ?></h4>
                                        <div class="event-actions">
                                            <form method="POST" action="" style="display: inline;">
                                                <input type="hidden" name="action" value="delete">
                                                <input type="hidden" name="event_id" value="<?php echo $event['event_id']; ?>">
                                                <button type="submit" class="btn-delete" onclick="return confirm('Are you sure you want to delete this event?')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <div class="event-card-details">
                                        <p class="event-date">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('F j, Y g:i A', strtotime($event['event_date'])); ?>
                                        </p>
                                        <p class="event-location">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <?php echo htmlspecialchars($event['location']); ?>
                                        </p>
                                        <p class="event-description">
                                            <?php echo htmlspecialchars($event['description']); ?>
                                        </p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="no-events">No upcoming events scheduled.</p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html> 