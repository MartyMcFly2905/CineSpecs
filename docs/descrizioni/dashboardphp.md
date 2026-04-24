# `dashboard.php` - Documentazione tecnica

## Scopo

`dashboard.php` e l'area contributor del progetto.

Permette a un utente autenticato di:

* inserire un nuovo film;
* caricare un frame associato a un film gia esistente.

## Controllo accesso

La pagina richiede sempre una sessione valida con:

* `id_utente`
* `username`
* `ruolo`

Se la sessione manca, l'utente viene reindirizzato a `login.php`.

## Struttura generale

Il file resta procedurale e concentra in un solo punto:

* controlli di sessione;
* validazione server-side;
* upload immagini;
* query PDO per insert e controlli duplicati;
* rendering HTML dei form.

## Azioni gestite

Le azioni arrivano via `POST` con un campo nascosto `action`.

### `create_film`

Salva:

* titolo;
* anno di uscita;
* regista;
* sinossi opzionale;
* copertina opzionale.

Prima dell'inserimento controlla se esiste gia un film con stesso titolo, anno e regista.

### `create_frame`

Salva:

* film selezionato da database;
* timestamp;
* immagine del frame;
* descrizione scena opzionale.

Prima dell'inserimento controlla:

* che il film esista davvero;
* che il timestamp sia valido;
* che non esista gia un frame con lo stesso timestamp per lo stesso film.
## Upload immagini

Gli upload sono gestiti direttamente nel file con una funzione dedicata che:

* accetta solo JPG, PNG e WEBP;
* limita la dimensione a 5 MB;
* crea la cartella se manca;
* genera un nome file univoco;
* salva il percorso relativo nel database.

Le cartelle usate sono:

* `assets/covers`
* `assets/frames`

## Sicurezza

La pagina usa:

* PDO con prepared statements;
* validazione lato server su tutti i campi;
* autore preso dalla sessione e mai dal client;
* output HTML escapato con `htmlspecialchars`.

## Interfaccia

La dashboard mostra:

* intestazione contributor;
* statistiche sintetiche dei contenuti creati;
* messaggi di esito;
* due form distinti e lineari.

Il form dei frame usa una select popolata dal database, cosi l'utente puo agganciare il nuovo frame solo a film realmente esistenti.

## Nota progettuale

Il file resta volutamente semplice per una consegna universitaria:

* niente framework;
* niente service layer;
* niente classi;
* logica leggibile e facile da spiegare oralmente.
