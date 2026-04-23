# `index.php` – Documentazione tecnica

## Scopo

`index.php` e la homepage pubblica di CineSpecs.

Le sue responsabilita principali sono:

* mostrare il catalogo dei film;
* filtrare per titolo e anno;
* mostrare uno stato header diverso per ospiti e utenti autenticati.

## Flusso server-side

Il file:

1. avvia la sessione;
2. include `php/config.php`;
3. legge `q` e `year` dalla query string;
4. recupera gli anni disponibili;
5. costruisce la query del catalogo;
6. esegue la query con PDO;
7. genera HTML dinamico.

In caso di errore database viene mostrato uno stato vuoto con messaggio generico.

## Gestione sessione nell'header

La homepage ora distingue due casi.

Ospite:

* compare un menu `details` con link a `login.php` e `register.php`.

Utente autenticato:

* compare il saluto con username;
* link a `dashboard.php`;
* link `admin.php` se il ruolo e `admin`;
* link `logout.php`.

Questo rende la homepage il punto di ingresso anche per la parte privata del progetto.

## Catalogo

La query principale recupera:

* `id_film`
* `titolo`
* `anno_uscita`
* `regista`
* `copertina_path`

Il filtro titolo usa `LIKE`, mentre l'anno viene validato con `FILTER_VALIDATE_INT`.

Tutti i valori mostrati nell'HTML vengono escapati con `htmlspecialchars`.

## Tema

La pagina contiene uno script inline minimo nell'`head` per applicare subito il tema salvato.
Alla fine del `body` carica `js/theme-toggle.js` per il cambio tema interattivo.

## Nota progettuale

`index.php` resta una pagina PHP procedurale semplice, ma ora integra anche il primo livello di stato autenticato dell'applicazione.
