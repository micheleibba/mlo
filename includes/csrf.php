<?php
/**
 * CSRF Token Management
 */

/**
 * Generate or retrieve CSRF token
 */
function getCsrfToken(): string
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Get CSRF hidden input field HTML
 */
function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(getCsrfToken()) . '">';
}

/**
 * Validate CSRF token from POST request
 */
function validateCsrfToken(): bool
{
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    $token = $_POST['csrf_token'] ?? '';
    $sessionToken = $_SESSION['csrf_token'] ?? '';

    if (empty($token) || empty($sessionToken)) {
        return false;
    }

    return hash_equals($sessionToken, $token);
}

/**
 * Require valid CSRF token or die
 */
function requireCsrfToken(): void
{
    if (!validateCsrfToken()) {
        http_response_code(403);
        die('Errore di sicurezza: token non valido. Ricarica la pagina e riprova.');
    }
}
