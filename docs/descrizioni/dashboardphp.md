# `dashboard.php` - Documentazione tecnica

## Scopo

`dashboard.php` e la prima pagina privata per utenti autenticati.

Per ora funziona come area riservata minima e mostra:

* username corrente;
* ruolo corrente;
* link utili di navigazione.

## Controllo accesso

Il file richiede una sessione valida con:

* `id_utente`
* `username`
* `ruolo`

Se questi dati non esistono, l'utente viene reindirizzato a `login.php`.

## Header dinamico

Nell'header compaiono:

* saluto con username;
* link `Admin` solo se il ruolo in sessione e `admin`;
* link `Logout`.

## Sicurezza

Lo username e il ruolo vengono stampati con `htmlspecialchars`.

Anche se i valori arrivano dalla sessione, vengono comunque trattati come output da escapare.

## Nota progettuale

La pagina e una base semplice per sviluppi futuri.
Al momento privilegia chiarezza e controllo accessi rispetto a funzionalita aggiuntive.
