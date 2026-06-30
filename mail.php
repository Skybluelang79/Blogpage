<?php
// mail.php - single-file contact form + PHPMailer send

require __DIR__ . '/vendor/autoload.php'; // Composer autoload

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Configuration (move to env/config in production) ---
$smtpHost = 'smtp.example.com';
$smtpPort = 587;
$smtpUser = 'smtp-user@example.com';
$smtpPass = 'supersecret';
$smtpSecure = 'tls'; // 'tls' or 'ssl'
$fromEmail = 'no-reply@example.com';
$fromName = 'Website Contact';
$toEmail = 'you@example.com';
$toName = 'Site Owner';
// ---------------------------------------------------------

// Helper: simple CSRF token on session (optional but recommended)
session_start();
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
}

$errors = [];
$success = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Basic CSRF check
    $postedToken = $_POST['csrf_token'] ?? '';
    if (!hash_equals($_SESSION['csrf_token'], $postedToken)) {
        $errors[] = 'Invalid form submission (CSRF token).';
    }

    // Retrieve and sanitize
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $message = trim($_POST['message'] ?? '');

    // Validation
    if ($name === '' || strlen($name) > 100) {
        $errors[] = 'Please enter your name (max 100 chars).';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254) {
        $errors[] = 'Please enter a valid email address.';
    }
    if ($subject === '' || strlen($subject) > 200) {
        $errors[] = 'Please enter a subject (max 200 chars).';
    }
    if ($message === '' || strlen($message) > 5000) {
        $errors[] = 'Please enter a message (max 5000 chars).';
    }

    // Rate-limiting / spam control (very simple): limit by session/time
    $lastSent = $_SESSION['last_sent_at'] ?? 0;
    if (time() - $lastSent < 15) { // 15 seconds between sends
        $errors[] = 'Please wait before sending another message.';
    }

    if (empty($errors)) {
        // Build email body (escape output where appropriate)
        $htmlBody = '<h2>Contact form submission</h2>'
            . '<p><strong>Name:</strong> ' . htmlspecialchars($name) . '</p>'
            . '<p><strong>Email:</strong> ' . htmlspecialchars($email) . '</p>'
            . '<p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>'
            . '<p><strong>Message:</strong><br>' . nl2br(htmlspecialchars($message)) . '</p>';

        $plainBody = "Contact form submission\n\n"
            . "Name: $name\n"
            . "Email: $email\n"
            . "Subject: $subject\n\n"
            . "Message:\n$message\n";

        $mail = new PHPMailer(true);
        try {
            // SMTP configuration
            $mail->isSMTP();
            $mail->Host = $smtpHost;
            $mail->SMTPAuth = true;
            $mail->Username = $smtpUser;
            $mail->Password = $smtpPass;
            $mail->SMTPSecure = $smtpSecure;
            $mail->Port = $smtpPort;

            // Message
            $mail->setFrom($fromEmail, $fromName);
            $mail->addReplyTo($email, $name); // reply goes to sender
            $mail->addAddress($toEmail, $toName);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = $plainBody;
            $mail->isHTML(true);

            // Optional: attach uploaded file (with validations)
            if (!empty($_FILES['attachment']['tmp_name'])) {
                $fileTmp = $_FILES['attachment']['tmp_name'];
                $fileName = basename($_FILES['attachment']['name']);
                $fileSize = $_FILES['attachment']['size'];
                $allowedMax = 2 * 1024 * 1024; // 2MB
                if ($fileSize <= $allowedMax) {
                    $mail->addAttachment($fileTmp, $fileName);
                } else {
                    // skip attachment but don't fail send
                    $errors[] = 'Attachment too large (max 2MB).';
                }
            }

            $mail->send();
            $_SESSION['last_sent_at'] = time();
            $success = true;

            // Rotate CSRF token after successful send
            $_SESSION['csrf_token'] = bin2hex(random_bytes(16));
        } catch (Exception $e) {
            $errors[] = 'Mailer Error: ' . $mail->ErrorInfo;
        }
    }
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Contact</title>
<meta name="viewport" content="width=device-width,initial-scale=1">
<style>
/* Minimal styling */
body { font-family: Arial, sans-serif; max-width:700px; margin:2rem auto; padding:1rem; }
input, textarea { width:100%; padding:.5rem; margin:.25rem 0 1rem; box-sizing:border-box; }
button { padding:.5rem 1rem; }
.error { color: #b00020; }
.success { color: #007a00; }
</style>
</head>
<body>
<h1>Contact Us</h1>

<?php if ($success): ?>
  <p class="success">Message sent — thank you! We'll get back to you soon.</p>
<?php endif; ?>

<?php if (!empty($errors)): ?>
  <div class="error">
    <ul>
      <?php foreach ($errors as $e): ?>
        <li><?php echo htmlspecialchars($e); ?></li>
      <?php endforeach; ?>
    </ul>
  </div>
<?php endif; ?>

<form method="post" enctype="multipart/form-data" novalidate>
  <label>Name
    <input type="text" name="name" required maxlength="100" value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
  </label>

  <label>Email
    <input type="email" name="email" required maxlength="254" value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
  </label>

  <label>Subject
    <input type="text" name="subject" required maxlength="200" value="<?php echo htmlspecialchars($_POST['subject'] ?? ''); ?>">
  </label>

  <label>Message
    <textarea name="message" rows="8" required maxlength="5000"><?php echo htmlspecialchars($_POST['message'] ?? ''); ?></textarea>
  </label>

  <label>Attachment (optional, max 2MB)
    <input type="file" name="attachment" accept=".pdf,.png,.jpg,.jpeg,.txt">
  </label>

  <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token']); ?>">

  <button type="submit">Send Message</button>
</form>
</body>
</html>
