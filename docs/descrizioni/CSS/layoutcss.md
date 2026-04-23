# `css/layout.css` - Descrizione tecnica

## Scopo

`layout.css` raccoglie i layout condivisi dell'interfaccia.

Gestisce soprattutto:

* header e navigazione utente;
* form della homepage;
* card film;
* layout delle pagine auth;
* stati responsive principali.

## Header e navigazione

L'header usa Flexbox tramite `.header-inner` e `.header-actions`.

Nel file sono presenti anche le regole per:

* `.header-auth`
* `.header-user`
* `.auth-menu`
* `.auth-menu-panel`

Queste classi servono a mostrare in modo coerente:

* link login e registrazione per ospiti;
* saluto, dashboard, admin e logout per utenti autenticati.

## Form e gruppi campo

I blocchi `.field-group` usano layout verticale con etichetta, controllo e spazio dedicato all'errore.

La classe `.field-error` e importante perché:

* riserva spazio stabile sotto il campo;
* evita spostamenti del bottone quando appare un messaggio;
* supporta anche messaggi che vanno su due righe.

Questa correzione e stata necessaria soprattutto per la scheda login.

## Homepage

Il form di ricerca principale usa CSS Grid:

```css
grid-template-columns: 1.4fr 1fr auto;
```

La griglia del catalogo usa `repeat(auto-fill, minmax(...))`, cosi il numero di card si adatta alla larghezza disponibile.

## Pagine auth

Le pagine `login.php` e `register.php` usano:

* `.auth-container`
* `.auth-shell`
* `.auth-intro`
* `.auth-card`
* `.auth-form`
* `.login-form`
* `.register-form`
* `.auth-submit`
* `.auth-feedback`

L'obiettivo e separare bene:

* colonna testuale;
* card del form;
* messaggio finale dell'operazione.

## Responsive

Sotto `900px` il layout auth passa a una sola colonna.
Sotto `640px` l'header si dispone in verticale per evitare sovrapposizioni.

## Nota per l'esame

Il file mostra un uso pratico di Flexbox e Grid per costruire layout robusti e riutilizzabili senza framework CSS.
