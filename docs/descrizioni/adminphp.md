# `admin.php` - Documentazione tecnica

## Scopo

`admin.php` e una pagina riservata agli utenti con ruolo `admin`.

In questa fase non contiene ancora strumenti gestionali completi, ma fornisce:

* controllo accesso;
* header coerente con sessione attiva;
* placeholder dell'area amministrativa.

## Controllo autorizzazione

Il file verifica due condizioni:

* esistenza della sessione utente;
* ruolo esattamente uguale a `admin`.

Se manca la sessione, l'utente viene reindirizzato a `login.php`.
Se la sessione esiste ma il ruolo non e corretto, il redirect va a `index.php`.

## Output

La pagina mostra:

* username in header;
* link a dashboard e logout;
* messaggio che segnala la natura riservata dell'area.

## Nota progettuale

La pagina e volutamente minima:

* controllo accessi lato server;
* output sanificato con `htmlspecialchars`;
* nessuna logica superflua finche l'area admin non viene estesa.
