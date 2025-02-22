<?php
session_start();
require_once 'config/database.php';

// Fetch today's sermon
try {
    $stmt = $pdo->query("SELECT * FROM daily_sermons 
                         WHERE scheduled_date = CURRENT_DATE() 
                         LIMIT 1");
    $today_sermon = $stmt->fetch();
} catch(PDOException $e) {
    // Silently handle error
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    try {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            switch($user['role']) {
                case 'admin':
                case 'staff':
                    header('Location: dashboard.php');
                    break;
                case 'member':
                    header('Location: member_dashboard.php');
                    break;
                default:
                    header('Location: index.php');
            }
            exit();
        } else {
            $error_message = "Invalid username or password";
        }

        // Add this after the user fetch
        if ($user) {
            echo "Role: " . $user['role']; // Check what role is being returned
            exit;
        }
    } catch(PDOException $e) {
        $error_message = "Login error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Login - Kabarak University</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-page">
    <a href="index.php" class="back-to-home">
        <i class="fas fa-arrow-left"></i>
        <span>Back to Home</span>
    </a>

    <div class="login-container">
        <a href="index.php" class="login-logo">
            <h1>Kabarak University</h1>
        </a>
        
        <?php if ($today_sermon): ?>
        <div class="daily-sermon">
            <h3>Today's Sermon</h3>
            <div class="sermon-content">
                <h4><?php echo htmlspecialchars($today_sermon['title']); ?></h4>
                <p><?php echo nl2br(htmlspecialchars($today_sermon['message'])); ?></p>
            </div>
        </div>
        <?php endif; ?>

        <div class="login-card">
            <h2>Welcome Back</h2>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label>
                        <i class="fas fa-user"></i>
                        <span>Username</span>
                    </label>
                    <input type="text" name="username" placeholder="Enter your username" required>
                </div>
                
                <div class="form-group">
                    <label>
                        <i class="fas fa-lock"></i>
                        <span>Password</span>
                    </label>
                    <input type="password" name="password" placeholder="Enter your password" required>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fas fa-sign-in-alt"></i>
                    Login
                </button>
            </form>
        </div>
    </div>
</body>
</html> 