<?php
require_once 'config/database.php';

class NotificationProcessor {
    private $pdo;
    
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
    
    public function processNotification($notification_id) {
        try {
            // Fetch notification details with member info
            $stmt = $this->pdo->prepare("
                SELECT n.*, m.email, m.phone_number, m.whatsapp_number 
                FROM notifications n
                JOIN members m ON n.member_id = m.member_id
                WHERE n.notification_id = ?
            ");
            $stmt->execute([$notification_id]);
            $notification = $stmt->fetch();
            
            $channels = explode(',', $notification['channels']);
            $success = true;
            $errors = [];
            
            // Process each selected channel
            foreach ($channels as $channel) {
                try {
                    switch ($channel) {
                        case 'email':
                            $success &= $this->sendEmail($notification);
                            break;
                        case 'sms':
                            $success &= $this->sendSMS($notification);
                            break;
                        case 'whatsapp':
                            $success &= $this->sendWhatsApp($notification);
                            break;
                    }
                } catch (Exception $e) {
                    $errors[] = "$channel: " . $e->getMessage();
                }
            }
            
            // Update notification status
            $status = $success ? 'sent' : 'failed';
            $error_message = !empty($errors) ? implode('; ', $errors) : null;
            
            $stmt = $this->pdo->prepare("
                UPDATE notifications 
                SET status = ?, error_message = ?
                WHERE notification_id = ?
            ");
            $stmt->execute([$status, $error_message, $notification_id]);
            
        } catch (Exception $e) {
            // Log the error
            error_log("Error processing notification $notification_id: " . $e->getMessage());
        }
    }
    
    private function sendEmail($notification) {
        // Implement email sending using PHPMailer or similar
        require 'vendor/autoload.php';
        
        $mail = new PHPMailer\PHPMailer\PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = SMTP_HOST;
            $mail->SMTPAuth = true;
            $mail->Username = SMTP_USERNAME;
            $mail->Password = SMTP_PASSWORD;
            $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port = SMTP_PORT;
            
            $mail->setFrom(CHURCH_EMAIL, 'Kabarak University');
            $mail->addAddress($notification['email']);
            $mail->Subject = "Church Notification: " . ucfirst($notification['type']);
            $mail->Body = $notification['content'];
            
            return $mail->send();
        } catch (Exception $e) {
            throw new Exception("Email sending failed: " . $mail->ErrorInfo);
        }
    }
    
    private function sendSMS($notification) {
        // Implement SMS sending using Twilio or similar
        require 'vendor/autoload.php';
        
        $client = new Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);
        try {
            $message = $client->messages->create(
                $notification['phone_number'],
                [
                    'from' => TWILIO_NUMBER,
                    'body' => $notification['content']
                ]
            );
            return true;
        } catch (Exception $e) {
            throw new Exception("SMS sending failed: " . $e->getMessage());
        }
    }
    
    private function sendWhatsApp($notification) {
        // Implement WhatsApp sending using Twilio/WhatsApp Business API
        require 'vendor/autoload.php';
        
        $client = new Twilio\Rest\Client(TWILIO_SID, TWILIO_TOKEN);
        try {
            $message = $client->messages->create(
                "whatsapp:" . $notification['whatsapp_number'],
                [
                    'from' => "whatsapp:" . TWILIO_WHATSAPP_NUMBER,
                    'body' => $notification['content']
                ]
            );
            return true;
        } catch (Exception $e) {
            throw new Exception("WhatsApp sending failed: " . $e->getMessage());
        }
    }
} 