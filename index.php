<?php
session_start();

// If user is already logged in, redirect to appropriate dashboard
if (isset($_SESSION['user_id'])) {
    switch($_SESSION['role']) {
        case 'admin':
        case 'staff':
            header('Location: dashboard.php');
            exit();
        case 'member':
            header('Location: member_dashboard.php');
            exit();
    }
}

require_once 'config/database.php';

// Fetch upcoming events
try {
    $stmt = $pdo->query("SELECT * FROM events 
                         WHERE event_date >= CURRENT_DATE() 
                         ORDER BY event_date ASC LIMIT 3");
    $upcoming_events = $stmt->fetchAll();

    // Fetch today's sermon
    $stmt = $pdo->query("SELECT * FROM daily_sermons 
                         WHERE scheduled_date = CURRENT_DATE() 
                         LIMIT 1");
    $today_sermon = $stmt->fetch();
} catch(PDOException $e) {
    $error_message = "Error fetching data: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Welcome to Kabarak University</title>
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
                    <h1>Kabarak University</h1>
                </a>
            </div>
            <div class="nav-links">
                <a href="#about">About Us</a>
                <a href="#events">Events</a>
                <a href="#sermons">Daily Sermon</a>
                <a href="#contact">Contact</a>
                <a href="login.php" class="btn-login">Member Login</a>
            </div>
            <button class="mobile-menu-btn">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Hero Section -->
    <header class="hero-section">
        <div class="hero-content">
            <h1>Welcome to Kabarak University</h1>
            <p>Join us in worship and fellowship as we grow together in faith</p>
            <div class="hero-buttons">
                <a href="#events" class="btn-primary">Upcoming Events</a>
                <a href="#contact" class="btn-secondary">Get in Touch</a>
            </div>
        </div>
    </header>

    <!-- About Section -->
    <section id="about" class="about-section">
        <div class="section-container">
            <h2>About Our Church</h2>
            <div class="about-grid">
                <div class="about-card">
                    <i class="fas fa-heart"></i>
                    <h3>Our Mission</h3>
                    <p>To spread love, hope, and faith throughout our community</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-hands-helping"></i>
                    <h3>Community</h3>
                    <p>Building strong relationships through fellowship and support</p>
                </div>
                <div class="about-card">
                    <i class="fas fa-pray"></i>
                    <h3>Worship</h3>
                    <p>Meaningful worship services that inspire and uplift</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Events Section -->
    <section id="events" class="events-section">
        <div class="section-container">
            <h2>Upcoming Events</h2>
            <div class="events-grid">
                <?php if (!empty($upcoming_events)): ?>
                    <?php foreach ($upcoming_events as $event): ?>
                        <div class="event-card">
                            <div class="event-date">
                                <span class="day"><?php echo date('d', strtotime($event['event_date'])); ?></span>
                                <span class="month"><?php echo date('M', strtotime($event['event_date'])); ?></span>
                            </div>
                            <div class="event-details">
                                <h3><?php echo htmlspecialchars($event['title']); ?></h3>
                                <p class="event-time">
                                    <i class="fas fa-clock"></i>
                                    <?php echo date('g:i A', strtotime($event['event_date'])); ?>
                                </p>
                                <p class="event-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?php echo htmlspecialchars($event['location']); ?>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="no-events">No upcoming events scheduled</p>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Today's Sermon Section -->
    <section id="sermons" class="sermon-section">
        <div class="section-container">
            <h2>Today's Sermon</h2>
            <?php if ($today_sermon): ?>
                <div class="sermon-card">
                    <h3><?php echo htmlspecialchars($today_sermon['title']); ?></h3>
                    <div class="sermon-content">
                        <?php echo nl2br(htmlspecialchars($today_sermon['message'])); ?>
                    </div>
                </div>
            <?php else: ?>
                <p class="no-sermon">Today's sermon will be posted soon</p>
            <?php endif; ?>
        </div>
    </section>

    <!-- Contact Section -->
    <section id="contact" class="contact-section">
        <div class="section-container">
            <h2>Contact Us</h2>
            <div class="contact-grid">
                <div class="contact-info">
                    <div class="contact-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <div>
                            <h3>Location</h3>
                            <p>123 Church Street<br>City, State 12345</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-clock"></i>
                        <div>
                            <h3>Service Times</h3>
                            <p>Sunday: 9:00 AM & 11:00 AM<br>Wednesday: 7:00 PM</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-phone"></i>
                        <div>
                            <h3>Phone</h3>
                            <p>(555) 123-4567</p>
                        </div>
                    </div>
                    <div class="contact-item">
                        <i class="fas fa-envelope"></i>
                        <div>
                            <h3>Email</h3>
                            <p>info@kabarak.ac.ke</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="main-footer">
        <div class="footer-content">
            <div class="footer-logo">
                <img src="images/logo.png" alt="Kabarak University" class="church-logo">
                <span>Kabarak University</span>
            </div>
            <div class="footer-links">
                <a href="#about">About</a>
                <a href="#events">Events</a>
                <a href="#sermons">Sermons</a>
                <a href="#contact">Contact</a>
                <a href="login.php">Member Login</a>
            </div>
            <div class="social-links">
                <a href="#" class="social-link"><i class="fab fa-facebook"></i></a>
                <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
            </div>
        </div>
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Kabarak University. All rights reserved.</p>
        </div>
    </footer>

    <!-- Add this at the bottom of the body -->
    <script>
    document.querySelector('.mobile-menu-btn').addEventListener('click', function() {
        document.querySelector('.nav-links').classList.toggle('show');
    });
    </script>
</body>
</html> 