# Manuale personale esposizione CineSpecs

Questo documento serve come guida rapida per spiegare il progetto all'esame. Descrive lo stato reale attuale e distingue le parti gia funzionanti dalla visione futura.

## Idea del progetto

CineSpecs e un archivio web di hardware usato nei film come oggetto di scena, reale o fittizio.

L'utente puo consultare un catalogo di film, aprire un viewer dei frame e vedere Pulse-Tag posizionati sopra l'immagine. Ogni tag rimanda a una scheda hardware con produttore, anno, descrizione, curiosita, autore del tag e punteggi upvote/downvote.

## Tecnologie

Il progetto rispetta i vincoli del corso:

* HTML5 per la struttura;
* CSS3 diviso in file dedicati;
* JavaScript vanilla con DOM API e Fetch API;
* PHP procedurale;
* MySQL/MariaDB;
* PDO con prepared statements.

Non ci sono framework, librerie esterne, build tool o transpiler.

## Stato attuale funzionante

Sono implementati:

* homepage/catalogo film con ricerca per titolo e filtro anno;
* viewer dei frame con timeline;
* Pulse-Tag letti da database;
* sidebar hardware con dettagli, autore e voti;
* login, registrazione, logout e sessioni;
* dashboard contributor protetta;
* inserimento film dalla dashboard;
* inserimento frame dalla dashboard;
* upload immagini per copertine e frame;
* area admin protetta, al momento come pagina base riservata.

## Cosa e gia modellato ma non ancora completo

Il database contiene gia le strutture per:

* hardware;
* tag;
* voti sui tag.

Il viewer legge gia i voti e li mostra. Pero l'utente non puo ancora votare dall'interfaccia, perche `php/api_vote.php` e ancora vuoto.

L'inserimento di tag non e ancora disponibile, perche `php/api_save_tag.php` e vuoto.

Il CRUD generico non e ancora disponibile, perche `php/api_crud.php` e vuoto.

## File principali

### `index.php`

E la homepage del progetto.

Responsabilita principali:

* apre la sessione;
* include la configurazione database;
* legge eventuali filtri `q` e `year`;
* recupera i film con PDO;
* mostra il catalogo;
* cambia header in base allo stato login.

Elemento da spiegare: l'header e dinamico. Se l'utente e ospite vede Login/Register, se e autenticato vede Dashboard, Logout e, se admin, anche Admin.

### `frame_viewer.php`

E la pagina piu importante lato esperienza utente.

Responsabilita principali:

* valida il parametro `film`;
* recupera film e frame;
* mostra il primo frame come principale;
* prepara la timeline;
* espone `data-frame-id` e altri `data-*` usati da JavaScript;
* mostra sidebar hardware e contenitore dei tag.

Elemento da spiegare: PHP prepara il markup e JavaScript aggiorna tag/sidebar senza ricaricare la pagina.

### `js/viewer.js`

Gestisce l'interazione del viewer.

Funzioni importanti:

* `caricaTag()` chiama `php/api_get_tags.php`;
* `disegnaTag()` crea i bottoni Pulse-Tag;
* `mostraDettagliHardware()` aggiorna la sidebar;
* `cambiaFrame()` cambia frame dalla timeline e ricarica i tag.

Elemento da spiegare: i tag sono posizionati con coordinate percentuali, quindi restano corretti anche con immagini responsive.

### `php/api_get_tags.php`

Endpoint JSON usato dal viewer.

Fa:

* controllo metodo GET;
* validazione di `id_frame`;
* query con JOIN tra `TAGS`, `HARDWARE`, `UTENTI` e `TAG_VOTI`;
* calcolo di upvote/downvote con `SUM`;
* risposta JSON pulita.

Elemento da spiegare: e un esempio chiaro di separazione tra backend dati e frontend interattivo.

### `login.php`

Mostra il form di login.

Invia i dati con `fetch` a `php/auth.php` usando `FormData`.
Se il login riesce, reindirizza a `dashboard.php`.

### `register.php`

Mostra il form di registrazione.

Invia email, username e password a `php/auth.php`.
Se la registrazione riesce, reindirizza a `login.php`.

### `php/auth.php`

Endpoint unico per autenticazione.

Gestisce:

* `register`;
* `login`;
* `logout`.

Punti importanti:

* password salvate con `password_hash`;
* verifica con `password_verify`;
* sessione rigenerata dopo login;
* nuovo utente registrato come `contributor`;
* risposte JSON con messaggi chiari.

### `dashboard.php`

Area protetta per utenti autenticati.

Permette:

* inserimento film;
* inserimento frame;
* visualizzazione statistiche personali su film e frame creati.

Punti importanti:

* autore preso dalla sessione, non dal client;
* validazione server-side;
* upload immagini controllato;
* controllo duplicati per film e frame;
* redirect con messaggi di successo.

### `js/validation.js`

Gestisce validazione client-side per:

* login;
* registrazione;
* form film;
* form frame;
* cambio pannelli nella dashboard.

Da chiarire all'esame: questa validazione migliora l'esperienza utente, ma la sicurezza vera resta lato server.

### `admin.php`

Pagina riservata agli admin.

Attualmente controlla:

* sessione esistente;
* ruolo `admin`.

Se l'utente non e admin viene mandato a `index.php`.
Per ora e una base pronta per la futura moderazione.

### `setup_database.sql`

Crea e popola il database `cinespecs`.

Tabelle:

* `UTENTI`;
* `FILM`;
* `HARDWARE`;
* `FRAME`;
* `TAGS`;
* `TAG_VOTI`.

Contiene seed per testare subito catalogo, viewer, timeline, tag e voti.

## Elementi particolari da spiegare

### Coordinate percentuali

I tag non usano pixel assoluti. Salvano `coord_x` e `coord_y` da 0 a 100.

Vantaggio: il tag resta nella posizione corretta anche se l'immagine cambia dimensione su mobile o desktop.

### Sessioni e ruoli

La sessione salva:

* `id_utente`;
* `username`;
* `ruolo`.

Il ruolo serve per distinguere contributor e admin.

### Autore dei contenuti

Film, frame, hardware e tag hanno un riferimento all'utente creatore.

Nella parte gia implementata, dashboard salva l'autore di film e frame prendendolo dalla sessione.

### Voti

La tabella `TAG_VOTI` usa chiave composta:

* `id_tag`;
* `id_utente`.

Questo impedisce piu voti dello stesso utente sullo stesso tag.
Attualmente i voti sono mostrati nel viewer, ma non sono ancora modificabili dall'interfaccia.

### Upload immagini

La dashboard accetta solo:

* JPG;
* PNG;
* WEBP.

Limite massimo: 5 MB.

Il nome file viene generato dal server per evitare di fidarsi del nome originale caricato dall'utente.

## Visione futura

Le prossime estensioni coerenti con il progetto sono:

* implementare `php/api_vote.php` per permettere upvote/downvote da viewer;
* aggiungere i pulsanti di voto nella sidebar;
* implementare `php/api_save_tag.php` per aggiungere tag da utente autenticato;
* aggiungere inserimento hardware dalla dashboard;
* trasformare `admin.php` in una vera area moderazione;
* permettere eliminazione tag errati da autore o admin;
* mostrare una lista dei propri contributi in dashboard;
* calcolare statistiche piu complete, per esempio tag creati e punteggio ricevuto.

## Frase utile per l'esposizione

Il progetto e stato costruito separando le responsabilita: PHP prepara pagine e API sicure, il database mantiene relazioni e vincoli, JavaScript aggiorna il viewer senza reload, e la dashboard permette gia i contributi base mantenendo autore e validazione lato server.
