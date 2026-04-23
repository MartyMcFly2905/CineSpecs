# `js/theme-init.js` - Documentazione tecnica

## Scopo

`theme-init.js` applica subito il tema salvato prima del rendering completo della pagina.

Serve a evitare il flash iniziale in tema chiaro quando l'utente aveva gia scelto il tema scuro.

## Funzionamento

Lo script:

* legge `localStorage`;
* usa la chiave `cinespecs-theme`;
* se trova `dark`, imposta `data-theme="dark"` su `<html>`.

Il tutto avviene dentro una IIFE, cosi non vengono lasciate variabili globali.

## Perche e separato da `theme-toggle.js`

`theme-init.js` ha un solo compito di bootstrap.
`theme-toggle.js` gestisce invece l'interazione completa con il pulsante.

Questa separazione mantiene piu chiaro il ruolo dei due file.

## Dove viene usato

Le pagine piu recenti del progetto lo caricano nell'`head`, prima dei CSS:

* `login.php`
* `register.php`
* `dashboard.php`
* `admin.php`
* `frame_viewer.php`

In questo modo il tema corretto e applicato il prima possibile.

## Limiti

Il file non:

* aggiorna il logo;
* salva preferenze;
* gestisce click o accessibilita del pulsante.

## Nota progettuale

E uno script piccolo ma utile, separato dal resto per mantenere il caricamento del tema rapido e facile da spiegare.
