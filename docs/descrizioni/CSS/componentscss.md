# `css/components.css` - Documentazione tecnica

## Scopo del file

`components.css` contiene gli stili dei componenti specifici del viewer.

Le aree principali sono:

* header contenutistico del viewer;
* frame principale;
* Pulse-Tag;
* sidebar hardware;
* timeline dei frame.

## Layout viewer

Il viewer usa una griglia a due colonne:

* area principale per il frame;
* sidebar laterale da `300px`.

Sotto `900px` il layout diventa a colonna unica.

## Frame e tag

`.frame-tag-layer` e il contenitore relativo del frame.
Al suo interno `#tag-layer` occupa tutto lo spazio con `position: absolute`.

Questo permette a `js/viewer.js` di inserire i Pulse-Tag sopra l'immagine senza modificare il layout.

## Pulse-Tag

La classe `.pulse-tag` definisce:

* forma circolare;
* nucleo centrale;
* due anelli animati;
* glow esterno;
* area cliccabile fissa.

Le animazioni vere e proprie arrivano da `animations.css`, importato in testa al file.

## Sidebar hardware

La sidebar usa uno stile piu tecnico:

* font terminale;
* testi in pannelli separati;
* accento cromatico coerente con il tema.

Serve a dare ai dettagli hardware un'identita diversa rispetto al catalogo film.

## Timeline

La timeline usa card cliccabili con:

* miniatura;
* titolo del frame;
* timestamp.

Lo stato `.timeline-item--active` evidenzia il frame attualmente mostrato sopra.

## Nota per l'esame

Il file dimostra come isolare in un foglio dedicato i componenti di una singola schermata complessa, lasciando in `style.css` e `layout.css` solo le regole globali.
