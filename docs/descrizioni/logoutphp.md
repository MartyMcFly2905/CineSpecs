# `logout.php` - Documentazione tecnica

## Scopo

`logout.php` chiude la sessione PHP corrente e riporta l'utente alla homepage.

E una versione server-side, utile come link diretto nelle aree protette.

## Flusso

Il file esegue questi passaggi:

1. `session_start()`;
2. svuota `$_SESSION`;
3. elimina il cookie di sessione, se attivo;
4. chiama `session_destroy()`;
5. reindirizza a `index.php`.

## Perche elimina anche il cookie

La sola `session_destroy()` non garantisce da sola la rimozione del cookie lato browser.

Per questo il file usa:

```php
setcookie(session_name(), '', time() - 3600, ...)
```

cosi la sessione viene chiusa in modo piu pulito.

## Nota progettuale

Questo file non produce HTML e non restituisce JSON.
Serve solo come endpoint semplice di uscita quando l'utente clicca il link "Logout" nell'interfaccia.
