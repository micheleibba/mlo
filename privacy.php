<?php
/**
 * Privacy Policy page
 */

session_start();

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Policy - ' . SITE_NAME;
$pageDescription = 'Informativa sul trattamento dei dati personali.';
$showNav = true;
$basePath = '';

include __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <h1 class="mb-4">Informativa sulla Privacy</h1>
            <p class="lead text-muted mb-5">
                Informativa sul trattamento dei dati personali ai sensi del Regolamento (UE) 2016/679 (GDPR)
            </p>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">1. Titolare del trattamento</h2>
                    <p>
                        Il Titolare del trattamento dei dati personali è il <strong>Comune di Elmas</strong>,
                        con sede in [indirizzo], P.IVA/C.F. [codice fiscale].
                    </p>
                    <p>
                        Email: <a href="mailto:privacy@comune.elmas.ca.it">privacy@comune.elmas.ca.it</a><br>
                        PEC: [pec@comune.elmas.ca.it]
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">2. Dati raccolti</h2>
                    <p>Attraverso questo sito raccogliamo i seguenti dati personali:</p>
                    <ul>
                        <li><strong>Dati identificativi</strong>: nome (facoltativo), indirizzo email</li>
                        <li><strong>Dati di navigazione</strong>: indirizzo IP (in forma anonimizzata), user agent del browser</li>
                        <li><strong>Contenuti forniti</strong>: testo dei messaggi, domande e proposte inviate</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">3. Finalità del trattamento</h2>
                    <p>I dati personali sono trattati per le seguenti finalità:</p>
                    <ul>
                        <li>Rispondere alle domande e richieste inviate tramite il modulo di contatto</li>
                        <li>Gestire le proposte per il programma partecipativo</li>
                        <li>Pubblicare le risposte alle domande (previo consenso esplicito)</li>
                        <li>Prevenire abusi e spam attraverso controlli automatici</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">4. Base giuridica</h2>
                    <p>Il trattamento dei dati si basa su:</p>
                    <ul>
                        <li><strong>Consenso</strong>: per l'invio delle comunicazioni e l'eventuale pubblicazione delle domande/risposte</li>
                        <li><strong>Interesse legittimo</strong>: per le misure di sicurezza anti-spam</li>
                        <li><strong>Interesse pubblico</strong>: per la gestione della partecipazione civica</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">5. Conservazione dei dati</h2>
                    <p>
                        I dati personali saranno conservati per il tempo necessario a perseguire le finalità
                        per cui sono stati raccolti e comunque non oltre:
                    </p>
                    <ul>
                        <li><strong>Messaggi e risposte</strong>: 5 anni dalla risposta</li>
                        <li><strong>Proposte</strong>: fino al termine del mandato amministrativo</li>
                        <li><strong>Dati di navigazione</strong>: 12 mesi</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">6. Pubblicazione delle risposte</h2>
                    <p>
                        Le risposte alle domande potranno essere pubblicate sul sito solo con il consenso esplicito
                        del cittadino. In caso di pubblicazione:
                    </p>
                    <ul>
                        <li>Il nome potrà essere omesso su richiesta</li>
                        <li>L'indirizzo email non sarà mai pubblicato</li>
                        <li>Il testo potrà essere editato per rimuovere riferimenti personali</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">7. Diritti dell'interessato</h2>
                    <p>In qualità di interessato, hai diritto di:</p>
                    <ul>
                        <li>Accedere ai tuoi dati personali</li>
                        <li>Rettificare dati inesatti</li>
                        <li>Cancellare i dati (diritto all'oblio)</li>
                        <li>Limitare il trattamento</li>
                        <li>Opporti al trattamento</li>
                        <li>Revocare il consenso in qualsiasi momento</li>
                        <li>Proporre reclamo all'Autorità Garante per la protezione dei dati personali</li>
                    </ul>
                    <p class="mt-3">
                        Per esercitare i tuoi diritti, scrivi a:
                        <a href="mailto:privacy@comune.elmas.ca.it">privacy@comune.elmas.ca.it</a>
                    </p>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">8. Sicurezza</h2>
                    <p>
                        I dati sono protetti con misure tecniche e organizzative adeguate, tra cui:
                    </p>
                    <ul>
                        <li>Crittografia delle connessioni (HTTPS)</li>
                        <li>Anonimizzazione degli indirizzi IP</li>
                        <li>Accesso limitato ai dati da parte del personale autorizzato</li>
                        <li>Backup regolari e sicuri</li>
                    </ul>
                </div>
            </div>

            <div class="card mb-4">
                <div class="card-body">
                    <h2 class="h5 mb-3">9. Cookie</h2>
                    <p>
                        Questo sito utilizza esclusivamente cookie tecnici necessari al funzionamento
                        (sessione utente). Non utilizziamo cookie di profilazione o di terze parti
                        per finalità pubblicitarie.
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <h2 class="h5 mb-3">10. Aggiornamenti</h2>
                    <p>
                        Questa informativa può essere aggiornata. Ti invitiamo a consultarla periodicamente.
                    </p>
                    <p class="text-muted small mb-0">
                        Ultimo aggiornamento: <?= date('d/m/Y') ?>
                    </p>
                </div>
            </div>

            <div class="text-center mt-5">
                <a href="index.php" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-left me-2"></i>Torna alla home
                </a>
            </div>
        </div>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
