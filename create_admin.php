<?php
require_once 'config/database.php';

try {
    $username = 'admin';
    $password = password_hash('admin123', PASSWORD_DEFAULT); // Change this password!
    
    $stmt = $pdo->prepare("INSERT INTO users (username, password, role) VALUES (:username, :password, 'admin')");
    $stmt->execute([
        ':username' => $username,
        ':password' => $password
    ]);
    
    echo "Admin user created successfully!";
} catch(PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage();
}
?> 