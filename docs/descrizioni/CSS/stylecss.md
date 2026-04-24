# `css/style.css` - Descrizione tecnica

## Scopo

`style.css` contiene le regole visive globali del progetto.

Gestisce:

* variabili CSS;
* tema chiaro e tema scuro;
* tipografia base;
* container comuni;
* aspetto generale di pannelli, bottoni e campi.

## Variabili principali

Il file definisce in `:root` le variabili condivise, per esempio:

* `--color-bg`
* `--color-panel`
* `--color-border`
* `--color-text`
* `--color-accent`
* `--color-focus-ring`

Questo permette di mantenere coerenza tra homepage, viewer, dashboard e pagine auth.

## Tema scuro

Il tema scuro usa:

```css
:root[data-theme="dark"] { ... }
```

Quando JavaScript imposta `data-theme="dark"` su `<html>`, tutte le variabili cambiano in blocco.

Nel tema scuro e presente anche uno sfondo con gradienti radiali leggeri, per dare profondita senza introdurre immagini o librerie.

## Componenti globali

Il file normalizza l'aspetto di:

* `.site-header`
* `.theme-toggle`
* `.search-panel`
* `.film-card`
* `.empty-state`
* `.auth-card`
* `.auth-intro`

In pratica `style.css` definisce il "look" generale, mentre `layout.css` decide il posizionamento.

## Campi e pulsanti

Le regole comuni per input e button garantiscono:

* altezza minima uniforme;
* contrasto coerente;
* hover e focus visibili;
* comportamento consistente tra form pubblici e aree riservate.

## Nota per l'esame

Questo file centralizza il tema dell'applicazione e riduce duplicazione visiva tra pagine diverse.
