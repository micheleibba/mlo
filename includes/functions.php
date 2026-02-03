<?php
/**
 * Utility functions
 */

/**
 * Escape output for HTML
 */
function e(string $str): string
{
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}

/**
 * Generate a URL-friendly slug
 */
function generateSlug(string $text): string
{
    $text = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
    $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    $text = strtolower(trim($text));
    $text = preg_replace('/\s+/', '-', $text);
    return substr($text, 0, 50) . '-' . bin2hex(random_bytes(4));
}

/**
 * Get hashed IP for rate limiting (privacy-preserving)
 */
function getIpHash(): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    return hash('sha256', $ip . date('Y-m-d'));
}

/**
 * Check rate limit for an IP hash
 */
function checkRateLimit(PDO $pdo, string $table, string $ipHash, int $limit, int $windowSeconds): bool
{
    $since = date('c', time() - $windowSeconds);

    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM {$table}
        WHERE ip_hash = ? AND created_at > ?
    ");
    $stmt->execute([$ipHash, $since]);
    $count = $stmt->fetchColumn();

    return $count < $limit;
}

/**
 * Check for honeypot field (should be empty)
 */
function checkHoneypot(): bool
{
    return empty($_POST['website'] ?? '');
}

/**
 * Check for suspicious content (too many links)
 */
function checkSpamContent(string $text): bool
{
    $linkCount = preg_match_all('/https?:\/\/|www\./i', $text);
    return $linkCount <= 2;
}

/**
 * Validate email format
 */
function isValidEmail(string $email): bool
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Format date in Italian
 */
function formatDate(string $isoDate): string
{
    $months = [
        1 => 'gennaio', 2 => 'febbraio', 3 => 'marzo', 4 => 'aprile',
        5 => 'maggio', 6 => 'giugno', 7 => 'luglio', 8 => 'agosto',
        9 => 'settembre', 10 => 'ottobre', 11 => 'novembre', 12 => 'dicembre'
    ];

    $timestamp = strtotime($isoDate);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);

    return "{$day} {$month} {$year}";
}

/**
 * Truncate text with ellipsis
 */
function truncate(string $text, int $length = 150): string
{
    if (mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . '...';
}

/**
 * Get status badge HTML
 */
function getStatusBadge(string $status): string
{
    $badges = [
        'fatto' => '<span class="badge bg-success">FATTO</span>',
        'in_parte' => '<span class="badge bg-warning text-dark">IN PARTE</span>',
        'non_fatto' => '<span class="badge bg-secondary">NON FATTO</span>',
    ];

    return $badges[$status] ?? '<span class="badge bg-secondary">-</span>';
}

/**
 * Get message status badge HTML
 */
function getMessageStatusBadge(string $status): string
{
    $badges = [
        'new' => '<span class="badge bg-danger">Nuovo</span>',
        'seen' => '<span class="badge bg-info">Letto</span>',
        'replied' => '<span class="badge bg-success">Risposto</span>',
        'archived' => '<span class="badge bg-secondary">Archiviato</span>',
    ];

    return $badges[$status] ?? '<span class="badge bg-secondary">-</span>';
}

/**
 * Check if user is logged in as admin
 */
function isAdminLoggedIn(): bool
{
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

/**
 * Require admin login or redirect
 */
function requireAdmin(): void
{
    if (!isAdminLoggedIn()) {
        header('HTTP/1.0 404 Not Found');
        echo '<!DOCTYPE html><html><head><title>404 Not Found</title></head><body><h1>Not Found</h1></body></html>';
        exit;
    }
}

/**
 * Get topics for message form
 */
function getTopics(): array
{
    return [
        'Ordinario' => 'Ordinario (manutenzione, servizi)',
        'Progetti' => 'Progetti (iniziative in corso)',
        'Idee' => 'Idee (proposte nuove)',
        'Altro' => 'Altro',
    ];
}

/**
 * Get areas for proposal form
 */
function getAreas(): array
{
    return [
        'Manutenzione' => 'Manutenzione',
        'Verde' => 'Verde pubblico',
        'Mobilità' => 'Mobilità e trasporti',
        'Sicurezza' => 'Sicurezza',
        'Cultura' => 'Cultura e sport',
        'Scuola' => 'Scuola e istruzione',
        'Sociale' => 'Sociale e assistenza',
        'Progetti' => 'Progetti e sviluppo',
        'Altro' => 'Altro',
    ];
}

/**
 * Get program areas
 */
function getProgramAreas(): array
{
    return [
        'Partecipazione e Digitale',
        'Sicurezza e Ambiente',
        'Spazi Pubblici',
        'Urbanistica e Viabilità',
        'Città Metropolitana',
        'Mobilità Sostenibile',
        'Economia Locale',
        'Politiche Sociali',
        'Cultura e Istruzione',
    ];
}

/**
 * Redirect with message
 */
function redirect(string $url, string $message = '', string $type = 'success'): void
{
    if ($message) {
        $_SESSION['flash_message'] = $message;
        $_SESSION['flash_type'] = $type;
    }
    header('Location: ' . $url);
    exit;
}

/**
 * Get and clear flash message
 */
function getFlashMessage(): ?array
{
    if (isset($_SESSION['flash_message'])) {
        $message = $_SESSION['flash_message'];
        $type = $_SESSION['flash_type'] ?? 'success';
        unset($_SESSION['flash_message'], $_SESSION['flash_type']);
        return ['message' => $message, 'type' => $type];
    }
    return null;
}
