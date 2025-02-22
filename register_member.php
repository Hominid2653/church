<?php
session_start();

// Check if user is logged in and is admin/staff
if (!isset($_SESSION['user_id']) || !in_array($_SESSION['role'], ['admin', 'staff'])) {
    header('Location: login.php');
    exit();
}

require_once 'config/database.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // First insert the member
        $stmt = $pdo->prepare("INSERT INTO members (first_name, last_name, email, phone_number, whatsapp_number, location) 
                              VALUES (:first_name, :last_name, :email, :phone_number, :whatsapp_number, :location)");
        
        $stmt->execute([
            ':first_name' => $_POST['first_name'],
            ':last_name' => $_POST['last_name'],
            ':email' => $_POST['email'],
            ':phone_number' => $_POST['phone_number'],
            ':whatsapp_number' => $_POST['whatsapp_number'],
            ':location' => $_POST['location']
        ]);
        
        // Get the last inserted member_id
        $member_id = $pdo->lastInsertId();
        
        // Then create a user account for the member
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, member_id, role) 
                              VALUES (:username, :password, :member_id, 'member')");
        
        $stmt->execute([
            ':username' => $_POST['username'],
            ':password' => $hashed_password,
            ':member_id' => $member_id
        ]);
        
        $success_message = "Member registered successfully with user account!";
    } catch(PDOException $e) {
        $error_message = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register Member - Kabarak University</title>
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
            <a href="dashboard.php">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="register_member.php" class="active">
                <i class="fas fa-user-plus"></i> Register Member
            </a>
            <a href="events.php">
                <i class="fas fa-calendar-alt"></i> Events
            </a>
            <a href="sermons.php">
                <i class="fas fa-bible"></i> Daily Sermons
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
            <h2>Register New Member</h2>
        </div>
        
        <div class="container">
            <?php if (isset($success_message)): ?>
                <div class="success"><?php echo $success_message; ?></div>
            <?php endif; ?>
            
            <?php if (isset($error_message)): ?>
                <div class="error"><?php echo $error_message; ?></div>
            <?php endif; ?>

            <form method="POST" action="" class="registration-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            First Name
                        </label>
                        <input type="text" name="first_name" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-user"></i>
                            Last Name
                        </label>
                        <input type="text" name="last_name" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-envelope"></i>
                            Email
                        </label>
                        <input type="email" name="email" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-phone"></i>
                            Phone Number
                        </label>
                        <input type="tel" name="phone_number" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-map-marker-alt"></i>
                            Location
                        </label>
                        <input type="text" name="location" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-user-circle"></i>
                            Username
                        </label>
                        <input type="text" name="username" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-lock"></i>
                            Password
                        </label>
                        <input type="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label>
                            <i class="fas fa-lock"></i>
                            Confirm Password
                        </label>
                        <input type="password" name="confirm_password" required>
                    </div>
                </div>

                <button type="submit" class="btn-register">
                    <i class="fas fa-user-plus"></i>
                    Register Member
                </button>
            </form>
        </div>
    </div>

    <script>
    document.querySelector('form').addEventListener('submit', function(e) {
        var password = document.querySelector('input[name="password"]').value;
        var confirmPassword = document.querySelector('input[name="confirm_password"]').value;
        
        if (password !== confirmPassword) {
            e.preventDefault();
            alert('Passwords do not match!');
        }
    });
    </script>
</body>
</html> 