<?php
/**
 * Admin - Program 2021 Management (CRUD)
 */

session_start();

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/csrf.php';

requireAdmin();

$pdo = getDB();

$action = $_GET['action'] ?? 'list';
$id = (int)($_GET['id'] ?? 0);
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    requireCsrfToken();

    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'save') {
        $itemId = (int)($_POST['id'] ?? 0);
        $area = trim($_POST['area'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $status = $_POST['status'] ?? 'non_fatto';
        $publicNote = trim($_POST['public_note'] ?? '');
        $sortOrder = (int)($_POST['sort_order'] ?? 0);

        if (empty($area) || empty($title)) {
            $error = 'Area e titolo sono obbligatori.';
        } elseif (!in_array($status, ['fatto', 'in_parte', 'non_fatto'])) {
            $error = 'Stato non valido.';
        } else {
            try {
                $now = date('c');

                if ($itemId > 0) {
                    // Update
                    $stmt = $pdo->prepare("
                        UPDATE program_items
                        SET area = ?, title = ?, description = ?, status = ?, public_note = ?, sort_order = ?, updated_at = ?
                        WHERE id = ?
                    ");
                    $stmt->execute([$area, $title, $description ?: null, $status, $publicNote ?: null, $sortOrder, $now, $itemId]);
                    $success = 'Voce aggiornata.';
                } else {
                    // Insert
                    $stmt = $pdo->prepare("
                        INSERT INTO program_items (area, title, description, status, public_note, sort_order, updated_at)
                        VALUES (?, ?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->execute([$area, $title, $description ?: null, $status, $publicNote ?: null, $sortOrder, $now]);
                    $success = 'Voce aggiunta.';
                }

                redirect('program.php', $success, 'success');

            } catch (PDOException $e) {
                error_log('Program item save failed: ' . $e->getMessage());
                $error = 'Errore nel salvataggio.';
            }
        }
    } elseif ($postAction === 'delete') {
        $itemId = (int)($_POST['id'] ?? 0);
        if ($itemId > 0) {
            try {
                $stmt = $pdo->prepare("DELETE FROM program_items WHERE id = ?");
                $stmt->execute([$itemId]);
                redirect('program.php', 'Voce eliminata.', 'success');
            } catch (PDOException $e) {
                error_log('Program item delete failed: ' . $e->getMessage());
                $error = 'Errore nell\'eliminazione.';
            }
        }
    }
}

// Fetch data based on action
$item = null;
$items = [];

if ($action === 'edit' && $id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM program_items WHERE id = ?");
    $stmt->execute([$id]);
    $item = $stmt->fetch();
    if (!$item) {
        redirect('program.php', 'Voce non trovata.', 'danger');
    }
} elseif ($action === 'list') {
    $filterArea = $_GET['area'] ?? '';

    $whereClause = '';
    $params = [];
    if ($filterArea) {
        $whereClause = "WHERE area = ?";
        $params[] = $filterArea;
    }

    $stmt = $pdo->prepare("
        SELECT * FROM program_items
        $whereClause
        ORDER BY area, sort_order, title
    ");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    // Group by area for display
    $itemsByArea = [];
    foreach ($items as $i) {
        $itemsByArea[$i['area']][] = $i;
    }
}

$flash = getFlashMessage();
$areas = getProgramAreas();
$statuses = [
    'fatto' => 'FATTO',
    'in_parte' => 'IN PARTE',
    'non_fatto' => 'NON FATTO',
];
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Programma 2021 - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <link href="../assets/style.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-4">
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="dashboard.php">Dashboard</a></li>
                <li class="breadcrumb-item <?= $action === 'list' ? 'active' : '' ?>">
                    <?php if ($action !== 'list'): ?>
                    <a href="program.php">Programma 2021</a>
                    <?php else: ?>
                    Programma 2021
                    <?php endif; ?>
                </li>
                <?php if ($action === 'add'): ?>
                <li class="breadcrumb-item active">Aggiungi</li>
                <?php elseif ($action === 'edit'): ?>
                <li class="breadcrumb-item active">Modifica</li>
                <?php endif; ?>
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

        <?php if ($action === 'add' || $action === 'edit'): ?>
        <!-- Add/Edit Form -->
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-white">
                        <h5 class="mb-0">
                            <i class="bi bi-<?= $action === 'add' ? 'plus-circle' : 'pencil' ?> me-2"></i>
                            <?= $action === 'add' ? 'Aggiungi voce' : 'Modifica voce' ?>
                        </h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <?= csrfField() ?>
                            <input type="hidden" name="action" value="save">
                            <input type="hidden" name="id" value="<?= $item['id'] ?? 0 ?>">

                            <div class="mb-3">
                                <label for="area" class="form-label">Area tematica <span class="text-danger">*</span></label>
                                <select class="form-select" id="area" name="area" required>
                                    <option value="">Seleziona...</option>
                                    <?php foreach ($areas as $a): ?>
                                    <option value="<?= e($a) ?>" <?= ($item['area'] ?? '') === $a ? 'selected' : '' ?>>
                                        <?= e($a) ?>
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="title" class="form-label">Titolo <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="title" name="title"
                                       value="<?= e($item['title'] ?? '') ?>" required>
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Descrizione</label>
                                <textarea class="form-control" id="description" name="description"
                                          rows="2"><?= e($item['description'] ?? '') ?></textarea>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="status" class="form-label">Stato <span class="text-danger">*</span></label>
                                    <select class="form-select" id="status" name="status" required>
                                        <?php foreach ($statuses as $value => $label): ?>
                                        <option value="<?= e($value) ?>" <?= ($item['status'] ?? 'non_fatto') === $value ? 'selected' : '' ?>>
                                            <?= e($label) ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="sort_order" class="form-label">Ordine</label>
                                    <input type="number" class="form-control" id="sort_order" name="sort_order"
                                           value="<?= (int)($item['sort_order'] ?? 0) ?>" min="0">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="public_note" class="form-label">Nota pubblica</label>
                                <textarea class="form-control" id="public_note" name="public_note"
                                          rows="2" placeholder="Spiegazione visibile ai cittadini (perché, tempi, vincoli...)"><?= e($item['public_note'] ?? '') ?></textarea>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="program.php" class="btn btn-outline-secondary">
                                    <i class="bi bi-x-lg me-1"></i>Annulla
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="bi bi-check-lg me-1"></i>Salva
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <?php else: ?>
        <!-- List View -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="mb-0">
                <i class="bi bi-list-check me-2"></i>Programma 2021
            </h4>
            <a href="program.php?action=add" class="btn btn-primary">
                <i class="bi bi-plus-lg me-1"></i>Aggiungi voce
            </a>
        </div>

        <!-- Filter -->
        <div class="card mb-4">
            <div class="card-body py-2">
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <span class="text-muted me-2">Filtra per area:</span>
                    <a href="program.php" class="btn btn-sm <?= empty($_GET['area']) ? 'btn-primary' : 'btn-outline-primary' ?>">
                        Tutte
                    </a>
                    <?php foreach ($areas as $a): ?>
                    <a href="program.php?area=<?= urlencode($a) ?>"
                       class="btn btn-sm <?= ($_GET['area'] ?? '') === $a ? 'btn-primary' : 'btn-outline-primary' ?>">
                        <?= e($a) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <?php if (empty($itemsByArea)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-inbox" style="font-size: 3rem;"></i>
            <p class="mt-2">Nessuna voce trovata.</p>
        </div>
        <?php else: ?>
        <?php foreach ($itemsByArea as $areaName => $areaItems): ?>
        <div class="card mb-4">
            <div class="card-header bg-white">
                <h6 class="mb-0">
                    <i class="bi bi-folder2-open me-2"></i>
                    <?= e($areaName) ?>
                    <span class="badge bg-secondary ms-2"><?= count($areaItems) ?></span>
                </h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Ordine</th>
                                <th>Titolo</th>
                                <th style="width: 120px;">Stato</th>
                                <th>Nota</th>
                                <th style="width: 120px;">Azioni</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($areaItems as $i): ?>
                            <tr>
                                <td class="text-center text-muted"><?= $i['sort_order'] ?></td>
                                <td>
                                    <strong><?= e($i['title']) ?></strong>
                                    <?php if ($i['description']): ?>
                                    <br><small class="text-muted"><?= e(truncate($i['description'], 80)) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td><?= getStatusBadge($i['status']) ?></td>
                                <td class="text-muted small"><?= e(truncate($i['public_note'] ?? '', 60)) ?></td>
                                <td>
                                    <a href="program.php?action=edit&id=<?= $i['id'] ?>" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form method="POST" class="d-inline" onsubmit="return confirm('Eliminare questa voce?')">
                                        <?= csrfField() ?>
                                        <input type="hidden" name="action" value="delete">
                                        <input type="hidden" name="id" value="<?= $i['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
        <?php endif; ?>

        <div class="mt-4">
            <a href="dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i>Torna alla dashboard
            </a>
        </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
