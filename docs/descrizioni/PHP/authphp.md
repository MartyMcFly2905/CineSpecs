# `php/auth.php` - Documentazione tecnica

## Scopo

`php/auth.php` e l'endpoint backend che gestisce autenticazione e registrazione.

Restituisce sempre JSON e supporta tre azioni:

* `register`
* `login`
* `logout`

## Struttura generale

Il file:

* include `config.php`;
* avvia la sessione;
* imposta `Content-Type: application/json`;
* rifiuta richieste diverse da `POST`;
* legge l'azione dal campo `action`.

La funzione:

```php
send_json($success, $message, $statusCode)
```

centralizza risposta JSON e codice HTTP.

## Registrazione

Se `action === 'register'`, il file:

* legge `username`, `email`, `password`;
* valida presenza e lunghezza;
* valida il formato email;
* controlla che username o email non esistano gia;
* genera `password_hash`;
* inserisce il nuovo utente con ruolo `contributor`.

## Login

Se `action === 'login'`, il file:

* legge `username` e `password`;
* recupera l'utente dal database;
* verifica la password con `password_verify`;
* rigenera l'id di sessione con `session_regenerate_id(true)`;
* salva in sessione `id_utente`, `username`, `ruolo`.

## Logout

Se `action === 'logout'`, il file:

* svuota `$_SESSION`;
* elimina il cookie di sessione;
* distrugge la sessione;
* restituisce JSON di conferma.

## Sicurezza

Le query usano PDO con prepared statements.
La password non viene mai salvata in chiaro.
Gli errori database vengono loggati ma non esposti nel dettaglio al client.

## Nota progettuale

Il file concentra tutta la logica auth minima in un solo endpoint.
Per un progetto universitario questo riduce duplicazione e rende il flusso piu facile da spiegare.
