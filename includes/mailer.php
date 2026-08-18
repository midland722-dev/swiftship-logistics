<?php
/**
 * Mailer wrapper.
 *
 * Sends email via PHPMailer when available, otherwise falls back to the
 * built-in mail() function. Every attempt is logged to uploads/mail.log so
 * deliverability is observable even on localhost where mail() is a no-op.
 *
 * For real deliverability, configure SMTP in includes/config.php:
 *   define('MAIL_DRIVER', 'smtp');
 *   define('MAIL_HOST', 'smtp.example.com');
 *   define('MAIL_USERNAME', '...');
 *   define('MAIL_PASSWORD', '...');
 *   define('MAIL_PORT', 587);
 *   define('MAIL_ENCRYPTION', 'tls');
 */

if (!defined('MAIL_FROM')) {
    define('MAIL_FROM', 'no-reply@ascl-logistics.com');
}
if (!defined('MAIL_FROM_NAME')) {
    define('MAIL_FROM_NAME', 'Americans Shipping & Courier Logistics');
}

/**
 * Send an email. Returns true on success (or accepted by the transport).
 */
function sendMail($to, $subject, $body, $opts = []) {
    $to = trim($to);
    if ($to === '' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
        logMail($to, $subject, 'skipped (invalid recipient)');
        return false;
    }

    $from = str_replace(["\r", "\n"], '', $opts['from'] ?? MAIL_FROM);
    $fromName = str_replace(["\r", "\n"], '', $opts['from_name'] ?? MAIL_FROM_NAME);
    $attachment = $opts['attachment_path'] ?? null;
    $attachmentName = $opts['attachment_name'] ?? basename($attachment ?? 'receipt.pdf');

    // Prefer PHPMailer if installed (vendor autoload or app lib).
    if (class_exists('PHPMailer\PHPMailer\PHPMailer') || class_exists('PHPMailer')) {
        try {
            $mail = class_exists('PHPMailer\PHPMailer\PHPMailer')
                ? new PHPMailer\PHPMailer\PHPMailer(true)
                : new PHPMailer(true);
            if (defined('MAIL_DRIVER') && MAIL_DRIVER === 'smtp') {
                $mail->isSMTP();
                $mail->Host = MAIL_HOST ?? 'localhost';
                $mail->Port = MAIL_PORT ?? 25;
                if (defined('MAIL_USERNAME')) {
                    $mail->SMTPAuth = true;
                    $mail->Username = MAIL_USERNAME;
                    $mail->Password = MAIL_PASSWORD ?? '';
                    $mail->SMTPSecure = MAIL_ENCRYPTION ?? '';
                }
            }
            $mail->setFrom($from, $fromName);
            $mail->addAddress($to);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->isHTML(false);
            if ($attachment && is_file($attachment)) {
                $mail->addAttachment($attachment, $attachmentName);
            }
            $mail->send();
            logMail($to, $subject, 'sent via PHPMailer' . ($attachment ? ' (+attachment)' : ''));
            return true;
        } catch (Exception $e) {
            logMail($to, $subject, 'PHPMailer error: ' . $e->getMessage());
            return false;
        }
    }

    // Fallback to mail() with MIME multipart when attachment is present.
    $boundary = '----=_Part_' . md5(microtime(true));
    $headers = "From: \"{$fromName}\" <{$from}>\r\n"
             . "Reply-To: {$from}\r\n"
             . "MIME-Version: 1.0\r\n"
             . "Content-Type: multipart/mixed; boundary=\"{$boundary}\"\r\n";

    $bodyPart = "--{$boundary}\r\n"
             . "Content-Type: text/plain; charset=UTF-8\r\n"
             . "Content-Transfer-Encoding: 7bit\r\n\r\n"
             . $body . "\r\n";

    $attachmentPart = '';
    if ($attachment && is_file($attachment)) {
        $data = base64_encode(file_get_contents($attachment));
        $attachmentPart = "--{$boundary}\r\n"
             . "Content-Type: application/pdf; name=\"{$attachmentName}\"\r\n"
             . "Content-Transfer-Encoding: base64\r\n"
             . "Content-Disposition: attachment; filename=\"{$attachmentName}\"\r\n\r\n"
             . chunk_split($data) . "\r\n";
    }

    $fullBody = $bodyPart . $attachmentPart . "--{$boundary}--";
    $ok = @mail($to, $subject, $fullBody, $headers);
    logMail($to, $subject, ($ok ? 'sent via mail()' : 'mail() returned false (localhost?)') . ($attachment ? ' (+attachment)' : ''));
    return (bool)$ok;
}

function logMail($to, $subject, $result) {
    $line = '[' . date('c') . "] TO={$to} SUBJ=" . str_replace("\n", ' ', $subject) . " => {$result}\n";
    @file_put_contents(__DIR__ . '/../uploads/mail.log', $line, FILE_APPEND);
}
