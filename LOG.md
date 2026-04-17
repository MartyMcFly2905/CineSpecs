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
