# Scrivimi - Maria Laura Orrù

Un sito web semplice e curato per la comunicazione diretta con i cittadini.

## Caratteristiche

- **Homepage single-page** con form per scrivere messaggi
- **Q&A pubbliche** - domande e risposte pubblicate con consenso
- **Programma 2021** - checklist trasparente delle promesse elettorali
- **Proposte cittadine** - co-programmazione partecipativa
- **Dashboard admin** completa per gestire messaggi, risposte e programma

## Requisiti

- PHP 8.0 o superiore
- SQLite3 (incluso di default in PHP)
- Composer

## Installazione

### 1. Clona o scarica il progetto

```bash
cd /path/to/your/webserver
git clone <repository-url> mlo
cd mlo
```

### 2. Installa le dipendenze

```bash
composer install
```

### 3. Configura le impostazioni

Modifica il file `includes/config.php`:

```php
// Cambia la chiave segreta per l'accesso admin (stringa lunga e casuale)
define('ADMIN_SECRET_KEY', 'la_tua_chiave_segreta_molto_lunga');

// Cambia la password admin (genera un nuovo hash)
define('ADMIN_PASS_HASH', password_hash('la_tua_password', PASSWORD_DEFAULT));
```

### 4. Configura SMTP per l'invio email

Nel file `includes/config.php`, configura i parametri SMTP:

```php
define('SMTP_HOST', 'smtp.tuoprovider.com');
define('SMTP_USER', 'email@tuodominio.it');
define('SMTP_PASS', 'password_smtp');
define('SMTP_PORT', 587);
define('SMTP_SECURE', 'tls'); // oppure 'ssl' per porta 465
```

### 5. Avvia il server di sviluppo

```bash
php -S localhost:8000
```

Apri il browser su `http://localhost:8000`

## Accesso Admin

L'area admin è accessibile solo tramite un link segreto:

```
http://localhost:8000/admin/login.php?k=ADMIN_SECRET_KEY
```

Credenziali di default:
- **Username:** `admin`
- **Password:** `admin123`

**IMPORTANTE:** Cambia immediatamente queste credenziali in produzione!

### Generare un nuovo hash password

```bash
php -r "echo password_hash('nuova_password', PASSWORD_DEFAULT);"
```

## Struttura del progetto

```
/mlo
├── admin/                  # Area amministrazione
│   ├── dashboard.php       # Dashboard principale
│   ├── login.php           # Login admin (accesso nascosto)
│   ├── logout.php          # Logout
│   ├── message.php         # Visualizza singolo messaggio
│   ├── reply.php           # Rispondi a un messaggio
│   ├── program.php         # Gestione programma 2021
│   └── proposals.php       # Gestione proposte cittadini
├── assets/
│   └── style.css           # Stili personalizzati
├── data/
│   ├── .htaccess           # Protezione directory
│   └── site.sqlite         # Database SQLite (auto-generato)
├── includes/
│   ├── config.php          # Configurazione
│   ├── csrf.php            # Protezione CSRF
│   ├── db.php              # Connessione database
│   ├── footer.php          # Footer HTML
│   ├── functions.php       # Funzioni utilità
│   ├── header.php          # Header HTML
│   ├── init_db.php         # Inizializzazione DB
│   └── mailer.php          # Invio email
├── composer.json           # Dipendenze Composer
├── domande.php             # Pagina Q&A pubbliche
├── index.php               # Homepage
├── privacy.php             # Privacy policy
├── submit_message.php      # Handler form messaggi
└── submit_proposal.php     # Handler form proposte
```

## Funzionalità Admin

### Gestione Messaggi
- Visualizza messaggi ricevuti (nuovi, letti, risposti)
- Scrivi e modifica risposte
- Invia risposte via email
- Pubblica Q&A sul sito

### Gestione Programma 2021
- Aggiungi/modifica/elimina voci del programma
- Imposta stato: FATTO / IN PARTE / NON FATTO
- Aggiungi note pubbliche esplicative
- Organizza per area tematica

### Gestione Proposte
- Visualizza proposte cittadine
- Segna come lette
- Archivia proposte

## Sicurezza

Il progetto implementa:

- **Prepared statements** per tutte le query SQL
- **htmlspecialchars** su tutti gli output
- **CSRF token** su tutte le form admin
- **Session regeneration** al login
- **Honeypot** anti-spam
- **Rate limiting** per IP
- **Protezione directory /data** via .htaccess
- **Admin nascosto** - risponde 404 senza chiave corretta

## Deploy in Produzione

1. Imposta `display_errors = 0` in php.ini
2. Usa HTTPS
3. Cambia tutte le credenziali di default
4. Configura un server SMTP reale
5. Verifica che la directory `/data` non sia accessibile via web
6. Imposta permessi corretti sui file (644) e directory (755)

## Database

Il database SQLite viene creato automaticamente al primo accesso in `data/site.sqlite`.

Tabelle:
- `messages` - Messaggi ricevuti
- `replies` - Risposte ai messaggi
- `program_items` - Voci del programma 2021
- `proposals` - Proposte cittadine

## Licenza

Progetto sviluppato per Maria Laura Orrù.
