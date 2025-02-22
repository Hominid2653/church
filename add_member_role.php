<?php
require_once 'config/database.php';

try {
    // First check if the role column exists and has ENUM type
    $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'role'");
    $roleColumn = $stmt->fetch();

    if ($roleColumn) {
        // Extract current enum values
        preg_match("/^enum\(\'(.*)\'\)$/", $roleColumn['Type'], $matches);
        $currentValues = explode("','", $matches[1]);
        
        // Check if 'member' is already in the enum
        if (!in_array('member', $currentValues)) {
            // Add 'member' to the enum values
            $newValues = implode("','", array_merge($currentValues, ['member']));
            $sql = "ALTER TABLE users MODIFY COLUMN role ENUM('$newValues') NOT NULL DEFAULT 'member'";
            $pdo->exec($sql);
            echo "Successfully added 'member' role to users table.<br>";
        } else {
            echo "'member' role already exists in users table.<br>";
        }
    } else {
        // If role column doesn't exist, create it
        $sql = "ALTER TABLE users ADD COLUMN role ENUM('admin', 'staff', 'member') NOT NULL DEFAULT 'member'";
        $pdo->exec($sql);
        echo "Successfully created role column with 'member' role.<br>";
    }

    // Update any existing users without a role to 'member'
    $sql = "UPDATE users SET role = 'member' WHERE role IS NULL";
    $stmt = $pdo->exec($sql);
    echo "Updated {$stmt} users with default 'member' role.<br>";

    echo "Role update completed successfully!";

} catch(PDOException $e) {
    die("Error updating database: " . $e->getMessage());
}
?> 