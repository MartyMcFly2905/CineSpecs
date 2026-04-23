# `frame_viewer.php` - Documentazione tecnica

## Scopo

`frame_viewer.php` mostra i frame collegati a un film specifico e rappresenta la schermata piu ricca del progetto.

La pagina combina:

* rendering PHP lato server;
* dati recuperati dal database;
* interazione client-side per tag e timeline;
* stato utente nell'header.

## Flusso iniziale

Il file:

1. avvia la sessione;
2. include `php/config.php`;
3. valida `film` dalla query string;
4. recupera i dati del film;
5. recupera i frame ordinati per timestamp;
6. sceglie il primo frame come frame principale.

Se il parametro e errato, il film non esiste o i frame mancano, viene mostrato un messaggio chiaro.

## Header con sessione

Come nella homepage, anche qui l'header cambia in base allo stato utente.

Per gli ospiti:

* menu con `Accedi` e `Registrati`.

Per gli utenti autenticati:

* saluto con username;
* link dashboard;
* link admin se il ruolo e `admin`;
* logout.

## Viewer principale

La parte centrale contiene:

* immagine principale;
* `data-frame-id` per i tag;
* contenitore `#tag-layer`;
* didascalia con timestamp e descrizione;
* sidebar hardware.

Sotto compare la timeline dei frame, dove ogni elemento ha attributi `data-*` usati da `js/viewer.js`.

## Sicurezza

I dati stampati in HTML vengono escapati con `htmlspecialchars`.
Le query usano prepared statements PDO.

## File collegati

Il viewer dipende in particolare da:

* `css/components.css`
* `js/viewer.js`
* `js/theme-init.js`
* `js/theme-toggle.js`

## Nota progettuale

Pur essendo la pagina piu articolata, il file resta procedurale e leggibile, con responsabilita abbastanza separate tra PHP, CSS e JavaScript.
