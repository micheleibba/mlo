<?php
/**
 * Email functionality using PHPMailer (optional)
 */

// Check if PHPMailer is available
$phpmailerAvailable = false;
$autoloadPath = __DIR__ . '/../vendor/autoload.php';

if (file_exists($autoloadPath)) {
    require_once $autoloadPath;
    $phpmailerAvailable = class_exists('PHPMailer\PHPMailer\PHPMailer');
}

require_once __DIR__ . '/config.php';

/**
 * Check if email functionality is available
 */
function isEmailAvailable(): bool
{
    global $phpmailerAvailable;
    return $phpmailerAvailable && SMTP_HOST !== 'smtp.example.com';
}

/**
 * Send reply email to citizen
 */
function sendReplyEmail(string $toEmail, ?string $toName, string $questionExcerpt, string $answerText): bool
{
    global $phpmailerAvailable;

    // If PHPMailer is not available, log and return false
    if (!$phpmailerAvailable) {
        error_log('PHPMailer not installed. Run "composer install" to enable email functionality.');
        return false;
    }

    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        // Server settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USER;
        $mail->Password   = SMTP_PASS;
        $mail->SMTPSecure = SMTP_SECURE === 'ssl' ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';

        // Recipients
        $mail->setFrom(MAIL_FROM, MAIL_FROM_NAME);
        $mail->addAddress($toEmail, $toName ?? '');
        $mail->addReplyTo(MAIL_FROM, MAIL_FROM_NAME);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Risposta alla tua domanda - ' . SITE_NAME;

        // Build HTML email
        $htmlBody = buildEmailHtml($toName, $questionExcerpt, $answerText);
        $mail->Body = $htmlBody;

        // Plain text alternative
        $mail->AltBody = buildEmailPlainText($toName, $questionExcerpt, $answerText);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log('Email sending failed: ' . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Build HTML email body
 */
function buildEmailHtml(?string $name, string $question, string $answer): string
{
    $greeting = $name ? "Gentile {$name}," : "Gentile cittadino/a,";
    $siteName = SITE_NAME;

    return <<<HTML
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #059669 0%, #0ea5e9 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="margin: 0; font-size: 24px;">Risposta da Maria Laura Orrù</h1>
    </div>

    <div style="background: #f8f9fa; padding: 30px; border-radius: 0 0 10px 10px;">
        <p style="margin-top: 0;">{$greeting}</p>

        <p>hai ricevuto una risposta alla domanda che mi hai inviato.</p>

        <div style="background: #fff; border-left: 4px solid #059669; padding: 15px 20px; margin: 20px 0; border-radius: 0 8px 8px 0;">
            <strong style="color: #666; font-size: 12px; text-transform: uppercase;">La tua domanda:</strong>
            <p style="margin: 10px 0 0 0; color: #444;">{$question}</p>
        </div>

        <div style="background: #fff; border-left: 4px solid #22c55e; padding: 15px 20px; margin: 20px 0; border-radius: 0 8px 8px 0;">
            <strong style="color: #666; font-size: 12px; text-transform: uppercase;">Risposta:</strong>
            <p style="margin: 10px 0 0 0; color: #333;">{$answer}</p>
        </div>

        <p style="margin-top: 30px;">Cordiali saluti,<br>
        <strong>Maria Laura Orrù</strong></p>

        <hr style="border: none; border-top: 1px solid #ddd; margin: 30px 0;">

        <p style="font-size: 12px; color: #888; margin-bottom: 0;">
            Questa email è stata inviata in risposta a una tua richiesta tramite il sito {$siteName}.<br>
            I tuoi dati sono trattati nel rispetto della privacy.
        </p>
    </div>
</body>
</html>
HTML;
}

/**
 * Build plain text email body
 */
function buildEmailPlainText(?string $name, string $question, string $answer): string
{
    $greeting = $name ? "Gentile {$name}," : "Gentile cittadino/a,";
    $siteName = SITE_NAME;

    return <<<TEXT
{$greeting}

hai ricevuto una risposta alla domanda che mi hai inviato.

LA TUA DOMANDA:
{$question}

RISPOSTA:
{$answer}

Cordiali saluti,
Maria Laura Orrù

---
Questa email è stata inviata in risposta a una tua richiesta tramite il sito {$siteName}.
I tuoi dati sono trattati nel rispetto della privacy.
TEXT;
}
