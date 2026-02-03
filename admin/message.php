<?php
/**
 * Admin - View single message
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$pdo = getDB();

// Get message ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('dashboard.php', 'Messaggio non trovato.', 'danger');
}

// Fetch message with reply
$stmt = $pdo->prepare("
    SELECT m.*, r.id as reply_id, r.reply_text, r.published, r.public_slug, r.replied_at, r.email_sent_at
    FROM messages m
    LEFT JOIN replies r ON m.id = r.message_id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    redirect('dashboard.php', 'Messaggio non trovato.', 'danger');
}

// Mark as seen if new
if ($message['status'] === 'new') {
    $stmt = $pdo->prepare("UPDATE messages SET status = 'seen' WHERE id = ?");
    $stmt->execute([$id]);
    $message['status'] = 'seen';
}

// Handle publish toggle
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['toggle_publish'])) {
    requireCsrfToken();

    if ($message['reply_id']) {
        $newPublished = $message['published'] ? 0 : 1;
        $slug = $message['public_slug'];

        // Generate slug if publishing and doesn't have one
        if ($newPublished && !$slug) {
            $slug = generateSlug(truncate($message['message'], 50));
        }

        $stmt = $pdo->prepare("UPDATE replies SET published = ?, public_slug = ? WHERE id = ?");
        $stmt->execute([$newPublished, $slug, $message['reply_id']]);

        redirect('message.php?id=' . $id, $newPublished ? 'Risposta pubblicata.' : 'Risposta rimossa dalla pubblicazione.', 'success');
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messaggio #<?= $id ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php?tab=messages">Messaggi</a></li>
                <li class="breadcrumb-item active">Messaggio #<?= $id ?></li>
            </ol>
        </nav>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <!-- Message -->
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-0">
                                <i class="bi bi-envelope-open me-2"></i>Messaggio ricevuto
                            </h5>
                        </div>
                        <?= getMessageStatusBadge($message['status']) ?>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Da:</strong> <?= e($message['name'] ?: 'Anonimo') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong>
                                <a href="mailto:<?= e($message['email']) ?>"><?= e($message['email']) ?></a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Argomento:</strong>
                                <span class="badge bg-secondary"><?= e($message['topic']) ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Data:</strong> <?= formatDate($message['created_at']) ?>
                            </div>
                        </div>

                        <hr>

                        <div class="message-content p-3 bg-light rounded">
                            <?= nl2br(e($message['message'])) ?>
                        </div>
                    </div>
                </div>

                <!-- Reply -->
                <?php if ($message['reply_id']): ?>
                <div class="card">
                    <div class="card-header bg-success text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-reply me-2"></i>Risposta inviata
                        </h5>
                        <?php if ($message['published']): ?>
                        <span class="badge bg-light text-success">Pubblicata</span>
                        <?php endif; ?>
                    </div>
                    <div class="card-body">
                        <div class="message-content p-3 bg-light rounded mb-3">
                            <?= nl2br(e($message['reply_text'])) ?>
                        </div>

                        <div class="row text-muted small">
                            <div class="col-md-6">
                                <i class="bi bi-clock me-1"></i>
                                Risposto: <?= formatDate($message['replied_at']) ?>
                            </div>
                            <div class="col-md-6">
                                <?php if ($message['email_sent_at']): ?>
                                <i class="bi bi-envelope-check me-1"></i>
                                Email inviata: <?= formatDate($message['email_sent_at']) ?>
                                <?php else: ?>
                                <i class="bi bi-envelope-x me-1"></i>
                                Email non inviata
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer bg-white">
                        <div class="d-flex justify-content-between align-items-center">
                            <form method="POST" class="d-inline">
                                <?= csrfField() ?>
                                <input type="hidden" name="toggle_publish" value="1">
                                <button type="submit" class="btn btn-sm <?= $message['published'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                    <?php if ($message['published']): ?>
                                    <i class="bi bi-eye-slash me-1"></i>Rimuovi pubblicazione
                                    <?php else: ?>
                                    <i class="bi bi-eye me-1"></i>Pubblica sul sito
                                    <?php endif; ?>
                                </button>
                            </form>

                            <a href="reply.php?id=<?= $id ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil me-1"></i>Modifica risposta
                            </a>
                        </div>

                        <?php if ($message['published'] && $message['public_slug']): ?>
                        <div class="mt-2">
                            <small class="text-muted">
                                Link pubblico:
                                <a href="../domande.php?slug=<?= e($message['public_slug']) ?>" target="_blank">
                                    domande.php?slug=<?= e($message['public_slug']) ?>
                                </a>
                            </small>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php else: ?>
                <div class="card border-warning">
                    <div class="card-body text-center py-5">
                        <i class="bi bi-reply text-warning" style="font-size: 3rem;"></i>
                        <p class="mt-3 mb-0">Questo messaggio non ha ancora una risposta.</p>
                        <a href="reply.php?id=<?= $id ?>" class="btn btn-primary mt-3">
                            <i class="bi bi-reply me-2"></i>Scrivi risposta
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>

            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Azioni</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if (!$message['reply_id']): ?>
                            <a href="reply.php?id=<?= $id ?>" class="btn btn-primary">
                                <i class="bi bi-reply me-2"></i>Rispondi
                            </a>
                            <?php endif; ?>

                            <a href="dashboard.php?tab=messages" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Torna ai messaggi
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="card mt-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Informazioni tecniche</h6>
                    </div>
                    <div class="card-body small text-muted">
                        <p class="mb-1"><strong>ID:</strong> <?= $id ?></p>
                        <p class="mb-1"><strong>IP Hash:</strong> <?= e(substr($message['ip_hash'] ?? '-', 0, 16)) ?>...</p>
                        <p class="mb-0"><strong>User Agent:</strong> <?= e(truncate($message['user_agent'] ?? '-', 50)) ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
