<?php
/**
 * Public Q&A page with pagination and search
 */

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pdo = getDB();

// Pagination settings
$perPage = 10;
$page = max(1, (int)($_GET['page'] ?? 1));
$offset = ($page - 1) * $perPage;

// Search query
$search = trim($_GET['q'] ?? '');

// Check for slug (single Q&A view)
$slug = trim($_GET['slug'] ?? '');

if ($slug) {
    // Single Q&A view
    $stmt = $pdo->prepare("
        SELECT m.name, m.message, m.topic, r.reply_text, r.replied_at, r.public_slug
        FROM replies r
        JOIN messages m ON r.message_id = m.id
        WHERE r.published = 1 AND r.public_slug = ?
    ");
    $stmt->execute([$slug]);
    $singleQA = $stmt->fetch();

    if (!$singleQA) {
        http_response_code(404);
        $pageTitle = 'Non trovato - ' . SITE_NAME;
        $showNav = true;
        $basePath = '';
        include __DIR__ . '/includes/header.php';
        echo '<div class="container py-5 text-center"><h1>Pagina non trovata</h1><p>La domanda richiesta non esiste o non è stata pubblicata.</p><a href="domande.php" class="btn btn-primary">Torna alle domande</a></div>';
        include __DIR__ . '/includes/footer.php';
        exit;
    }

    $pageTitle = 'Domanda e Risposta - ' . SITE_NAME;
    $pageDescription = truncate($singleQA['message'], 150);
} else {
    // List view
    // Build query
    $whereClause = 'WHERE r.published = 1';
    $params = [];

    if ($search) {
        $whereClause .= ' AND (m.message LIKE ? OR r.reply_text LIKE ?)';
        $searchParam = '%' . $search . '%';
        $params[] = $searchParam;
        $params[] = $searchParam;
    }

    // Get total count
    $countStmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM replies r
        JOIN messages m ON r.message_id = m.id
        $whereClause
    ");
    $countStmt->execute($params);
    $totalItems = $countStmt->fetchColumn();
    $totalPages = max(1, ceil($totalItems / $perPage));

    // Ensure page is within bounds
    if ($page > $totalPages) {
        $page = $totalPages;
        $offset = ($page - 1) * $perPage;
    }

    // Get items
    $stmt = $pdo->prepare("
        SELECT m.name, m.message, m.topic, r.reply_text, r.replied_at, r.public_slug
        FROM replies r
        JOIN messages m ON r.message_id = m.id
        $whereClause
        ORDER BY r.replied_at DESC
        LIMIT ? OFFSET ?
    ");
    $params[] = $perPage;
    $params[] = $offset;
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    $pageTitle = 'Domande e Risposte - ' . SITE_NAME;
    $pageDescription = 'Tutte le domande e risposte pubblicate.';
}

$showNav = true;
$basePath = '';

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <?php if ($slug && isset($singleQA)): ?>
    <!-- Single Q&A View -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">Home</a></li>
            <li class="breadcrumb-item"><a href="domande.php">Domande</a></li>
            <li class="breadcrumb-item active" aria-current="page">Dettaglio</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card qa-card">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <span class="badge bg-secondary"><?= e($singleQA['topic']) ?></span>
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= formatDate($singleQA['replied_at']) ?>
                        </small>
                    </div>

                    <div class="question mb-4">
                        <h5 class="text-muted mb-3">
                            <i class="bi bi-chat-left-quote me-2"></i>La domanda
                        </h5>
                        <p class="lead"><?= nl2br(e($singleQA['message'])) ?></p>
                        <?php if ($singleQA['name']): ?>
                        <p class="text-muted small">— <?= e($singleQA['name']) ?></p>
                        <?php endif; ?>
                    </div>

                    <hr>

                    <div class="answer mt-4">
                        <h5 class="text-primary mb-3">
                            <i class="bi bi-chat-right-text me-2"></i>La risposta
                        </h5>
                        <p><?= nl2br(e($singleQA['reply_text'])) ?></p>
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <a href="domande.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Torna alle domande
                </a>
            </div>
        </div>
    </div>

    <?php else: ?>
    <!-- List View -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h1 class="h2 mb-0">Domande e Risposte</h1>
            <p class="text-muted">Le domande pubblicate con il consenso dei cittadini</p>
        </div>
        <div class="col-md-4">
            <form method="GET" action="domande.php">
                <div class="input-group">
                    <input type="text" class="form-control" name="q" placeholder="Cerca..."
                           value="<?= e($search) ?>">
                    <button class="btn btn-primary" type="submit">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <?php if ($search): ?>
    <div class="alert alert-info d-flex align-items-center mb-4">
        <i class="bi bi-info-circle me-2"></i>
        <div>
            Risultati per: <strong><?= e($search) ?></strong>
            (<?= $totalItems ?> <?= $totalItems === 1 ? 'risultato' : 'risultati' ?>)
            <a href="domande.php" class="ms-2">Rimuovi filtro</a>
        </div>
    </div>
    <?php endif; ?>

    <?php if (empty($items)): ?>
    <div class="text-center py-5">
        <i class="bi bi-chat-dots" style="font-size: 4rem; color: #ddd;"></i>
        <p class="mt-3 text-muted">
            <?= $search ? 'Nessun risultato trovato per la tua ricerca.' : 'Non ci sono ancora domande pubblicate.' ?>
        </p>
    </div>
    <?php else: ?>
    <div class="row g-4">
        <?php foreach ($items as $item): ?>
        <div class="col-12">
            <div class="card qa-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <span class="badge bg-secondary"><?= e($item['topic']) ?></span>
                        <small class="text-muted">
                            <i class="bi bi-calendar3 me-1"></i>
                            <?= formatDate($item['replied_at']) ?>
                        </small>
                    </div>

                    <div class="question mb-3">
                        <strong><?= e(truncate($item['message'], 200)) ?></strong>
                    </div>

                    <div class="answer">
                        <p class="mb-2 text-muted"><?= e(truncate($item['reply_text'], 250)) ?></p>
                    </div>

                    <?php if ($item['public_slug']): ?>
                    <a href="domande.php?slug=<?= e($item['public_slug']) ?>" class="btn btn-sm btn-outline-primary">
                        Leggi tutto <i class="bi bi-arrow-right ms-1"></i>
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Pagination -->
    <?php if ($totalPages > 1): ?>
    <nav aria-label="Navigazione pagine" class="mt-5">
        <ul class="pagination justify-content-center">
            <?php if ($page > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page - 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?>">
                    <i class="bi bi-chevron-left"></i>
                </a>
            </li>
            <?php endif; ?>

            <?php
            $startPage = max(1, $page - 2);
            $endPage = min($totalPages, $page + 2);

            if ($startPage > 1): ?>
            <li class="page-item">
                <a class="page-link" href="?page=1<?= $search ? '&q=' . urlencode($search) : '' ?>">1</a>
            </li>
            <?php if ($startPage > 2): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <?php endif; ?>

            <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
            <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                <a class="page-link" href="?page=<?= $i ?><?= $search ? '&q=' . urlencode($search) : '' ?>"><?= $i ?></a>
            </li>
            <?php endfor; ?>

            <?php if ($endPage < $totalPages): ?>
            <?php if ($endPage < $totalPages - 1): ?>
            <li class="page-item disabled"><span class="page-link">...</span></li>
            <?php endif; ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $totalPages ?><?= $search ? '&q=' . urlencode($search) : '' ?>"><?= $totalPages ?></a>
            </li>
            <?php endif; ?>

            <?php if ($page < $totalPages): ?>
            <li class="page-item">
                <a class="page-link" href="?page=<?= $page + 1 ?><?= $search ? '&q=' . urlencode($search) : '' ?>">
                    <i class="bi bi-chevron-right"></i>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>
    <?php endif; ?>
    <?php endif; ?>
    <?php endif; ?>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
