# LOG – CineSpecs

## Fase 1 

### 🔹 Struttura progetto

* Creata struttura iniziale:

  * root con file principali
  * cartelle separate (`php/`, `css/`, `js/`, `assets/`, `docs/`)
* Aggiunti file di configurazione e documentazione base
* Definite convenzioni di sviluppo (`AGENTS.md`)

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

