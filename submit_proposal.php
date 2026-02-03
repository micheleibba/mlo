<?php
/**
 * Handle proposal form submission
 */

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php#proposta');
    exit;
}

// Validate CSRF token
if (!validateCsrfToken()) {
    $_SESSION['error'] = 'Errore di sicurezza. Ricarica la pagina e riprova.';
    header('Location: index.php#proposta');
    exit;
}

// Check honeypot
if (!checkHoneypot()) {
    // Silently redirect (likely bot)
    header('Location: index.php?proposal=1#proposta');
    exit;
}

// Get and sanitize input
$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$area = trim($_POST['area'] ?? '');
$problem = trim($_POST['problem'] ?? '');
$proposal = trim($_POST['proposal'] ?? '');
$privacy = isset($_POST['privacy']);

// Validate required fields
if (empty($email) || empty($area) || empty($problem) || empty($proposal) || !$privacy) {
    $_SESSION['error'] = 'Compila tutti i campi obbligatori.';
    header('Location: index.php#proposta');
    exit;
}

// Validate email format
if (!isValidEmail($email)) {
    $_SESSION['error'] = 'Inserisci un indirizzo email valido.';
    header('Location: index.php#proposta');
    exit;
}

// Validate area
$validAreas = array_keys(getAreas());
if (!in_array($area, $validAreas)) {
    $_SESSION['error'] = 'Seleziona un\'area tematica valida.';
    header('Location: index.php#proposta');
    exit;
}

// Check for spam content
if (!checkSpamContent($problem . ' ' . $proposal)) {
    $_SESSION['error'] = 'Il contenuto contiene troppi link.';
    header('Location: index.php#proposta');
    exit;
}

$pdo = getDB();
$ipHash = getIpHash();

// Check rate limit
if (!checkRateLimit($pdo, 'proposals', $ipHash, RATE_LIMIT_PROPOSALS, RATE_LIMIT_WINDOW)) {
    $_SESSION['error'] = 'Hai inviato troppe proposte. Riprova più tardi.';
    header('Location: index.php#proposta');
    exit;
}

// Insert proposal
try {
    $stmt = $pdo->prepare("
        INSERT INTO proposals (name, email, area, problem, proposal, status, created_at, ip_hash, user_agent)
        VALUES (?, ?, ?, ?, ?, 'new', ?, ?, ?)
    ");

    $stmt->execute([
        $name ?: null,
        $email,
        $area,
        $problem,
        $proposal,
        date('c'),
        $ipHash,
        substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500)
    ]);

    // Redirect with success
    header('Location: index.php?proposal=1#proposta');
    exit;

} catch (PDOException $e) {
    error_log('Proposal submission failed: ' . $e->getMessage());
    $_SESSION['error'] = 'Si è verificato un errore. Riprova più tardi.';
    header('Location: index.php#proposta');
    exit;
}
