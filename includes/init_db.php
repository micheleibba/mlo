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
            phone TEXT,
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

    // Insert REAL program items from MLO PROGRAMMA
    $now = date('c');
    $programItems = [
        // Partecipazione dei cittadini, trasparenza, innovazione e digitale
        ['Partecipazione e Digitale', 'Rendere l\'amministrazione più trasparente e accessibile ai cittadini', null, 'non_fatto', null, 1],
        ['Partecipazione e Digitale', 'Aumentare l\'efficienza della macchina comunale', 'Colmando carenze di personale e digitalizzando le pratiche', 'non_fatto', null, 2],
        ['Partecipazione e Digitale', 'Attivare un bilancio partecipativo', 'Ascolto e scelta diretta delle politiche da parte dei cittadini', 'non_fatto', null, 3],
        ['Partecipazione e Digitale', 'Wi-Fi gratuito nei luoghi di maggiore aggregazione', null, 'non_fatto', null, 4],
        ['Partecipazione e Digitale', 'Politiche di supporto all\'informazione e comunicazione digitale', null, 'non_fatto', null, 5],
        ['Partecipazione e Digitale', 'App gratuita per segnalazioni in tempo reale', null, 'non_fatto', null, 6],
        ['Partecipazione e Digitale', 'Spazio pubblico attrezzato per servizi online con assistenza', null, 'non_fatto', null, 7],

        // Sicurezza, ambiente, CO₂ ed efficienza energetica
        ['Sicurezza e Ambiente', 'Riprendere dialogo per Caserma dei Carabinieri', null, 'non_fatto', null, 1],
        ['Sicurezza e Ambiente', 'Migliorare pattugliamenti tramite Caserma di riferimento', null, 'non_fatto', null, 2],
        ['Sicurezza e Ambiente', 'Installare dissuasori di velocità nei punti critici', null, 'non_fatto', null, 3],
        ['Sicurezza e Ambiente', 'Educazione alla sicurezza stradale nelle scuole', null, 'non_fatto', null, 4],
        ['Sicurezza e Ambiente', 'Attuare il Piano di Protezione Civile con formazione', null, 'non_fatto', null, 5],
        ['Sicurezza e Ambiente', 'Defibrillatori nelle piazze e scuole con corsi di formazione', null, 'non_fatto', null, 6],
        ['Sicurezza e Ambiente', 'Valorizzare la Laguna di Santa Gilla e progetto parco unico', null, 'non_fatto', null, 7],
        ['Sicurezza e Ambiente', 'Introdurre appalti verdi e ampliare eco-raccoglitori', null, 'non_fatto', null, 8],
        ['Sicurezza e Ambiente', 'Politiche plastic-free con punti acqua', null, 'non_fatto', null, 9],
        ['Sicurezza e Ambiente', 'Piano gestione beni storico-archeologici e ambientali', null, 'non_fatto', null, 10],
        ['Sicurezza e Ambiente', 'Incentivare pratiche sostenibili in area umida', null, 'non_fatto', null, 11],
        ['Sicurezza e Ambiente', 'Manutenzione spazi verdi con coinvolgimento cittadini', null, 'non_fatto', null, 12],
        ['Sicurezza e Ambiente', 'Indipendenza energetica con impianti solari e incentivi', null, 'non_fatto', null, 13],
        ['Sicurezza e Ambiente', 'Sistema di gestione ambientale con agevolazioni fiscali', null, 'non_fatto', null, 14],
        ['Sicurezza e Ambiente', 'Centro di Educazione Ambientale alla Sostenibilità (CEAS)', null, 'non_fatto', null, 15],
        ['Sicurezza e Ambiente', 'Analisi epidemiologica su aria e rumore con risultati pubblici', null, 'non_fatto', null, 16],
        ['Sicurezza e Ambiente', 'Incentivi per rimozione eternit', null, 'non_fatto', null, 17],
        ['Sicurezza e Ambiente', 'Piano contrasto randagismo e cura colonie feline', null, 'non_fatto', null, 18],
        ['Sicurezza e Ambiente', 'Piano comunale riduzione rifiuti', null, 'non_fatto', null, 19],
        ['Sicurezza e Ambiente', 'Potenziare raccolta differenziata con compostaggio domestico', null, 'non_fatto', null, 20],
        ['Sicurezza e Ambiente', 'Promuovere vuoto a rendere e sistemi ricarica alla spina', null, 'non_fatto', null, 21],
        ['Sicurezza e Ambiente', 'Incentivi a start-up green', null, 'non_fatto', null, 22],
        ['Sicurezza e Ambiente', 'Combattere abbandono rifiuti e smaltimento illegale', null, 'non_fatto', null, 23],

        // Creazione e ripristino di spazi pubblici
        ['Spazi Pubblici', 'Nuova biblioteca con aree studio, multimediali e archivio', null, 'non_fatto', null, 1],
        ['Spazi Pubblici', 'Valorizzare ex Provveditorato e area ex Protezione Civile', 'Mercato pescato, struttura ricettiva, spazio convegnistico', 'non_fatto', null, 2],

        // Urbanistica, viabilità, verde e patrimonio
        ['Urbanistica e Viabilità', 'Promuovere modifiche urbanistiche con premialità per riqualificazione', null, 'non_fatto', null, 1],
        ['Urbanistica e Viabilità', 'Riqualificazione verde, arredo urbano e manutenzione stradale', null, 'non_fatto', null, 2],
        ['Urbanistica e Viabilità', 'Alloggi edilizia popolare e zona AREA per edilizia economica', null, 'non_fatto', null, 3],
        ['Urbanistica e Viabilità', 'Due polmoni verdi attrezzati per famiglie, giovani e anziani', null, 'non_fatto', null, 4],
        ['Urbanistica e Viabilità', 'Aree attrezzate per cani', null, 'non_fatto', null, 5],
        ['Urbanistica e Viabilità', 'Completare vie di collegamento strategiche', 'Rio Sestu, Tanca e Linarbus', 'non_fatto', null, 6],
        ['Urbanistica e Viabilità', 'Valorizzazione Parco archeologico Tanca e Linarbus', null, 'non_fatto', null, 7],
        ['Urbanistica e Viabilità', 'Sicurezza e fruibilità Chiesa di Santa Caterina', null, 'non_fatto', null, 8],
        ['Urbanistica e Viabilità', 'Definire criteri assegnazione lotti giovani coppie', null, 'non_fatto', null, 9],

        // Ruolo nella Città Metropolitana
        ['Città Metropolitana', 'Collegare industriale, laguna e centro cittadino', null, 'non_fatto', null, 1],
        ['Città Metropolitana', 'Progettare camminamenti ciclopedonali con comuni limitrofi', null, 'non_fatto', null, 2],
        ['Città Metropolitana', 'Promuovere tavoli su aeroporto e sviluppo urbano', null, 'non_fatto', null, 3],
        ['Città Metropolitana', 'Sviluppo green economy', null, 'non_fatto', null, 4],

        // Mobilità lenta e sostenibile
        ['Mobilità Sostenibile', 'Manutenzione marciapiedi e percorsi pedonali', null, 'non_fatto', null, 1],
        ['Mobilità Sostenibile', 'Incrementare piste ciclabili e collegamenti regionali', null, 'non_fatto', null, 2],
        ['Mobilità Sostenibile', 'Riaprire dialogo per accessi SS 130', null, 'non_fatto', null, 3],
        ['Mobilità Sostenibile', 'Promuovere nuovi collegamenti trasporto pubblico con CTM', null, 'non_fatto', null, 4],

        // Sostegno a economia, commercio e produzioni locali
        ['Economia Locale', 'Consolidare sportello Fare Impresa', null, 'non_fatto', null, 1],
        ['Economia Locale', 'Inserimenti lavorativi e borse di studio con aziende', null, 'non_fatto', null, 2],
        ['Economia Locale', 'Relazioni istituzionali per formazione professionale mirata', null, 'non_fatto', null, 3],
        ['Economia Locale', 'Sito delle associazioni e punti vetrina promozione territoriale', null, 'non_fatto', null, 4],
        ['Economia Locale', 'Incentivi imprese legate a pesca e turismo locale', null, 'non_fatto', null, 5],
        ['Economia Locale', 'Ufficio progettazione europea per fondi UE e marketing territoriale', null, 'non_fatto', null, 6],

        // Politiche sociali, inclusione, giovani, donne e anziani
        ['Politiche Sociali', 'Rafforzare rete solidarietà con cittadini e associazioni', null, 'non_fatto', null, 1],
        ['Politiche Sociali', 'Collaborare con Parrocchia, Oratorio e Caritas', null, 'non_fatto', null, 2],
        ['Politiche Sociali', 'Supportare mensa del povero e servizi sociali', null, 'non_fatto', null, 3],
        ['Politiche Sociali', 'Riqualificare case popolari e liberarne vendita agli assegnatari', null, 'non_fatto', null, 4],
        ['Politiche Sociali', 'Temi di pari opportunità (gender e discriminazioni)', null, 'non_fatto', null, 5],
        ['Politiche Sociali', 'Sostegno famiglie (tempi vita-lavoro)', null, 'non_fatto', null, 6],
        ['Politiche Sociali', 'Contrastare povertà nuove e vecchie post-Covid', null, 'non_fatto', null, 7],
        ['Politiche Sociali', 'Supporto inserimento lavorativo giovani e donne', null, 'non_fatto', null, 8],
        ['Politiche Sociali', 'Ticket e voucher sociali', null, 'non_fatto', null, 9],
        ['Politiche Sociali', 'Baratto amministrativo (prestazioni sociali vs tasse)', null, 'non_fatto', null, 10],
        ['Politiche Sociali', 'Vaccinazioni gratuite animali domestici per fasce ISEE', null, 'non_fatto', null, 11],
        ['Politiche Sociali', 'Installazione defibrillatori capillare e corsi utilizzo', null, 'non_fatto', null, 12],
        ['Politiche Sociali', 'Valorizzazione patrimonio culturale e memoria collettiva dagli anziani', null, 'non_fatto', null, 13],
        ['Politiche Sociali', 'Incentivare esperienze intergenerazionali', null, 'non_fatto', null, 14],
        ['Politiche Sociali', 'Riaprire informa-giovani con operatori', null, 'non_fatto', null, 15],
        ['Politiche Sociali', 'Creare forum dei giovani', null, 'non_fatto', null, 16],
        ['Politiche Sociali', 'Riportare educativa di strada attiva', null, 'non_fatto', null, 17],
        ['Politiche Sociali', 'Ripristinare centro aggregazione giovani', null, 'non_fatto', null, 18],
        ['Politiche Sociali', 'Aule studio universitarie con associazioni', null, 'non_fatto', null, 19],
        ['Politiche Sociali', 'Promuovere Servizio Civile Universale', null, 'non_fatto', null, 20],

        // Cultura e istruzione
        ['Cultura e Istruzione', 'Sostenere Istituto Comprensivo, Agrario e scuole paritarie', null, 'non_fatto', null, 1],
        ['Cultura e Istruzione', 'Tutoraggio per studenti con difficoltà', null, 'non_fatto', null, 2],
        ['Cultura e Istruzione', 'Promuovere Scuole Aperte con collaborazione associativa', null, 'non_fatto', null, 3],
        ['Cultura e Istruzione', 'Ampliare spazi culturali e biblioteca con nuovi libri e attrezzature', null, 'non_fatto', null, 4],
        ['Cultura e Istruzione', 'Potenziare scuolabus per uscite formative', null, 'non_fatto', null, 5],
        ['Cultura e Istruzione', 'Orientamento universitario e borse di studio', null, 'non_fatto', null, 6],
        ['Cultura e Istruzione', 'Banca dati online di curricula', null, 'non_fatto', null, 7],
        ['Cultura e Istruzione', 'Cofinanziamenti per strumenti didattici e libri usati', null, 'non_fatto', null, 8],
    ];

    $stmt = $pdo->prepare("
        INSERT INTO program_items (area, title, description, status, public_note, sort_order, updated_at)
        VALUES (?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($programItems as $item) {
        $stmt->execute([...$item, $now]);
    }
}
