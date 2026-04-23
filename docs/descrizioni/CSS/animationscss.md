# `css/animations.css` - Descrizione tecnica

## Scopo

`animations.css` contiene le animazioni dei Pulse-Tag del frame viewer.

Il file e separato da `components.css` per mantenere isolate le animazioni e rendere piu chiara la struttura degli stili.

## Animazioni presenti

Sono definite due animazioni:

* `pulse-tag-middle`
* `pulse-tag-outer`

Entrambe modificano:

* `opacity`
* `transform: scale(...)`

Questo crea l'effetto di un segnale che si espande dal centro del tag.

## Accessibilita

Il file include:

```css
@media (prefers-reduced-motion: reduce)
```

Se l'utente preferisce ridurre le animazioni, i Pulse-Tag restano visibili ma non animati.

## Nota per l'esame

Questa scelta mostra attenzione all'accessibilita e alla separazione delle responsabilita: l'animazione e definita in CSS, mentre JavaScript gestisce solo dati e interazione.
