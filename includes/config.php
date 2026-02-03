<?php
/**
 * Configuration file
 *
 * IMPORTANT: Change all sensitive values before deploying to production!
 */

// Error reporting - set to 0 in production
error_reporting(E_ALL);
ini_set('display_errors', '0');
ini_set('log_errors', '1');

// Site configuration
define('SITE_NAME', 'Maria Laura Orrù - Scrivimi');
define('SITE_URL', 'https://mlo.nubelab.it');

// Timezone
date_default_timezone_set('Europe/Rome');

// Database path
define('DB_PATH', __DIR__ . '/../data/site.sqlite');

// Admin access - CHANGE THESE IN PRODUCTION!
// The secret key required in URL to access admin login: admin/login.php?k=YOUR_SECRET_KEY
define('ADMIN_SECRET_KEY', '123a9fa01bc19007a79f99ff6eea176a');

// Admin credentials - CHANGE PASSWORD IN PRODUCTION!
// To generate a new hash: echo password_hash('your_new_password', PASSWORD_DEFAULT);
define('ADMIN_USER', 'admin');
define('ADMIN_PASS_HASH', password_hash('admin123', PASSWORD_DEFAULT)); // CHANGE THIS!

// Email configuration
define('MAIL_FROM', 'marialauraorru.elezioni@gmail.com');
define('MAIL_FROM_NAME', 'Maria Laura Orrù');

// SMTP Configuration - Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_USER', 'marialauraorru.elezioni@gmail.com');
define('SMTP_PASS', 'wxvmxcvqkiyljqns');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls');

// Rate limiting
define('RATE_LIMIT_MESSAGES', 3); // max messages per IP
define('RATE_LIMIT_PROPOSALS', 3); // max proposals per IP
define('RATE_LIMIT_WINDOW', 900); // 15 minutes in seconds

// Session configuration
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure', '0'); // Set to '1' if using HTTPS
ini_set('session.use_strict_mode', '1');
