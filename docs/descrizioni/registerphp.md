# `register.php` - Documentazione tecnica

## Scopo

`register.php` gestisce la schermata di registrazione pubblica.

Permette di creare un nuovo account contributor con:

* email
* username
* password

Se l'utente e gia autenticato, la pagina effettua redirect verso `dashboard.php`.

## Struttura

La pagina usa la stessa impostazione visiva del login:

* header coerente con il resto del sito;
* sezione introduttiva laterale;
* card con il form;
* area feedback finale.

Il form contiene:

```html
<input type="hidden" name="action" value="register">
```

In questo modo il backend puo distinguere la registrazione dal login usando lo stesso endpoint `php/auth.php`.

## Invio dei dati

Il submit viene gestito con `fetch`:

* i dati vengono raccolti con `new FormData(registerForm)`;
* la risposta JSON aggiorna `#register-message`;
* in caso di successo viene aperto `login.php`.

## Validazione client-side

La pagina carica `js/validation.js`.

La validazione lato client controlla:

* email non vuota;
* formato email minimo;
* username non vuoto;
* password non vuota;
* lunghezza minima di 8 caratteri.

I controlli reali restano comunque lato server in `php/auth.php`.

## Nota progettuale

Il file e pensato come pagina di accesso semplice e spiegabile oralmente:

* HTML semantico;
* nessun framework;
* JavaScript ridotto al necessario;
* coerenza visiva con `login.php`.
