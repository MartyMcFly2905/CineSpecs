# Coding Rules

## Regole generali
- Preferire semplicità a riusabilità astratta
- Non creare helper generici se servono una sola volta
- Non creare classi se bastano funzioni
- No dipendenze esterne

## JavaScript
- Usare addEventListener
- Usare fetch con async/await quando rende il codice più chiaro
- Validare input lato client ma non fidarsi mai del client
- Non usare mai var, solo let

## PHP
- Usare require per config comuni
- Mai concatenare SQL con input utente
- Rispondere con http_response_code quando opportuno
- Restituire JSON uniforme:
  - success
  - message
  - data opzionale

## CSS
- Nomi classi chiari
- Evitare effetti complicati se non stabili
- Preferire layout robusti a layout spettacolari