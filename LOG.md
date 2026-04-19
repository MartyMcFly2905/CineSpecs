# LOG – CineSpecs

## Fase 1 

### 🔹 Struttura progetto

* Creata struttura iniziale:

  * root con file principali
  * cartelle separate (`php/`, `css/`, `js/`, `assets/`, `docs/`)
* Aggiunti file di configurazione e documentazione base

---

### 🔹 Database

* Progettato schema relazionale:

  * `UTENTI`
  * `FILM`
  * `HARDWARE`
  * `FRAME`
  * `TAGS`
* Implementati vincoli:

  * foreign key
  * `ON DELETE CASCADE`
  * `UNIQUE (id_frame, id_hardware)`
  * `CHECK` coordinate (0–100)
* Inseriti dati di test (seed)

**Decisioni chiave:**

* coordinate salvate in percentuale (`DECIMAL`)
* uso di tabella `TAGS` per relazione frame–hardware
* schema semplice e normalizzato (3NF)

---

### 🔹 Connessione database (PDO)

* Implementata connessione in `php/config.php`
* Configurazione:

  * `utf8mb4`
  * `ERRMODE_EXCEPTION`
  * `FETCH_ASSOC`
  * prepared statements reali
* Gestione errori:

  * log su `error_log`
  * messaggio generico all’utente

---

### 🔹 Test compatibilità (XAMPP)

Test eseguiti su XAMPP:

* ✔ creazione database e tabelle
* ✔ import dati iniziali
* ✔ foreign key (verifica fallimento controllato)
* ✔ vincolo UNIQUE su `TAGS`
* ✔ vincoli coordinate (`CHECK`)
* ✔ cascade delete
* ✔ connessione PDO funzionante
* ✔ query PHP di test funzionanti
* ✔ gestione errore DB corretta

**Esito:**  
→ Database e connessione **verificati e compatibili con l’ambiente del corso**

### 🔹 Homepage base (index.php + UI)

* Implementata homepage dinamica con PHP + PDO
* Recupero dati da tabella `FILM`
* Query dinamica con:

  * filtro per titolo (`LIKE`)
  * filtro per anno (`anno_uscita`)
* Uso di prepared statements (PDO)
* Output sanitizzato con `htmlspecialchars` (protezione XSS)

**UI e struttura:**

* Layout responsive con CSS separato (`style.css`, `layout.css`)
* Griglia film con card
* Form di ricerca semantico e accessibile
* Logo cliccabile (ritorno homepage)

**JavaScript:**

* Implementato toggle tema (`theme-toggle.js`)
* Gestione stato tramite `data-theme`
* Persistenza preferenza con `localStorage`
* Aggiornamento dinamico logo e UI

**Funzionalità:**

* ricerca per titolo
* filtro per anno
* lista film
* gestione stato vuoto
* gestione errore DB
* cambio tema (chiaro/scuro)

**Esito:**
→ Homepage funzionante, stilizzata e interattiva, integrata con database e pronta per estensioni future

---

### 🔹 Frame viewer minimo

* Implementata prima versione di `frame_viewer.php`
* Lettura parametro GET `film` con validazione intera
* Recupero film e frame tramite PDO e prepared statements
* Mostrato come principale il primo frame disponibile
* Gestiti casi:

  * parametro film non valido
  * film non trovato
  * film senza frame

**Struttura UI preparata:**

* area immagine principale
* contenitore relativo per futuri Pulse-Tag
* sidebar hardware placeholder
* timeline frame sotto l'immagine

**Database e dati test:**

* Aggiunto `Ritorno al futuro` come film senza frame
* Aggiornato `setup_database.sql`
* Inserito il film anche nel database locale attivo per test manuale

**Tema e UI:**

* Aggiunto `js/theme-init.js` per mantenere il tema scelto tra homepage e viewer
* Resa coerente la larghezza globale di homepage e viewer
* Ridotti container, card, search panel e viewer per una UI meno ingombrante
* Aggiunto cache busting anche a `layout.css` in homepage

**Esito:**
→ Viewer minimo funzionante, homepage e viewer coerenti, film senza frame disponibile per test

---

## Fase 2

### 🔹 API tag del viewer

* Implementato `php/api_get_tags.php`
* L'endpoint:

  * accetta solo richieste `GET`
  * valida `id_frame` lato server
  * usa PDO e prepared statements
  * restituisce JSON pulito
  * recupera coordinate tag e dati hardware collegati
  * include anche descrizione e curiosita hardware

**Esito:**
→ Backend pronto per caricare i Pulse-Tag del frame viewer tramite JavaScript

---

### 🔹 Integrazione viewer e Pulse-Tag

* Aggiornato `frame_viewer.php` con:

  * `#frame-image`
  * `data-frame-id`
  * `#tag-layer`
  * `#hardware-sidebar`
  * `#sidebar-content`
  * `#frame-timestamp`
  * `#frame-description`
  * inclusione di `js/viewer.js` con cache busting

* Implementato `js/viewer.js`:

  * caricamento tag con Fetch API
  * creazione Pulse-Tag sopra il frame
  * posizionamento con coordinate percentuali
  * click sul tag con dettagli hardware in sidebar
  * gestione errori semplice

**Esito:**
→ Viewer interattivo funzionante, con tag cliccabili e sidebar dati hardware

---

### 🔹 Timeline interattiva

* Aggiunti attributi `data-*` alle miniature della timeline
* Implementato cambio frame senza ricaricare pagina
* Al click su una miniatura:

  * cambia immagine principale
  * aggiorna `data-frame-id`
  * aggiorna timestamp e descrizione
  * sposta lo stato `.timeline-item--active`
  * ricarica i tag del nuovo frame

**Esito:**
→ Timeline del viewer navigabile e collegata ai tag del frame corrente

---

### 🔹 Stile viewer e catalogo

* Completati gli stili in `css/components.css`
* Aggiunto `css/animations.css` per animare i Pulse-Tag
* Rifiniti:

  * layout viewer
  * sidebar hardware
  * timeline
  * stato attivo delle miniature
  * card del catalogo film

* Aggiunta variabile `--font-terminal` in `style.css`
* Allargato il container generale e bilanciate le dimensioni delle card film

**Esito:**
→ Interfaccia piu coerente, responsive e leggibile

---

### 🔹 Dati di test per Ritorno al futuro

* Aggiornato `setup_database.sql`
* Aggiunti:

  * 3 frame per `Ritorno al futuro`
  * 3 hardware collegati
  * 3 tag con coordinate gia compilate

**Esito:**
→ Dataset pronto per testare timeline, cambio frame e ricaricamento tag

---

### 🔹 Rimozione icone hardware dedicate

* Rimossi riferimenti alle icone hardware dal seed SQL
* Eliminati file icona hardware non piu usati
* Mantenuti solo i loghi applicativi in `assets/icons`
* Lasciato il campo `HARDWARE.immagine_path` nello schema per eventuale futura compatibilita

**Esito:**
→ Sidebar e viewer usano solo dettagli testuali hardware, senza dipendere da immagini dedicate

