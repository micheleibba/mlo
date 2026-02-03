<?php
/**
 * Admin - Reply to message
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';
require_once __DIR__ . '/../includes/mailer.php';

requireAdmin();

$pdo = getDB();

// Get message ID
$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('dashboard.php', 'Messaggio non trovato.', 'danger');
}

// Fetch message with existing reply
$stmt = $pdo->prepare("
    SELECT m.*, r.id as reply_id, r.reply_text, r.published, r.public_slug
    FROM messages m
    LEFT JOIN replies r ON m.id = r.message_id
    WHERE m.id = ?
");
$stmt->execute([$id]);
$message = $stmt->fetch();

if (!$message) {
    redirect('dashboard.php', 'Messaggio non trovato.', 'danger');
}

$error = '';
$success = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();

    $replyText = trim($_POST['reply_text'] ?? '');
    $sendEmail = isset($_POST['send_email']);
    $publish = isset($_POST['publish']);

    if (empty($replyText)) {
        $error = 'La risposta non può essere vuota.';
    } else {
        try {
            $pdo->beginTransaction();

            $now = date('c');

            if ($message['reply_id']) {
                // Update existing reply
                $slug = $message['public_slug'];
                if ($publish && !$slug) {
                    $slug = generateSlug(truncate($message['message'], 50));
                }

                $stmt = $pdo->prepare("
                    UPDATE replies
                    SET reply_text = ?, published = ?, public_slug = ?, replied_at = ?
                    WHERE id = ?
                ");
                $stmt->execute([$replyText, $publish ? 1 : 0, $slug, $now, $message['reply_id']]);
            } else {
                // Insert new reply
                $slug = $publish ? generateSlug(truncate($message['message'], 50)) : null;

                $stmt = $pdo->prepare("
                    INSERT INTO replies (message_id, reply_text, published, public_slug, replied_at)
                    VALUES (?, ?, ?, ?, ?)
                ");
                $stmt->execute([$id, $replyText, $publish ? 1 : 0, $slug, $now]);
            }

            // Update message status
            $stmt = $pdo->prepare("UPDATE messages SET status = 'replied' WHERE id = ?");
            $stmt->execute([$id]);

            // Send email if requested
            $emailSent = false;
            if ($sendEmail) {
                $emailSent = sendReplyEmail(
                    $message['email'],
                    $message['name'],
                    truncate($message['message'], 200),
                    $replyText
                );

                if ($emailSent) {
                    // Update email_sent_at
                    if ($message['reply_id']) {
                        $stmt = $pdo->prepare("UPDATE replies SET email_sent_at = ? WHERE id = ?");
                        $stmt->execute([$now, $message['reply_id']]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE replies SET email_sent_at = ? WHERE message_id = ?");
                        $stmt->execute([$now, $id]);
                    }
                }
            }

            $pdo->commit();

            $successMsg = 'Risposta salvata.';
            if ($sendEmail) {
                $successMsg .= $emailSent ? ' Email inviata.' : ' Errore nell\'invio email.';
            }
            if ($publish) {
                $successMsg .= ' Pubblicata sul sito.';
            }

            redirect('message.php?id=' . $id, $successMsg, $emailSent || !$sendEmail ? 'success' : 'warning');

        } catch (PDOException $e) {
            $pdo->rollBack();
            error_log('Reply save failed: ' . $e->getMessage());
            $error = 'Errore nel salvataggio. Riprova.';
        }
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rispondi - Messaggio #<?= $id ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="message.php?id=<?= $id ?>">Messaggio #<?= $id ?></a></li>
                <li class="breadcrumb-item active">Rispondi</li>
            </ol>
        </nav>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?>"><?= e($flash['message']) ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <form method="POST">
                    <?= csrfField() ?>

                    <!-- Original Message -->
                    <div class="card mb-4">
                        <div class="card-header bg-white">
                            <h6 class="mb-0">
                                <i class="bi bi-envelope me-2"></i>Messaggio originale
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-2">
                                <span><strong><?= e($message['name'] ?: 'Anonimo') ?></strong></span>
                                <span class="badge bg-secondary"><?= e($message['topic']) ?></span>
                            </div>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(e($message['message'])) ?>
                            </div>
                            <small class="text-muted mt-2 d-block">
                                <?= formatDate($message['created_at']) ?>
                            </small>
                        </div>
                    </div>

                    <!-- Reply -->
                    <div class="card mb-4">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0">
                                <i class="bi bi-reply me-2"></i>La tua risposta
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <textarea class="form-control" name="reply_text" rows="10"
                                          required placeholder="Scrivi qui la tua risposta..."><?= e($message['reply_text'] ?? '') ?></textarea>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="send_email"
                                           name="send_email" value="1" <?= !$message['reply_id'] ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="send_email">
                                        <i class="bi bi-envelope me-1"></i>
                                        Invia risposta via email a <?= e($message['email']) ?>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="publish"
                                           name="publish" value="1" <?= ($message['published'] ?? false) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="publish">
                                        <i class="bi bi-globe me-1"></i>
                                        Pubblica sul sito (visibile a tutti)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="card-footer bg-white">
                            <div class="d-flex justify-content-between">
                                <a href="message.php?id=<?= $id ?>" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Annulla
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Salva risposta
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Informazioni</h6>
                    </div>
                    <div class="card-body">
                        <p class="small text-muted">
                            <i class="bi bi-info-circle me-1"></i>
                            La risposta verrà inviata all'indirizzo email del cittadino solo se
                            selezioni l'opzione "Invia via email".
                        </p>
                        <p class="small text-muted mb-0">
                            <i class="bi bi-globe me-1"></i>
                            Selezionando "Pubblica sul sito", la domanda e la risposta saranno
                            visibili nella sezione pubblica delle Q&A.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
