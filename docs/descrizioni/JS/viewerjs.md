# `js/viewer.js` - Descrizione tecnica

## Scopo

`viewer.js` gestisce la parte interattiva del frame viewer.

Serve a:

* leggere il frame corrente dal DOM;
* caricare i tag con `fetch`;
* disegnare i Pulse-Tag sopra l'immagine;
* mostrare i dettagli hardware nella sidebar;
* cambiare frame tramite timeline senza ricaricare la pagina.

## Inizializzazione

Il codice parte su `DOMContentLoaded`, quindi viene eseguito solo quando gli elementi HTML sono disponibili.

Gli elementi principali sono:

* `#frame-image`
* `#tag-layer`
* `#sidebar-content`
* `#sidebar-meta`
* `#sidebar-title`
* `#sidebar-votes`
* `.timeline-list`
* `#frame-timestamp`
* `#frame-description`
* `#frame-author`

Se mancano elementi fondamentali, il codice si ferma e scrive un errore in console.

## Caricamento dei tag

La funzione `caricaTag()` chiama:

```text
php/api_get_tags.php?id_frame=ID
```

Usa Fetch API, controlla `response.ok`, legge il JSON e passa i dati a `disegnaTag()`.

## Pulse-Tag

I tag vengono creati come bottoni:

```js
const pulseTag = document.createElement('button');
```

La posizione viene applicata con:

* `left` in percentuale;
* `top` in percentuale;
* `transform: translate(-50%, -50%)`.

Le coordinate percentuali permettono ai tag di restare corretti anche se l'immagine cambia dimensione.

## Sidebar hardware

Al click su un tag, `mostraDettagliHardware()` svuota la sidebar e inserisce:

* produttore;
* anno;
* descrizione;
* curiosita.

In parallelo aggiorna l'header della sidebar con:

* nome del prop in `#sidebar-title`;
* autore e data del tag in `#sidebar-meta`;
* due badge distinti in `#sidebar-votes` per upvote e downvote.

Il testo viene inserito con `textContent` e `createTextNode`, non con HTML libero. Questo riduce il rischio di inserire markup non controllato.

## Timeline

La timeline usa i dati gia presenti nel markup tramite attributi `data-*`.

Al click su una miniatura:

1. cambia immagine principale;
2. aggiorna `data-frame-id`;
3. aggiorna descrizione, timestamp e autore del frame;
4. sposta la classe `.timeline-item--active`;
5. ricarica i tag del nuovo frame.

## Nota per l'esame

Il file usa solo DOM API e Fetch API, senza framework. La logica e divisa in funzioni piccole e leggibili, cosi ogni parte puo essere spiegata separatamente.
