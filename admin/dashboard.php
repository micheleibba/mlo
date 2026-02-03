<?php
/**
 * Admin Dashboard
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$pdo = getDB();

// Get statistics
$stats = [];

// Messages stats
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM messages GROUP BY status");
$messageStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$stats['messages_new'] = $messageStats['new'] ?? 0;
$stats['messages_seen'] = $messageStats['seen'] ?? 0;
$stats['messages_replied'] = $messageStats['replied'] ?? 0;
$stats['messages_total'] = array_sum($messageStats);

// Published Q&A
$stmt = $pdo->query("SELECT COUNT(*) FROM replies WHERE published = 1");
$stats['published'] = $stmt->fetchColumn();

// Proposals stats
$stmt = $pdo->query("SELECT status, COUNT(*) as count FROM proposals GROUP BY status");
$proposalStats = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
$stats['proposals_new'] = $proposalStats['new'] ?? 0;
$stats['proposals_total'] = array_sum($proposalStats);

// Get active tab
$activeTab = $_GET['tab'] ?? 'messages';

// Fetch data based on tab
if ($activeTab === 'messages') {
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = '';
    if ($filter === 'new') {
        $whereClause = "WHERE m.status = 'new'";
    } elseif ($filter === 'seen') {
        $whereClause = "WHERE m.status = 'seen'";
    } elseif ($filter === 'replied') {
        $whereClause = "WHERE m.status = 'replied'";
    }

    $stmt = $pdo->query("
        SELECT m.*, r.id as reply_id, r.published
        FROM messages m
        LEFT JOIN replies r ON m.id = r.message_id
        $whereClause
        ORDER BY m.created_at DESC
        LIMIT 50
    ");
    $messages = $stmt->fetchAll();
} elseif ($activeTab === 'proposals') {
    $filter = $_GET['filter'] ?? 'all';
    $whereClause = '';
    if ($filter === 'new') {
        $whereClause = "WHERE status = 'new'";
    } elseif ($filter === 'seen') {
        $whereClause = "WHERE status = 'seen'";
    }

    $stmt = $pdo->query("
        SELECT * FROM proposals
        $whereClause
        ORDER BY created_at DESC
        LIMIT 50
    ");
    $proposals = $stmt->fetchAll();
}

// Flash message
$flash = getFlashMessage();
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - <?= e(SITE_NAME) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="d-flex">
        <!-- Sidebar -->
        <nav class="admin-sidebar d-flex flex-column p-3" style="width: 250px; min-height: 100vh;">
            <a href="../index.php" class="d-flex align-items-center mb-4 text-white text-decoration-none">
                <i class="bi bi-envelope-heart me-2 fs-4"></i>
                <span class="fs-5 fw-bold">Scrivimi</span>
            </a>

            <ul class="nav nav-pills flex-column mb-auto">
                <li class="nav-item">
                    <a href="dashboard.php?tab=messages" class="nav-link <?= $activeTab === 'messages' ? 'active' : '' ?>">
                        <i class="bi bi-envelope me-2"></i>Messaggi
                        <?php if ($stats['messages_new'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $stats['messages_new'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="dashboard.php?tab=proposals" class="nav-link <?= $activeTab === 'proposals' ? 'active' : '' ?>">
                        <i class="bi bi-lightbulb me-2"></i>Proposte
                        <?php if ($stats['proposals_new'] > 0): ?>
                        <span class="badge bg-danger ms-auto"><?= $stats['proposals_new'] ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="program.php" class="nav-link <?= $activeTab === 'program' ? 'active' : '' ?>">
                        <i class="bi bi-list-check me-2"></i>Programma 2021
                    </a>
                </li>
            </ul>

            <hr class="text-white-50">

            <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle"
                   data-bs-toggle="dropdown">
                    <i class="bi bi-person-circle me-2"></i>
                    <strong><?= e($_SESSION['admin_user'] ?? 'Admin') ?></strong>
                </a>
                <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                    <li><a class="dropdown-item" href="../index.php" target="_blank">Vai al sito</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Esci</a></li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <div class="flex-grow-1 p-4">
            <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show" role="alert">
                <?= e($flash['message']) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Stats -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number text-danger"><?= $stats['messages_new'] ?></div>
                        <div class="stat-label">Nuovi messaggi</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number"><?= $stats['messages_replied'] ?></div>
                        <div class="stat-label">Risposte inviate</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number text-success"><?= $stats['published'] ?></div>
                        <div class="stat-label">Q&A pubblicate</div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card stat-card">
                        <div class="stat-number text-warning"><?= $stats['proposals_new'] ?></div>
                        <div class="stat-label">Nuove proposte</div>
                    </div>
                </div>
            </div>

            <?php if ($activeTab === 'messages'): ?>
            <!-- Messages Tab -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-envelope me-2"></i>Messaggi ricevuti
                    </h5>
                    <div class="btn-group" role="group">
                        <a href="?tab=messages&filter=all" class="btn btn-sm <?= ($_GET['filter'] ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Tutti
                        </a>
                        <a href="?tab=messages&filter=new" class="btn btn-sm <?= ($_GET['filter'] ?? '') === 'new' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Nuovi (<?= $stats['messages_new'] ?>)
                        </a>
                        <a href="?tab=messages&filter=seen" class="btn btn-sm <?= ($_GET['filter'] ?? '') === 'seen' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Letti
                        </a>
                        <a href="?tab=messages&filter=replied" class="btn btn-sm <?= ($_GET['filter'] ?? '') === 'replied' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Risposti
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($messages)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox" style="font-size: 3rem;"></i>
                        <p class="mt-2">Nessun messaggio trovato</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Stato</th>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Cellulare</th>
                                    <th>Argomento</th>
                                    <th>Messaggio</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($messages as $msg): ?>
                                <tr class="<?= $msg['status'] === 'new' ? 'table-warning' : '' ?>">
                                    <td>
                                        <?= getMessageStatusBadge($msg['status']) ?>
                                        <?php if ($msg['published']): ?>
                                        <span class="badge bg-success">Pub</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-nowrap small"><?= formatDate($msg['created_at']) ?></td>
                                    <td><?= e($msg['name'] ?: '—') ?></td>
                                    <td class="text-nowrap small"><?= e($msg['phone'] ?? '—') ?></td>
                                    <td><span class="badge bg-secondary"><?= e($msg['topic']) ?></span></td>
                                    <td><?= e(truncate($msg['message'], 80)) ?></td>
                                    <td>
                                        <a href="message.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <?php if ($msg['status'] !== 'replied'): ?>
                                        <a href="reply.php?id=<?= $msg['id'] ?>" class="btn btn-sm btn-primary">
                                            <i class="bi bi-reply"></i>
                                        </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php elseif ($activeTab === 'proposals'): ?>
            <!-- Proposals Tab -->
            <div class="card">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">
                        <i class="bi bi-lightbulb me-2"></i>Proposte cittadini
                    </h5>
                    <div class="btn-group" role="group">
                        <a href="?tab=proposals&filter=all" class="btn btn-sm <?= ($_GET['filter'] ?? 'all') === 'all' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Tutte
                        </a>
                        <a href="?tab=proposals&filter=new" class="btn btn-sm <?= ($_GET['filter'] ?? '') === 'new' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Nuove (<?= $stats['proposals_new'] ?>)
                        </a>
                        <a href="?tab=proposals&filter=seen" class="btn btn-sm <?= ($_GET['filter'] ?? '') === 'seen' ? 'btn-primary' : 'btn-outline-primary' ?>">
                            Lette
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <?php if (empty($proposals)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-lightbulb" style="font-size: 3rem;"></i>
                        <p class="mt-2">Nessuna proposta trovata</p>
                    </div>
                    <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Stato</th>
                                    <th>Data</th>
                                    <th>Nome</th>
                                    <th>Area</th>
                                    <th>Problema</th>
                                    <th>Azioni</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($proposals as $prop): ?>
                                <tr class="<?= $prop['status'] === 'new' ? 'table-warning' : '' ?>">
                                    <td><?= getMessageStatusBadge($prop['status']) ?></td>
                                    <td class="text-nowrap small"><?= formatDate($prop['created_at']) ?></td>
                                    <td><?= e($prop['name'] ?: '—') ?></td>
                                    <td><span class="badge bg-info"><?= e($prop['area']) ?></span></td>
                                    <td><?= e(truncate($prop['problem'], 80)) ?></td>
                                    <td>
                                        <a href="proposals.php?id=<?= $prop['id'] ?>" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> Vedi
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
