<?php
/**
 * Database connection and initialization
 */

require_once __DIR__ . '/config.php';

/**
 * Get database connection (singleton pattern)
 */
function getDB(): PDO
{
    static $pdo = null;

    if ($pdo === null) {
        $dbDir = dirname(DB_PATH);

        // Create data directory if it doesn't exist
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        $dbExists = file_exists(DB_PATH);

        try {
            $pdo = new PDO('sqlite:' . DB_PATH);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

            // Enable foreign keys
            $pdo->exec('PRAGMA foreign_keys = ON');

            // Initialize database if it's new
            if (!$dbExists) {
                require_once __DIR__ . '/init_db.php';
                initializeDatabase($pdo);
            }
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            die('Errore di connessione al database. Riprova più tardi.');
        }
    }

    return $pdo;
}
