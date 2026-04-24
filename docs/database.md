# Database Notes

## Principi
- Schema in 3NF
- Foreign key con ON DELETE CASCADE dove sensato
- Coordinate sempre in percentuale
- Evitare duplicati di tag nello stesso frame
- Tracciare sempre autore e data di creazione dei contenuti contributor

## Controlli applicativi
- coord_x tra 0 e 100
- coord_y tra 0 e 100
- timestamp frame in formato coerente
- username univoco
- prop_fittizio impostato a 1 solo per props inventati, 0 per hardware reale


# CineSpecs – Database Documentation

## Overview

Il database `cinespecs` supporta una web app per catalogare hardware reale utilizzato nei film come oggetti di scena.

Le entità principali sono:

* UTENTI (utenti registrati)
* FILM (film catalogati)
* HARDWARE (dispositivi hardware)
* FRAME (fotogrammi dei film)
* TAGS (collegamento tra frame e hardware con coordinate)
* TAG_VOTI (voti up/down sui tag)

---

## Schema generale delle relazioni

* Un **FILM** ha molti **FRAME**
* Un **FRAME** può avere molti **TAGS**
* Un **UTENTE** può creare molti **FILM**, **FRAME**, **HARDWARE** e **TAGS**
* Un **TAG** collega:

  * un FRAME
  * un HARDWARE
  * un UTENTE (chi ha inserito il tag)
* Un **HARDWARE** può apparire in molti FRAME
* Un **TAG** può ricevere molti voti in **TAG_VOTI**
* Un **UTENTE** può votare molti tag, ma una sola volta per tag

---

## Tabella: UTENTI

Contiene gli utenti del sistema.

### Campi

* `id_utente` (PK) – identificatore univoco
* `username` – nome utente univoco
* `email` – email univoca
* `password_hash` – password hashata (password_hash)
* `ruolo` – 'contributor' o 'admin'
* `creato_il` – timestamp creazione account

### Vincoli

* UNIQUE su `username`
* UNIQUE su `email`

### Note

* Le password **non sono mai salvate in chiaro**
* Il ruolo determina accesso a funzionalità admin
* Per semplicità scolastica non è prevista una gestione avanzata di ban o soft delete utente

---

## Tabella: FILM

Contiene i film.

### Campi

* `id_film` (PK)
* `id_utente_creatore` (FK → UTENTI)
* `titolo`
* `anno_uscita`
* `regista`
* `sinossi` (opzionale)
* `copertina_path` (opzionale)
* `creato_il`

### Note

* `copertina_path` è un percorso relativo (es: assets/covers/alien.jpg)
* `id_utente_creatore` permette di attribuire il film a un contributor

---

## Tabella: HARDWARE

Contiene i dispositivi hardware reali o fittizi.

### Campi

* `id_hardware` (PK)
* `id_utente_creatore` (FK → UTENTI)
* `nome_modello`
* `produttore`
* `anno_rilascio` (opzionale)
* `descrizione` (opzionale)
* `curiosita` (opzionale)
* `prop_fittizio` (boolean)
* `immagine_path` (opzionale)
* `creato_il`

### Note

* `prop_fittizio = 0` → hardware reale
* `prop_fittizio = 1` → oggetto fittizio
* l'autore della scheda hardware è tracciato nel database

---

## Tabella: FRAME

Contiene i fotogrammi dei film.

### Campi

* `id_frame` (PK)
* `id_film` (FK → FILM)
* `id_utente_creatore` (FK → UTENTI)
* `immagine_path`
* `timestamp_frame` (TIME)
* `descrizione_scena` (opzionale)
* `creato_il`

### Vincoli

* FOREIGN KEY su `id_film`
* ON DELETE CASCADE
* FOREIGN KEY su `id_utente_creatore`

### Note

* Se un film viene eliminato, tutti i suoi frame vengono eliminati
* L'autore del frame viene conservato per dashboard e moderazione

---

## Tabella: TAGS

Tabella centrale del progetto.

Rappresenta un tag posizionato su un frame che identifica un hardware.

### Campi

* `id_tag` (PK)
* `id_frame` (FK → FRAME)
* `id_hardware` (FK → HARDWARE)
* `id_utente` (FK → UTENTI)
* `coord_x` (percentuale)
* `coord_y` (percentuale)
* `creato_il` (timestamp)

### Vincoli

* FOREIGN KEY su:

  * `id_frame`
  * `id_hardware`
  * `id_utente`

* ON DELETE CASCADE su tutte le relazioni

* CHECK:

  * `coord_x` tra 0 e 100
  * `coord_y` tra 0 e 100

* UNIQUE:

  * `(id_frame, id_hardware)`

### Note importanti

* Le coordinate sono salvate in **percentuale**, non in pixel
* Questo garantisce corretto posizionamento su immagini responsive
* Un hardware può comparire **una sola volta per frame**
* Il tag porta già con sé l'autore tramite `id_utente`

---

## Tabella: TAG_VOTI

Tabella prevista per supportare upvote e downvote sui tag.

### Campi

* `id_tag` (FK → TAGS)
* `id_utente` (FK → UTENTI)
* `upvote` (`1` se voto positivo, altrimenti `0`)
* `downvote` (`1` se voto negativo, altrimenti `0`)
* `creato_il` (timestamp)
* `aggiornato_il` (timestamp opzionale)

### Vincoli

* FOREIGN KEY su `id_tag`
* FOREIGN KEY su `id_utente`
* ON DELETE CASCADE
* CHECK che consente un solo tipo di voto per record
* UNIQUE `(id_tag, id_utente)`

### Note

* Ogni utente può avere un solo voto per tag
* I contatori del tag si ottengono con `SUM(upvote)` e `SUM(downvote)`
* Questa soluzione è sufficiente per viewer, dashboard e moderazione admin

---

## Coordinate dei TAG

Le coordinate sono calcolate lato client:

```js
const rect = image.getBoundingClientRect();
const x = ((event.clientX - rect.left) / rect.width) * 100;
const y = ((event.clientY - rect.top) / rect.height) * 100;
```

E salvate come:

* `coord_x`
* `coord_y`

---

## Regole applicative importanti

### Inserimento TAG

* Solo utenti autenticati
* coordinate valide (0–100)
* hardware esistente
* frame esistente

### Inserimento contenuti contributor

* Solo utenti autenticati
* contributor può inserire:

  * film
  * frame
  * hardware
  * tag sui frame

* eliminazione contenuti limitata all'autore, salvo admin
* film, frame e hardware salvano sempre `id_utente_creatore` preso dalla sessione

### Voti sui TAG

* Solo utenti autenticati
* un solo voto per utente per tag
* voto ammesso: upvote o downvote
* cambio voto consentito aggiornando il record esistente

### Moderazione admin

* admin può rimuovere tag errati o malevoli
* se necessario può rimuovere anche contenuti derivati o abusivi
* la moderazione si basa sul punteggio ma non dipende solo da esso
* la dashboard admin deve poter vedere autore e data di creazione di ogni contenuto

### Sicurezza

* Validazione lato server obbligatoria
* Prepared statements (PDO)
* Controllo sessione su API protette

### Eliminazione dati

* Eliminare un film elimina:

  * tutti i frame
  * tutti i tag associati

---

## Query tipiche

### Recuperare i tag di un frame

```sql
SELECT
    t.id_tag,
    t.coord_x,
    t.coord_y,
    COALESCE(SUM(tv.upvote), 0) AS upvotes,
    COALESCE(SUM(tv.downvote), 0) AS downvotes,
    u.username AS autore_tag,
    h.*
FROM TAGS t
JOIN HARDWARE h ON t.id_hardware = h.id_hardware
JOIN UTENTI u ON t.id_utente = u.id_utente
LEFT JOIN TAG_VOTI tv ON tv.id_tag = t.id_tag
WHERE t.id_frame = ?;
```

### Inserire un tag

```sql
INSERT INTO TAGS (id_frame, id_hardware, id_utente, coord_x, coord_y)
VALUES (?, ?, ?, ?, ?);
```

### Inserire un film con autore

```sql
INSERT INTO FILM (id_utente_creatore, titolo, anno_uscita, regista, sinossi, copertina_path)
VALUES (?, ?, ?, ?, ?, ?);
```

### Inserire un frame con autore

```sql
INSERT INTO FRAME (id_film, id_utente_creatore, immagine_path, timestamp_frame, descrizione_scena)
VALUES (?, ?, ?, ?, ?);
```

### Inserire hardware con autore

```sql
INSERT INTO HARDWARE (id_utente_creatore, nome_modello, produttore, anno_rilascio, descrizione, curiosita, prop_fittizio, immagine_path)
VALUES (?, ?, ?, ?, ?, ?, ?, ?);
```

### Registrare o aggiornare un voto

```sql
INSERT INTO TAG_VOTI (id_tag, id_utente, upvote, downvote)
VALUES (?, ?, ?, ?)
ON DUPLICATE KEY UPDATE
    upvote = VALUES(upvote),
    downvote = VALUES(downvote);
```

---

## Dati di test inclusi

Il database contiene dati iniziali:

* utenti:

  * admin
  * marta
* film:

  * Alien
  * WarGames
* hardware:

  * DEC VT100
  * IMSAI 8080
* frame associati
* tag di esempio

---

## Decisioni progettuali

* Uso di `DECIMAL` per coordinate → precisione e stabilità
* Uso di `TIME` per timestamp → tipo corretto
* Uso di `CASCADE` → semplifica pulizia dati
* Uso di tabella TAGS → struttura normalizzata
* Uso di `id_utente_creatore` su film, frame e hardware → tracciabilità semplice dei contributor
* Uso di `creato_il` sui contenuti → utile per dashboard e log personale
* Uso di `RESTRICT` sulle foreign key autore → evita cancellazioni accidentali di contenuti se un utente viene rimosso

---

## Limitazioni attuali

* Nessun supporto a più tag dello stesso hardware nello stesso frame
* Nessuna gestione avanzata di versioning
* Nessun badge o sistema reputazionale complesso
* La rimozione utenti non è una funzionalità prioritaria, perché con autore tracciato richiederebbe una politica dedicata

---

## Estensioni prioritarie

* sistema di voti (upvote/downvote) sui tag
* moderazione admin basata su punteggio e controllo manuale
* dashboard contributor con inserimento film, frame e hardware

---

## Estensioni secondarie future

* trust score utenti
* moderazione admin
* cronologia modifiche tag
* badge utente
* preferiti

---
