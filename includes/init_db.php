<?php
/**
 * Database initialization - creates tables and seed data
 */

function initializeDatabase(PDO $pdo): void
{
    // Create messages table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT NOT NULL,
            topic TEXT NOT NULL,
            message TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'new',
            created_at TEXT NOT NULL,
            ip_hash TEXT,
            user_agent TEXT
        )
    ");

    // Create replies table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS replies (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            message_id INTEGER NOT NULL UNIQUE,
            reply_text TEXT NOT NULL,
            published INTEGER NOT NULL DEFAULT 0,
            public_slug TEXT UNIQUE,
            replied_at TEXT NOT NULL,
            email_sent_at TEXT,
            FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE
        )
    ");

    // Create program_items table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS program_items (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            area TEXT NOT NULL,
            title TEXT NOT NULL,
            description TEXT,
            status TEXT NOT NULL DEFAULT 'non_fatto',
            public_note TEXT,
            sort_order INTEGER NOT NULL DEFAULT 0,
            updated_at TEXT NOT NULL
        )
    ");

    // Create proposals table
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS proposals (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT,
            email TEXT NOT NULL,
            area TEXT NOT NULL,
            problem TEXT NOT NULL,
            proposal TEXT NOT NULL,
            status TEXT NOT NULL DEFAULT 'new',
            created_at TEXT NOT NULL,
            ip_hash TEXT,
            user_agent TEXT
        )
    ");

    // Create indexes
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_created_at ON messages(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_messages_status ON messages(status)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_replies_published ON replies(published, replied_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_program_items_area ON program_items(area, sort_order)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_proposals_created_at ON proposals(created_at)");
    $pdo->exec("CREATE INDEX IF NOT EXISTS idx_proposals_status ON proposals(status)");

    // Insert sample program items
    $now = date('c');
    $programItems = [
        // Partecipazione
        ['Partecipazione', 'Bilancio partecipativo', 'Coinvolgimento diretto dei cittadini nelle scelte di spesa', 'fatto', 'Avviato nel 2022 con ottima partecipazione', 1],
        ['Partecipazione', 'Consulte di quartiere', 'Creazione di tavoli permanenti con i cittadini', 'in_parte', 'Avviate 3 su 5 consulte previste', 2],
        ['Partecipazione', 'Trasparenza atti comunali', 'Pubblicazione online di tutti gli atti', 'fatto', null, 3],

        // Ambiente
        ['Ambiente', 'Raccolta differenziata porta a porta', 'Estensione del servizio a tutto il territorio', 'fatto', 'Raggiunto 78% di differenziata', 1],
        ['Ambiente', 'Piantumazione 500 alberi', 'Nuovo verde pubblico in aree urbane', 'in_parte', 'Piantati 320 alberi, completamento previsto entro 2025', 2],
        ['Ambiente', 'Isole ecologiche intelligenti', 'Installazione cassonetti smart', 'non_fatto', 'Progetto rinviato per mancanza fondi regionali', 3],

        // Sicurezza
        ['Sicurezza', 'Potenziamento illuminazione pubblica', 'LED in tutte le strade comunali', 'fatto', 'Completato con risparmio energetico del 40%', 1],
        ['Sicurezza', 'Videosorveglianza centro storico', 'Nuove telecamere nelle aree critiche', 'fatto', null, 2],
        ['Sicurezza', 'Vigile di quartiere', 'Presenza fissa della polizia locale', 'non_fatto', 'Organico insufficiente, in attesa di nuove assunzioni', 3],

        // Urbanistica
        ['Urbanistica', 'Piano del traffico', 'Revisione viabilità e parcheggi', 'in_parte', 'Approvato piano, in corso realizzazione', 1],
        ['Urbanistica', 'Riqualificazione piazza centrale', 'Pedonalizzazione e arredo urbano', 'fatto', 'Inaugurata a giugno 2023', 2],
        ['Urbanistica', 'Abbattimento barriere architettoniche', 'Marciapiedi e accessi pubblici', 'in_parte', 'Interventi in corso, priorità edifici pubblici', 3],

        // Giovani
        ['Giovani', 'Spazio giovani comunale', 'Apertura centro aggregazione', 'fatto', 'Aperto presso ex biblioteca', 1],
        ['Giovani', 'Borse di studio comunali', 'Sostegno merito scolastico', 'fatto', 'Erogate 45 borse nel 2023', 2],
        ['Giovani', 'Skate park', 'Area sportiva per giovani', 'non_fatto', 'Area individuata, in attesa finanziamento', 3],

        // Cultura
        ['Cultura', 'Rassegna estiva eventi', 'Calendario manifestazioni gratuite', 'fatto', 'Edizione 2023 con 25 eventi', 1],
        ['Cultura', 'Digitalizzazione archivio storico', 'Accesso online documenti storici', 'in_parte', 'Completato 60% del materiale', 2],

        // Economia
        ['Economia', 'Sportello imprese', 'Assistenza gratuita per attività locali', 'fatto', 'Attivo dal 2022', 1],
        ['Economia', 'Mercato contadino settimanale', 'Valorizzazione prodotti locali', 'fatto', 'Ogni sabato in piazza', 2],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO program_items (area, title, description, status, public_note, sort_order, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($programItems as $item) {
        $stmt->execute([...$item, $now]);
    }
}
