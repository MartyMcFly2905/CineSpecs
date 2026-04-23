# `theme-toggle.js` – Documentazione tecnica

## Scopo

`theme-toggle.js` gestisce il cambio tema lato client.

Le sue responsabilita sono:

* leggere il tema attuale;
* alternare chiaro e scuro;
* salvare la scelta in `localStorage`;
* aggiornare il testo del pulsante;
* aggiornare il logo in base al tema.

## Struttura

Il file e racchiuso in una IIFE per non sporcare lo scope globale.

Gli elementi principali cercati nel DOM sono:

* `#theme-toggle`
* `#brand-logo`

Se il pulsante non esiste, lo script termina senza errori.

## Funzioni principali

`getCurrentTheme()`
legge `data-theme` da `<html>`.

`saveTheme(theme)`
salva il valore nella chiave `cinespecs-theme`.

`updateButton(theme)`
aggiorna testo e `aria-pressed`.

`updateLogo(theme)`
cambia il file immagine leggendo `data-light-logo` e `data-dark-logo`.

`applyTheme(theme)`
centralizza l'intero aggiornamento dell'interfaccia.

## Flusso

Quando la pagina e pronta:

* bottone e logo vengono sincronizzati con il tema gia attivo;
* al click, il tema viene invertito e salvato.

## Nota progettuale

La logica e volutamente semplice e leggibile.
Non usa framework e dipende solo da attributi HTML gia presenti nel markup.
