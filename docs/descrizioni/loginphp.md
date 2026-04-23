# `login.php` - Documentazione tecnica

## Scopo

`login.php` mostra la pagina di accesso di CineSpecs.

La pagina ha due responsabilita:

* mostrare un form semplice per username e password;
* inviare i dati a `php/auth.php` senza ricaricare la pagina.

Se esiste gia una sessione valida, l'utente viene reindirizzato a `dashboard.php`.

## Controllo sessione iniziale

All'inizio del file viene eseguito:

```php
session_start();
```

Subito dopo viene controllata la presenza di:

* `id_utente`
* `username`
* `ruolo`

Se tutti questi dati esistono, il login non serve piu e quindi viene fatto redirect.

## Struttura della pagina

La pagina contiene:

* header con logo e toggle tema;
* colonna introduttiva;
* card del form di login;
* area messaggi sotto il form.

Il form usa:

```html
<input type="hidden" name="action" value="login">
```

Questo permette di riutilizzare `php/auth.php` come endpoint unico anche per altre azioni.

## Invio asincrono

Lo script inline finale:

* intercetta il `submit`;
* mostra un messaggio temporaneo;
* invia i dati con `fetch` e `FormData`;
* legge la risposta JSON;
* aggiorna `#login-message`;
* reindirizza a `dashboard.php` in caso di successo.

## Validazione client-side

`login.php` carica anche `js/validation.js`.

La validazione lato client:

* controlla che username e password non siano vuoti;
* mostra messaggi vicini ai campi;
* non sostituisce la validazione server-side di `php/auth.php`.

## Nota progettuale

Il file resta volutamente lineare:

* niente librerie esterne;
* niente componenti astratti;
* PHP solo per controllo sessione e cache busting;
* logica interattiva minima in JavaScript vanilla.
