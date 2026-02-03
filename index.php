<?php
/**
 * Homepage - Single page with all sections
 */

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/csrf.php';

$pdo = getDB();

// Get latest 3 published Q&A
$stmt = $pdo->query("
    SELECT m.name, m.message, r.reply_text, r.replied_at, r.public_slug
    FROM replies r
    JOIN messages m ON r.message_id = m.id
    WHERE r.published = 1
    ORDER BY r.replied_at DESC
    LIMIT 3
");
$latestQA = $stmt->fetchAll();

// Get program items grouped by area
$stmt = $pdo->query("
    SELECT * FROM program_items
    ORDER BY area, sort_order
");
$allProgramItems = $stmt->fetchAll();

// Group by area
$programByArea = [];
foreach ($allProgramItems as $item) {
    $programByArea[$item['area']][] = $item;
}

// Check for success message
$showSuccess = isset($_GET['sent']) && $_GET['sent'] === '1';
$showProposalSuccess = isset($_GET['proposal']) && $_GET['proposal'] === '1';

$pageTitle = SITE_NAME;
$pageDescription = 'Scrivi a Maria Laura Orrù e ricevi una risposta personale. Trasparenza, ascolto e partecipazione.';
$showNav = true;
$basePath = '';

include __DIR__ . '/includes/header.php';
?>

<!-- SEZIONE A: HERO + FORM -->
<section class="hero-section" id="form">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8 text-center">
                <h1 class="hero-title">Scrivimi.</h1>
                <p class="hero-subtitle mb-2">
                    Raccontami un problema, una critica o un'idea. Ti risponderò io.
                </p>
                <p class="hero-microcopy">
                    Non sempre potrò dirti "sì". Ma ti dirò sempre la verità.
                </p>
            </div>
        </div>
    </div>
</section>

<div class="container">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="form-card p-4 p-md-5">
                <?php if ($showSuccess): ?>
                <div class="alert alert-success d-flex align-items-center" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        <strong>Grazie!</strong> Il tuo messaggio è stato inviato. Ti risponderò appena possibile.
                    </div>
                </div>
                <?php endif; ?>

                <form action="submit_message.php" method="POST" id="messageForm">
                    <?= csrfField() ?>

                    <div class="mb-3">
                        <label for="name" class="form-label">Nome <span class="text-muted fw-normal">(facoltativo)</span></label>
                        <input type="text" class="form-control" id="name" name="name" placeholder="Come vuoi essere chiamato/a">
                    </div>

                    <div class="mb-3">
                        <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="Per ricevere la risposta">
                    </div>

                    <div class="mb-3">
                        <label for="phone" class="form-label">Cellulare <span class="text-muted fw-normal">(facoltativo)</span></label>
                        <input type="tel" class="form-control" id="phone" name="phone" placeholder="Es. 333 1234567">
                    </div>

                    <div class="mb-3">
                        <label for="topic" class="form-label">Argomento <span class="text-danger">*</span></label>
                        <select class="form-select" id="topic" name="topic" required>
                            <option value="">Seleziona...</option>
                            <?php foreach (getTopics() as $value => $label): ?>
                            <option value="<?= e($value) ?>"><?= e($label) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="message" class="form-label">Il tuo messaggio <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="message" name="message" rows="5" required placeholder="Scrivi qui il tuo messaggio..."></textarea>
                    </div>

                    <!-- Honeypot field -->
                    <div class="hp-field" aria-hidden="true">
                        <label for="website">Website</label>
                        <input type="text" name="website" id="website" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="privacy" name="privacy" required>
                            <label class="form-check-label" for="privacy">
                                Ho letto e accetto la <a href="privacy.php" target="_blank">Privacy Policy</a> <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-send me-2"></i>Invia messaggio
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- SEZIONE B: ULTIME Q&A PUBBLICATE -->
<section class="section" id="risposte">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Ultime domande e risposte</h2>
            <p class="section-subtitle mx-auto">
                Alcune delle domande ricevute e le risposte pubblicate con il consenso dei cittadini.
            </p>
        </div>

        <?php if (empty($latestQA)): ?>
        <div class="text-center text-muted py-5">
            <i class="bi bi-chat-dots" style="font-size: 3rem; opacity: 0.3;"></i>
            <p class="mt-3">Non ci sono ancora domande e risposte pubblicate.</p>
        </div>
        <?php else: ?>
        <div class="row g-4">
            <?php foreach ($latestQA as $qa): ?>
            <div class="col-md-4">
                <div class="card qa-card h-100">
                    <div class="card-body">
                        <div class="question mb-3">
                            <?= e(truncate($qa['message'], 120)) ?>
                        </div>
                        <div class="answer">
                            <p class="mb-2"><?= e(truncate($qa['reply_text'], 150)) ?></p>
                            <small class="text-muted">
                                <i class="bi bi-calendar3 me-1"></i>
                                <?= formatDate($qa['replied_at']) ?>
                            </small>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="domande.php" class="btn btn-outline-primary">
                <i class="bi bi-list-ul me-2"></i>Mostra tutte le domande
            </a>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- SEZIONE C: PROGRAMMA 2021 -->
<section class="section section-accent" id="programma">
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">Programma 2021: cosa è stato fatto</h2>
            <p class="section-subtitle mx-auto">
                Qui trovi le promesse del 2021 e lo stato aggiornato, con una nota quando qualcosa non è stato possibile o ha richiesto tempi più lunghi.
            </p>
        </div>

        <?php if (empty($programByArea)): ?>
        <div class="text-center text-muted py-5">
            <p>Il programma verrà pubblicato a breve.</p>
        </div>
        <?php else: ?>
        <div class="accordion" id="programAccordion">
            <?php $areaIndex = 0; foreach ($programByArea as $area => $items): ?>
            <div class="accordion-item">
                <h2 class="accordion-header">
                    <button class="accordion-button <?= $areaIndex > 0 ? 'collapsed' : '' ?>" type="button"
                            data-bs-toggle="collapse" data-bs-target="#collapse<?= $areaIndex ?>"
                            aria-expanded="<?= $areaIndex === 0 ? 'true' : 'false' ?>">
                        <i class="bi bi-folder2-open me-2"></i>
                        <?= e($area) ?>
                        <span class="badge bg-secondary ms-2"><?= count($items) ?> voci</span>
                    </button>
                </h2>
                <div id="collapse<?= $areaIndex ?>" class="accordion-collapse collapse <?= $areaIndex === 0 ? 'show' : '' ?>"
                     data-bs-parent="#programAccordion">
                    <div class="accordion-body">
                        <?php foreach ($items as $item): ?>
                        <div class="program-item">
                            <div class="flex-grow-1">
                                <div class="title"><?= e($item['title']) ?></div>
                                <?php if ($item['public_note']): ?>
                                <p class="note"><?= e($item['public_note']) ?></p>
                                <?php endif; ?>
                            </div>
                            <?= getStatusBadge($item['status']) ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php $areaIndex++; endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- SEZIONE D: PROPOSTA PROGRAMMA -->
<section class="section" id="proposta">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="text-center mb-5">
                    <h2 class="section-title">Scriviamo insieme il prossimo programma</h2>
                    <p class="section-subtitle mx-auto">
                        Non prometto di fare tutto. Prometto di decidere meglio, insieme.
                    </p>
                    <p class="text-muted">
                        Raccontami il problema e la tua proposta concreta.
                    </p>
                </div>

                <?php if ($showProposalSuccess): ?>
                <div class="alert alert-success d-flex align-items-center mb-4" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>
                    <div>
                        <strong>Grazie!</strong> La tua proposta è stata registrata. La leggeremo con attenzione.
                    </div>
                </div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body p-4">
                        <form action="submit_proposal.php" method="POST" id="proposalForm">
                            <?= csrfField() ?>

                            <div class="mb-3">
                                <label for="proposal_name" class="form-label">Nome <span class="text-muted fw-normal">(facoltativo)</span></label>
                                <input type="text" class="form-control" id="proposal_name" name="name" placeholder="Come vuoi essere chiamato/a">
                            </div>

                            <div class="mb-3">
                                <label for="proposal_email" class="form-label">Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="proposal_email" name="email" required placeholder="Per eventuali approfondimenti">
                            </div>

                            <div class="mb-3">
                                <label for="area" class="form-label">Area tematica <span class="text-danger">*</span></label>
                                <select class="form-select" id="area" name="area" required>
                                    <option value="">Seleziona...</option>
                                    <?php foreach (getAreas() as $value => $label): ?>
                                    <option value="<?= e($value) ?>"><?= e($label) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="problem" class="form-label">Qual è il problema? <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="problem" name="problem" rows="3" required placeholder="Descrivi brevemente il problema che vorresti affrontare..."></textarea>
                            </div>

                            <div class="mb-3">
                                <label for="proposal_text" class="form-label">La tua proposta <span class="text-danger">*</span></label>
                                <textarea class="form-control" id="proposal_text" name="proposal" rows="4" required placeholder="Come pensi si potrebbe risolvere? Hai un'idea concreta?"></textarea>
                            </div>

                            <!-- Honeypot field -->
                            <div class="hp-field" aria-hidden="true">
                                <label for="website2">Website</label>
                                <input type="text" name="website" id="website2" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="mb-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="privacy2" name="privacy" required>
                                    <label class="form-check-label" for="privacy2">
                                        Ho letto e accetto la <a href="privacy.php" target="_blank">Privacy Policy</a> <span class="text-danger">*</span>
                                    </label>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-lightbulb me-2"></i>Invia proposta
                            </button>
                        </form>
                    </div>
                </div>

                <p class="text-muted text-center mt-3 small">
                    <i class="bi bi-info-circle me-1"></i>
                    Questa sezione è per la co-programmazione, non per segnalazioni urgenti.
                </p>
            </div>
        </div>
    </div>
</section>

<?php include __DIR__ . '/includes/footer.php'; ?>
