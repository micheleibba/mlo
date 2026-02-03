<?php
/**
 * Admin - Proposals Management
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$pdo = getDB();

$id = (int)($_GET['id'] ?? 0);
$error = '';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();

    $proposalId = (int)($_POST['id'] ?? 0);
    $newStatus = $_POST['status'] ?? '';

    if ($proposalId > 0 && in_array($newStatus, ['seen', 'archived'])) {
        try {
            $stmt = $pdo->prepare("UPDATE proposals SET status = ? WHERE id = ?");
            $stmt->execute([$newStatus, $proposalId]);

            $msg = $newStatus === 'archived' ? 'Proposta archiviata.' : 'Proposta segnata come letta.';
            redirect('proposals.php?id=' . $proposalId, $msg, 'success');
        } catch (PDOException $e) {
            error_log('Proposal status update failed: ' . $e->getMessage());
            $error = 'Errore nell\'aggiornamento.';
        }
    }
}

// Fetch single proposal
$proposal = null;
if ($id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM proposals WHERE id = ?");
    $stmt->execute([$id]);
    $proposal = $stmt->fetch();

    if (!$proposal) {
        redirect('dashboard.php?tab=proposals', 'Proposta non trovata.', 'danger');
    }

    // Mark as seen if new
    if ($proposal['status'] === 'new') {
        $stmt = $pdo->prepare("UPDATE proposals SET status = 'seen' WHERE id = ?");
        $stmt->execute([$id]);
        $proposal['status'] = 'seen';
    }
}

$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Proposta #<?= $id ?> - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="dashboard.php?tab=proposals">Proposte</a></li>
                <li class="breadcrumb-item active">Proposta #<?= $id ?></li>
            </ol>
        </nav>

        <?php if ($error): ?>
        <div class="alert alert-danger"><?= e($error) ?></div>
        <?php endif; ?>

        <?php if ($flash): ?>
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
            <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <?php if ($proposal): ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="bi bi-lightbulb me-2"></i>Proposta cittadina
                        </h5>
                        <?= getMessageStatusBadge($proposal['status']) ?>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Da:</strong> <?= e($proposal['name'] ?: 'Anonimo') ?>
                            </div>
                            <div class="col-md-6">
                                <strong>Email:</strong>
                                <a href="mailto:<?= e($proposal['email']) ?>"><?= e($proposal['email']) ?></a>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <strong>Area:</strong>
                                <span class="badge bg-info"><?= e($proposal['area']) ?></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Data:</strong> <?= formatDate($proposal['created_at']) ?>
                            </div>
                        </div>

                        <hr>

                        <div class="mb-4">
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-exclamation-triangle me-1"></i>Il problema segnalato
                            </h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(e($proposal['problem'])) ?>
                            </div>
                        </div>

                        <div>
                            <h6 class="text-muted mb-2">
                                <i class="bi bi-lightbulb me-1"></i>La proposta
                            </h6>
                            <div class="p-3 bg-light rounded">
                                <?= nl2br(e($proposal['proposal'])) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <!-- Actions -->
                <div class="card mb-3">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Azioni</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <?php if ($proposal['status'] !== 'archived'): ?>
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $proposal['id'] ?>">
                                <input type="hidden" name="status" value="archived">
                                <button type="submit" class="btn btn-outline-secondary w-100">
                                    <i class="bi bi-archive me-2"></i>Archivia
                                </button>
                            </form>
                            <?php else: ?>
                            <form method="POST">
                                <?= csrfField() ?>
                                <input type="hidden" name="id" value="<?= $proposal['id'] ?>">
                                <input type="hidden" name="status" value="seen">
                                <button type="submit" class="btn btn-outline-primary w-100">
                                    <i class="bi bi-arrow-counterclockwise me-2"></i>Ripristina
                                </button>
                            </form>
                            <?php endif; ?>

                            <a href="dashboard.php?tab=proposals" class="btn btn-outline-secondary">
                                <i class="bi bi-arrow-left me-2"></i>Torna alle proposte
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Info -->
                <div class="card">
                    <div class="card-header bg-white">
                        <h6 class="mb-0">Informazioni tecniche</h6>
                    </div>
                    <div class="card-body small text-muted">
                        <p class="mb-1"><strong>ID:</strong> <?= $proposal['id'] ?></p>
                        <p class="mb-1"><strong>IP Hash:</strong> <?= e(substr($proposal['ip_hash'] ?? '-', 0, 16)) ?>...</p>
                        <p class="mb-0"><strong>User Agent:</strong> <?= e(truncate($proposal['user_agent'] ?? '-', 50)) ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">Nessuna proposta selezionata.</div>
        <a href="dashboard.php?tab=proposals" class="btn btn-outline-primary">
            <i class="bi bi-arrow-left me-1"></i>Torna alle proposte
        </a>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
