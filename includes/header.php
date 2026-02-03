<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($pageDescription ?? 'Scrivi al Sindaco di Elmas e ricevi una risposta.') ?>">
    <title><?= e($pageTitle ?? SITE_NAME) ?></title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="<?= $basePath ?? '' ?>assets/style.css" rel="stylesheet">
</head>
<body>
    <?php if (isset($showNav) && $showNav): ?>
    <nav class="navbar navbar-expand-lg navbar-light bg-white border-bottom sticky-top">
        <div class="container">
            <a class="navbar-brand fw-bold text-primary" href="<?= $basePath ?? '' ?>index.php">
                <i class="bi bi-envelope-heart me-2"></i>Scrivimi
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath ?? '' ?>index.php#form">Scrivi</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath ?? '' ?>domande.php">Domande & Risposte</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath ?? '' ?>index.php#programma">Programma 2021</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= $basePath ?? '' ?>index.php#proposta">Proponi</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main>
