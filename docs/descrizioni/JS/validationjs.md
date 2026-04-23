# `js/validation.js` - Documentazione tecnica

## Scopo

`validation.js` gestisce la validazione client-side dei form di login e registrazione.

Il file non sostituisce i controlli server-side, ma migliora l'esperienza utente mostrando errori immediati.

## Form supportati

Lo script cerca due form:

* `#login-form`
* `#register-form`

Se un form non esiste nella pagina corrente, la relativa inizializzazione viene saltata.

## Funzionamento

Su `DOMContentLoaded` vengono chiamate:

* `setupLoginValidation()`
* `setupRegisterValidation()`

Ogni funzione:

* prepara i campi;
* aggiunge un listener sul submit;
* blocca l'invio se i dati minimi non sono validi.

## Errori di campo

Per ogni `field-group`, lo script crea un elemento `.field-error` se non esiste gia.

Quando un controllo fallisce:

* il campo riceve `aria-invalid="true"`;
* il bordo diventa rosso;
* compare un messaggio testuale sotto il campo.

Quando l'utente modifica il valore, l'errore viene rimosso.

## Validazioni implementate

Login:

* username obbligatorio;
* password obbligatoria.

Registrazione:

* email obbligatoria;
* email con controllo minimo di formato;
* username obbligatorio;
* password obbligatoria;
* password minima di 8 caratteri.

## Nota progettuale

Il file usa solo DOM API standard e funzioni piccole.
La scelta mantiene il codice leggibile e coerente con i vincoli del progetto.
