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

---

## Fase 3

### 🔹 Autenticazione base e pagine riservate

* Implementati login, registrazione, dashboard e pagina admin con controllo sessione
* Aggiunta validazione client minima in `js/validation.js`
* Collegati header pubblici e privati con stato utente coerente

**Esito:**
→ Base autenticazione pronta per distinguere ospiti, contributor e admin

---

## Fase 4

### 🔹 Viewer rifinito

* Rimossa la data di aggiunta del film dalla testata del viewer
* Semplificata la riga metadati del frame:

  * timestamp a sinistra
  * pillola autore a destra

* Rimossa la dicitura "Ordine per timestamp" dalla timeline

**Esito:**
→ Viewer piu pulito e focalizzato sui dati utili del frame

---

### 🔹 Voti separati sui tag

* Aggiornato `TAG_VOTI` da modello con `valore` unico a modello esplicito con:

  * `upvote`
  * `downvote`

* Aggiornato `setup_database.sql`
* Migrato anche il database locale attivo
* Aggiornato `php/api_get_tags.php` per restituire i due contatori separati
* Aggiornata la sidebar del viewer con:

  * triangolino verde verso l'alto
  * triangolino rosso verso il basso
  * contatore visibile per ciascun voto

**Esito:**
→ Sistema voti piu chiaro e coerente tra database, API e interfaccia

---

### 🔹 Sidebar viewer riorganizzata

* Rimosso il titolo fisso "Hardware"
* Spostato il nome del prop in alto a sinistra nella sidebar
* Mantenuti sotto il titolo autore e data del tag
* Fissati i due badge voto impilati in alto a destra

**Esito:**
→ Sidebar piu ordinata e piu vicina al linguaggio del progetto

---

### 🔹 Dashboard contributor reale

* Trasformata `dashboard.php` in area contributor con inserimento:

  * film
  * frame

* Implementati controlli server-side:

  * sessione valida
  * film duplicati
  * film esistente per i frame
  * timestamp valido
  * upload immagini con controlli su tipo e dimensione

* Aggiunti form piu puliti e spazio dedicato a feedback e statistiche
* Aggiunti pulsanti tondi per scegliere il contenuto da inserire
* Resa visibile una sola scheda di inserimento per volta

**Esito:**
→ Dashboard ora utile davvero per il flusso contributor, ma ancora semplice e spiegabile

---

### 🔹 Rimozione inserimento prop dalla dashboard

* Rimossa dalla dashboard tutta la parte di inserimento prop/hardware
* Rimossi:

  * bottone dedicato
  * form dedicato
  * validazione JS associata
  * riferimenti documentali non piu coerenti
  * cartella `assets/hardware`

* Decisione progettuale:
  i prop verranno aggiunti piu avanti tramite editor sul frame, non con un form separato in dashboard

**Esito:**
→ Dashboard riallineata al flusso reale del progetto

---

### 🔹 Fix upload immagini dashboard

* Individuato problema di permessi sulle cartelle upload usate da Apache/XAMPP
* Verificato che Apache gira come gruppo `daemon`
* Sistemati i permessi sulle cartelle:

  * `assets/covers`
  * `assets/frames`

**Esito:**
→ Upload coerente con l'ambiente XAMPP locale

* Creati `login.php`, `register.php` e `logout.php`
* Implementato `php/auth.php` come endpoint unico JSON per:

  * registrazione
  * login
  * logout
* Aggiunta validazione client-side in `js/validation.js`
* Aggiunti controlli di sessione in `dashboard.php` e `admin.php`
* Gestita distinzione ruolo `admin` / `contributor`

**Esito:**
→ Flusso minimo di autenticazione funzionante con sessioni PHP e accesso protetto alle aree riservate

---

### 🔹 Header dinamico con stato utente

* Aggiornati `index.php` e `frame_viewer.php`
* Se l'utente non e autenticato compare un menu rapido con link a login e registrazione
* Se l'utente e autenticato compaiono:

  * username
  * link dashboard
  * link admin per i soli amministratori
  * logout

**Esito:**
→ Navigazione pubblica e privata integrate senza duplicare logica lato client

---

### 🔹 Rifinitura UI form di accesso

* Rifatta la sezione auth in `css/layout.css` e `css/style.css`
* Aggiunte card dedicate per login e registrazione
* Stabilizzato il layout dei campi con messaggi errore riservati nel flusso
* Corretto il disallineamento del pulsante nella scheda login
* Allineati tema, header e spaziature delle nuove pagine auth

**Esito:**
→ Login e registrazione coerenti, responsive e piu stabili durante la validazione

---

### 🔹 Coerenza tema e componenti

* Aggiornati `js/theme-init.js` e `js/theme-toggle.js`
* Estesi `css/components.css` e `css/style.css` per rendere coerenti:

  * viewer
  * card
  * pulsanti
  * pannelli auth
* Mantenuto cache busting con `filemtime()` nelle nuove pagine

**Esito:**
→ Interfaccia piu uniforme tra homepage, viewer e aree di autenticazione

---

### 🔹 Tracciamento autori e voti nei contenuti

* Aggiornato `setup_database.sql`
* Aggiunti:

  * `id_utente_creatore` e `creato_il` su `FILM`
  * `id_utente_creatore` e `creato_il` su `FRAME`
  * `id_utente_creatore` e `creato_il` su `HARDWARE`
  * tabella `TAG_VOTI`

* Reimportato il database locale con il nuovo schema
* Aggiornato il seed con autori e voti di esempio

**Esito:**
→ Base dati pronta per dashboard contributor, punteggi e moderazione admin

---

### 🔹 Crediti contenutistici nel catalogo e nel viewer

* Aggiornato `index.php`
* Ogni card film ora mostra:

  * autore del film
  * data di inserimento

* Aggiornato `frame_viewer.php`
* Il viewer ora mostra:

  * autore e data del film nella testata
  * descrizione frame in alto nella scheda
  * timestamp in basso a sinistra
  * autore del frame in basso a destra
  * autore del frame anche nella timeline

**Esito:**
→ I contenuti risultano piu leggibili e meglio attribuiti ai contributor

---

### 🔹 Sidebar hardware piu coerente

* Aggiornato `php/api_get_tags.php`
* L'endpoint ora restituisce anche:

  * autore del tag
  * data di inserimento
  * punteggio netto

* Aggiornato `js/viewer.js`
* Al click su un tag la sidebar mostra:

  * nome hardware
  * punteggio accanto al nome
  * autore del tag in alto a destra
  * data sotto l'autore
  * dettagli hardware in pannelli coerenti

* Rifiniti `css/components.css` e `css/layout.css`

**Esito:**
→ Sidebar e viewer piu ordinati, coerenti e vicini al tono finale del progetto

---

### 🔹 Allineamento documentazione tecnica

* Aggiornati i file in `docs/descrizioni` collegati a:

  * `index.php`
  * `frame_viewer.php`
  * `php/api_get_tags.php`
  * `js/viewer.js`
  * `css/layout.css`
  * `css/components.css`

* Allineati anche i documenti generali su:

  * struttura database
  * contratti API
  * roadmap personale

**Esito:**
→ Documentazione coerente con lo stato reale del progetto e pronta per proseguire con la dashboard
