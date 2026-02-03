<?php
/**
 * Admin Login - Hidden access via secret key
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

// Check secret key in URL - if missing or wrong, return 404
$secretKey = $_GET['k'] ?? '';
if ($secretKey !== ADMIN_SECRET_KEY) {
    http_response_code(404);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 Not Found</title>
    <style>
        body { font-family: system-ui, sans-serif; display: flex; align-items: center; justify-content: center; min-height: 100vh; margin: 0; background: #f5f5f5; }
        .error { text-align: center; }
        h1 { color: #333; font-size: 72px; margin: 0; }
        p { color: #666; }
    </style>
</head>
<body>
    <div class="error">
        <h1>404</h1>
        <p>La pagina richiesta non esiste.</p>
    </div>
</body>
</html>';
    exit;
}

// Already logged in? Redirect to dashboard
if (isAdminLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCsrfToken()) {
        $error = 'Errore di sicurezza. Ricarica la pagina.';
    } else {
        $username = trim($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($username === ADMIN_USER && password_verify($password, ADMIN_PASS_HASH)) {
            // Regenerate session ID to prevent fixation
            session_regenerate_id(true);

            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_user'] = $username;
            $_SESSION['admin_login_time'] = time();

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Credenziali non valide.';
            // Add delay to slow down brute force
            sleep(1);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accesso Admin - <?= e(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #0d6e6e 0%, #1a8a8a 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            max-width: 400px;
            width: 100%;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-card">
            <div class="card shadow-lg">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <i class="bi bi-shield-lock text-primary" style="font-size: 3rem;"></i>
                        <h2 class="h4 mt-3">Area riservata</h2>
                        <p class="text-muted small">Accesso amministrativo</p>
                    </div>

                    <?php if ($error): ?>
                    <div class="alert alert-danger d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        <?= e($error) ?>
                    </div>
                    <?php endif; ?>

                    <form method="POST" action="login.php?k=<?= e($secretKey) ?>">
                        <?= csrfField() ?>

                        <div class="mb-3">
                            <label for="username" class="form-label">Username</label>
                            <input type="text" class="form-control" id="username" name="username"
                                   required autocomplete="username" autofocus>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control" id="password" name="password"
                                   required autocomplete="current-password">
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Accedi
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center text-white-50 mt-4 small">
                <a href="../index.php" class="text-white text-decoration-none">
                    <i class="bi bi-arrow-left me-1"></i>Torna al sito
                </a>
            </p>
        </div>
    </div>
</body>
</html>
