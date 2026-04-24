# `setup_database.sql` - Descrizione tecnica

## Scopo

`setup_database.sql` crea e popola il database `cinespecs`.

Serve per avere un ambiente di test ripetibile durante lo sviluppo e la presentazione del progetto.

## Operazioni principali

Il file:

1. crea il database se non esiste;
2. seleziona il database;
3. elimina le tabelle esistenti;
4. ricrea lo schema;
5. inserisce dati iniziali.

## Tabelle

Le tabelle principali sono:

* `UTENTI`
* `FILM`
* `HARDWARE`
* `FRAME`
* `TAGS`
* `TAG_VOTI`

La tabella `TAGS` collega frame e hardware usando coordinate percentuali.
Le tabelle `FILM`, `HARDWARE` e `FRAME` tracciano anche l'utente creatore.

## Vincoli

Lo schema usa:

* primary key;
* foreign key;
* `ON DELETE CASCADE`;
* `ON DELETE RESTRICT` dove conviene preservare i contenuti creati;
* vincoli `CHECK` sulle coordinate;
* vincolo `UNIQUE (id_frame, id_hardware)`;
* vincolo `PRIMARY KEY (id_tag, id_utente)` su `TAG_VOTI`;
* check su `TAG_VOTI` per permettere un solo tipo di voto tra `upvote` e `downvote`.

## Dati di test

Il seed include film, frame, hardware, tag gia compilati e alcuni voti di esempio.

Per `Ritorno al futuro` sono presenti piu frame, cosi si puo testare la timeline interattiva del viewer.

Ogni film, frame, hardware e tag di esempio ha un autore collegato, cosi la dashboard puo distinguere i contributi dei diversi utenti.

I voti di esempio sono salvati in `TAG_VOTI` tramite due campi espliciti:

* `upvote`
* `downvote`

## Nota importante

Il file contiene `DROP TABLE`, quindi reimportarlo resetta i dati esistenti.

Per aggiornare un database gia popolato, e meglio usare query `INSERT` o `UPDATE` mirate.

La presenza degli autori sui contenuti rende sconsigliata la cancellazione diretta degli utenti senza una logica dedicata.
