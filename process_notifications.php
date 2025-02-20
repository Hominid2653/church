<?php
require_once 'config/database.php';
require_once 'includes/NotificationProcessor.php';

$processor = new NotificationProcessor($pdo);

// Fetch pending notifications
$stmt = $pdo->query("
    SELECT notification_id 
    FROM notifications 
    WHERE status = 'pending' 
    ORDER BY sent_at ASC
");

while ($notification = $stmt->fetch()) {
    $processor->processNotification($notification['notification_id']);
} 