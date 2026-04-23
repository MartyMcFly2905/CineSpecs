# Database Notes

## Principi
- Schema in 3NF
- Foreign key con ON DELETE CASCADE dove sensato
- Coordinate sempre in percentuale
- Evitare duplicati di tag nello stesso frame

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

---

## Schema generale delle relazioni

* Un **FILM** ha molti **FRAME**
* Un **FRAME** può avere molti **TAGS**
* Un **TAG** collega:

  * un FRAME
  * un HARDWARE
  * un UTENTE (chi ha inserito il tag)
* Un **HARDWARE** può apparire in molti FRAME

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

---

## Tabella: FILM

Contiene i film.

### Campi

* `id_film` (PK)
* `titolo`
* `anno_uscita`
* `regista`
* `sinossi` (opzionale)
* `copertina_path` (opzionale)

### Note

* `copertina_path` è un percorso relativo (es: assets/covers/alien.jpg)

---

## Tabella: HARDWARE

Contiene i dispositivi hardware reali o fittizi.

### Campi

* `id_hardware` (PK)
* `nome_modello`
* `produttore`
* `anno_rilascio` (opzionale)
* `descrizione` (opzionale)
* `curiosita` (opzionale)
* `prop_fittizio` (boolean)
* `immagine_path` (opzionale)

### Note

* `prop_fittizio = 0` → hardware reale
* `prop_fittizio = 1` → oggetto fittizio

---

## Tabella: FRAME

Contiene i fotogrammi dei film.

### Campi

* `id_frame` (PK)
* `id_film` (FK → FILM)
* `immagine_path`
* `timestamp_frame` (TIME)
* `descrizione_scena` (opzionale)

### Vincoli

* FOREIGN KEY su `id_film`
* ON DELETE CASCADE

### Note

* Se un film viene eliminato, tutti i suoi frame vengono eliminati

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
SELECT t.coord_x, t.coord_y, h.*
FROM TAGS t
JOIN HARDWARE h ON t.id_hardware = h.id_hardware
WHERE t.id_frame = ?;
```

### Inserire un tag

```sql
INSERT INTO TAGS (id_frame, id_hardware, id_utente, coord_x, coord_y)
VALUES (?, ?, ?, ?, ?);
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

---

## Limitazioni

* Nessun supporto a più tag dello stesso hardware nello stesso frame
* Nessuna gestione avanzata di versioning o moderazione

---

## Possibili estensioni future

* sistema di voti (upvote/downvote)
* trust score utenti
* moderazione admin
* cronologia modifiche tag

---
