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

La tabella `TAGS` collega frame e hardware usando coordinate percentuali.

## Vincoli

Lo schema usa:

* primary key;
* foreign key;
* `ON DELETE CASCADE`;
* vincoli `CHECK` sulle coordinate;
* vincolo `UNIQUE (id_frame, id_hardware)`.

## Dati di test

Il seed include film, frame, hardware e tag gia compilati.

Per `Ritorno al futuro` sono presenti piu frame, cosi si puo testare la timeline interattiva del viewer.

## Nota importante

Il file contiene `DROP TABLE`, quindi reimportarlo resetta i dati esistenti.

Per aggiornare un database gia popolato, e meglio usare query `INSERT` o `UPDATE` mirate.
