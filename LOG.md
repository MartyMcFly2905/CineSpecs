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
