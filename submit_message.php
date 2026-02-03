<?php
/**
 * Handle message form submission
 */

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

// Validate CSRF token
if (!validateCsrfToken()) {
    $_SESSION['error'] = 'Errore di sicurezza. Ricarica la pagina e riprova.';
    header('Location: index.php#form');
    exit;
}

// Check honeypot
if (!checkHoneypot()) {
    // Silently redirect (likely bot)
    header('Location: index.php?sent=1#form');
    exit;
}

// Get and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = preg_replace('/[^0-9+\s]/', '', trim($_POST['phone'] ?? ''));
$topic = trim($_POST['topic'] ?? '');
$message = trim($_POST['message'] ?? '');
$privacy = isset($_POST['privacy']);

// Validate required fields
if (empty($email) || empty($topic) || empty($message) || !$privacy) {
    $_SESSION['error'] = 'Compila tutti i campi obbligatori.';
    header('Location: index.php#form');
    exit;
}

// Validate email format
if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Inserisci un indirizzo email valido.';
    header('Location: index.php#form');
    exit;
}

// Validate topic
$validTopics = array_keys(getTopics());
if (!in_array($topic, $validTopics)) {
    $_SESSION['error'] = 'Seleziona un argomento valido.';
    header('Location: index.php#form');
    exit;
}

// Check for spam content
if (!checkSpamContent($message)) {
    $_SESSION['error'] = 'Il messaggio contiene troppi link.';
    header('Location: index.php#form');
    exit;
}

$pdo = getDB();
$ipHash = getIpHash();

// Check rate limit
if (!checkRateLimit($pdo, 'messages', $ipHash, RATE_LIMIT_MESSAGES, RATE_LIMIT_WINDOW)) {
    $_SESSION['error'] = 'Hai inviato troppi messaggi. Riprova più tardi.';
    header('Location: index.php#form');
    exit;
}

// Insert message
try {
    $stmt = $pdo->prepare("
        INSERT INTO messages (name, email, phone, topic, message, status, created_at, ip_hash, user_agent)
        VALUES (?, ?, ?, ?, ?, 'new', ?, ?, ?)
    ");

    $stmt->execute([
        $name ?: null,
        $email,
        $phone ?: null,
        $topic,
        $message,
        date('c'),
        $ipHash,
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
    ]);

    // Redirect with success
    header('Location: index.php?sent=1#form');
    exit;

} catch (PDOException $e) {
    error_log('Message submission failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Si è verificato un errore. Riprova più tardi.';
    header('Location: index.php#form');
    exit;
}
