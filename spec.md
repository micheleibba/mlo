# SPEC — Sito ultra semplice + Q&A + Programma 2021 (checklist) + Co-programma + Dashboard Admin
**Stack:** PHP 8.x + SQLite + Bootstrap 5 + PHPMailer (Composer)  
**Obiettivo:** sito 1-pagina “minimal ma curato” con focus su: *scrivimi e ti rispondo davvero*.

---

## 1) RISULTATO ATTeso (UX)
Homepage a scroll (single-page) con 4 sezioni in quest’ordine:

1) **SCRIVI E RICEVI RISPOSTA** (hero + form) — sopra la piega, protagonista
2) **ULTIME DOMANDE & RISPOSTE PUBBLICATE** — 3 esempi + “Mostra tutto”
3) **PROGRAMMA 2021: COSA PROMESSO / COSA FATTO / COSA NO** — checklist consultabile + note “perché”
4) **SCRIVIAMO INSIEME IL PROSSIMO PROGRAMMA** — form guidato di proposte (non sportello reclami)

Footer: Privacy + contatti essenziali.

---

## 2) SCELTE TECNICHE (vincoli)
- No framework PHP.
- **SQLite** come DB (file `data/site.sqlite`) → zero setup, perfetto per gestire molte Q&A e checklist.
- Grafica: **Bootstrap 5 CDN** + `assets/style.css` custom minimale.
- Email risposte: **PHPMailer** con SMTP (Composer).
- Admin: login “nascosto” + credenziali hardcoded in `config.php` (senza MySQL).

---

## 3) FILE TREE
/project-root
  /assets
    style.css
    logo.svg (opzionale)
  /data
    site.sqlite (auto-creato)
    .htaccess (nega accesso web a /data)
  /includes
    config.php
    db.php
    init_db.php
    functions.php
    csrf.php
    header.php
    footer.php
    mailer.php
  /admin
    login.php
    dashboard.php
    message.php
    reply.php
    program.php
    proposals.php
    logout.php
  index.php
  submit_message.php
  domande.php
  submit_proposal.php
  privacy.php
  composer.json
  vendor/ (composer)
  README.md

---

## 4) PALETTE & STILE (curato ma semplice)
- Background: bianco caldo / grigio chiarissimo
- Primario: verde/teal elegante (istituzionale)
- Accento: sabbia/avorio
- Card: radius 16px, shadow soft
- Bottoni: primary evidente, hover pulito
- Mobile-first (spazi generosi, font leggibile)

---

## 5) HOMEPAGE — index.php (single page)
### Sezione A — HERO + FORM (above the fold)
Testi:
- Titolo: **"Scrivimi."**
- Sottotitolo: "Raccontami un problema, una critica o un’idea per Elmas. Ti risponderò io."
- Microcopy: "Non sempre potrò dirti “sì”. Ma ti dirò sempre la verità."

Form (POST -> `submit_message.php`):
- name (opzionale)
- email (obbligatorio)
- topic (select): Ordinario / Progetti / Idee / Altro
- message (obbligatorio)
- privacy checkbox (obbligatorio)
- honeypot hidden `website` (deve rimanere vuoto)
CTA: **Invia**

Dopo invio: redirect a `index.php?sent=1#form` con alert “Grazie”.

---

### Sezione B — ULTIME 3 Q&A PUBBLICATE
- Recupera da DB le ultime 3 `replies` con `published=1`
- Mostra in 3 card:
  - Domanda (estratto)
  - Risposta (estratto)
  - Data risposta
- Bottone: **"Mostra tutte"** -> `domande.php`

---

### Sezione C — PROGRAMMA 2021 (checklist trasparente)
Obiettivo: far vedere subito che c’è metodo e verità, non propaganda.

UI ultra semplice:
- Titolo: **"Programma 2021: cosa è stato fatto"**
- Sottotitolo breve: "Qui trovi le promesse del 2021 e lo stato aggiornato, con una nota quando qualcosa non è stato possibile o ha richiesto tempi più lunghi."
- Mostrare le voci in accordion per aree (es: Partecipazione, Ambiente, Sicurezza, Urbanistica, Giovani, Cultura, Economia...)
- Ogni riga/voce ha:
  - titolo azione (testo breve)
  - stato (badge): FATTO / IN PARTE / NON FATTO
  - nota breve (opzionale): “perché / cosa manca / tempi / vincoli”
  - (opzionale) link “approfondisci” che apre modal o espande testo

In home mostrare:
- le prime 8–12 voci (o 2 aree) e un bottone:
  **"Vedi tutto il programma 2021"** -> anchor in basso o pagina dedicata `#programma` (restando in single page va bene anche mostrare tutto con “Mostra tutto”).

NOTA: Il programma 2021 deve essere gestibile dall’admin (CRUD) per aggiornare stati e note.

---

### Sezione D — SCRIVIAMO INSIEME IL PROGRAMMA (partecipazione vera)
Titolo: **"Scriviamo insieme il prossimo programma"**
Testo breve:
- “Non prometto di fare tutto. Prometto di decidere meglio, insieme.”
- “Raccontami il problema e la tua proposta concreta.”

Form guidato (POST -> `submit_proposal.php`):
- name (opzionale)
- email (obbligatorio, per eventuale follow-up)
- area (select): Manutenzione / Verde / Mobilità / Sicurezza / Cultura / Scuola / Sociale / Progetti / Altro
- problema (textarea breve)
- proposta (textarea)
- privacy checkbox
- honeypot
CTA: **Invia proposta**

Dopo invio: redirect con messaggio “Grazie”.

IMPORTANTE:
- Questa sezione NON promette interventi immediati. È co-programmazione.

---

Footer:
- Link Privacy
- Piccolo testo copyright

---

## 6) PAGINE PUBBLICHE EXTRA
### domande.php
- Lista paginata (10/pagina) di Q&A pubblicate
- Search `q` su domanda/risposta
- Permalink opzionale via `slug`

### privacy.php
- policy dati, uso email per risposta, conservazione.

---

## 7) ADMIN — Accesso e sicurezza
### Admin “solo se hai il link”
- `admin/login.php?k=ADMIN_SECRET_KEY`
- Se chiave errata: **rispondere con 404** (non redirect)
- Credenziali admin hardcoded in `includes/config.php`:
  - ADMIN_USER
  - ADMIN_PASS_HASH (password_hash)

Session security:
- `session_regenerate_id(true)` al login
- CSRF token su tutte le azioni POST in admin

---

## 8) DASHBOARD ADMIN — Funzionalità
### admin/dashboard.php
- KPI:
  - Messaggi nuovi
  - Messaggi letti
  - Risposti
  - Pubblicati
  - Proposte nuove
- Tab messaggi / tab proposte / tab programma 2021

### admin/message.php?id=…
- Visualizza domanda ricevuta
- Stato: new -> seen al primo accesso
- Link a reply.php
- Se risposta esiste: mostra risposta + toggle publish

### admin/reply.php?id=…
- Scrivi/modifica risposta
- Checkbox:
  - invia email ora (default ON)
  - pubblica sul sito (default OFF)
- Salvataggio:
  - upsert in tabella `replies`
  - set message.status = replied
  - se invia email: PHPMailer SMTP + salva email_sent_at
  - se publish: replies.published=1 e genera slug se mancante

### admin/program.php (GESTIONE PROGRAMMA 2021)
CRUD completo per voci programma:
- lista per area
- aggiungi / modifica / elimina
Campi:
- area
- titolo breve
- descrizione (opzionale)
- stato: fatto/in_parte/non_fatto
- nota_pubblica (opzionale)
- ordine (integer) per ordering

### admin/proposals.php
- Lista proposte cittadine (status new/seen/archived)
- Vedere dettagli
- Opzione “Segna come letta”
- Opzione “Archivia”
- (Opzionale) toggle “pubblica proposta” — NON necessario subito, ma predisporre campo.

---

## 9) DATABASE SQLite — Schema

`messages`
- id INTEGER PK
- name TEXT NULL
- email TEXT NOT NULL
- topic TEXT NOT NULL
- message TEXT NOT NULL
- status TEXT NOT NULL DEFAULT 'new'  -- new|seen|replied|archived
- created_at TEXT NOT NULL (ISO8601)
- ip_hash TEXT NULL
- user_agent TEXT NULL

`replies`
- id INTEGER PK
- message_id INTEGER NOT NULL UNIQUE
- reply_text TEXT NOT NULL
- published INTEGER NOT NULL DEFAULT 0
- public_slug TEXT UNIQUE
- replied_at TEXT NOT NULL
- email_sent_at TEXT NULL
- FOREIGN KEY(message_id) REFERENCES messages(id)

`program_items`
- id INTEGER PK
- area TEXT NOT NULL
- title TEXT NOT NULL
- description TEXT NULL
- status TEXT NOT NULL DEFAULT 'non_fatto' -- fatto|in_parte|non_fatto
- public_note TEXT NULL
- sort_order INTEGER NOT NULL DEFAULT 0
- updated_at TEXT NOT NULL

`proposals`
- id INTEGER PK
- name TEXT NULL
- email TEXT NOT NULL
- area TEXT NOT NULL
- problem TEXT NOT NULL
- proposal TEXT NOT NULL
- status TEXT NOT NULL DEFAULT 'new' -- new|seen|archived
- created_at TEXT NOT NULL
- ip_hash TEXT NULL
- user_agent TEXT NULL

Indici:
- messages(created_at), messages(status)
- replies(published, replied_at)
- program_items(area, sort_order)
- proposals(created_at), proposals(status)

Init DB:
- se `data/site.sqlite` non esiste -> crea tabelle + inserisce alcuni program_items placeholder.

---

## 10) ANTI-SPAM / RATE LIMIT (senza captcha)
- honeypot `website`
- rate limit per ip_hash:
  - max 3 invii / 15 minuti per messages
  - max 3 invii / 15 minuti per proposals
- block se troppi link nel testo

---

## 11) EMAIL — PHPMailer via Composer
- `composer.json` includere `phpmailer/phpmailer`
- `includes/mailer.php` con funzione:
  `sendReplyEmail($toEmail, $toName, $questionExcerpt, $answerText)`
- SMTP config in `includes/config.php`:
  - SMTP_HOST, SMTP_USER, SMTP_PASS, SMTP_PORT, SMTP_SECURE
- Email HTML template:
  - saluto
  - estratto domanda in box
  - risposta
  - firma
  - nota privacy

---

## 12) CONFIG — includes/config.php
- SITE_NAME
- TIMEZONE 'Europe/Rome'
- ADMIN_SECRET_KEY (string lunga random)
- ADMIN_USER
- ADMIN_PASS_HASH (password_hash)
- MAIL_FROM
- MAIL_FROM_NAME
- SMTP_HOST/USER/PASS/PORT/SECURE

---

## 13) QUALITÀ & SICUREZZA
- `htmlspecialchars` su ogni output
- prepared statements per SQLite
- CSRF su admin
- 404 reale su admin/login con key errata
- Proteggere /data via .htaccess (deny all)
- Non esporre stack trace (error reporting off in produzione)

---

## 14) DELIVERABLE
Generare tutto il codice completo pronto all’avvio.
Includere README con:
- `composer install`
- `php -S localhost:8000`
- come settare SMTP
- come cambiare admin secret link e password

FINE SPEC
