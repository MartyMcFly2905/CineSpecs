# Architecture

## Frontend
- HTML semantico
- CSS separato per base, layout, componenti, animazioni
- JavaScript vanilla modulare per:
  - fetch API
  - rendering tag
  - viewer
  - validazione form

## Backend
- PHP con file endpoint separati
- Sessioni PHP
- PDO per accesso DB
- Output JSON per API asincrone

## Database
- UTENTI
- FILM
- HARDWARE
- FRAME
- TAGS
- TAG_VOTI

## Convenzioni
- Un endpoint PHP per responsabilità principale
- Nomi file chiari e prevedibili
- Niente architetture enterprise
- Ogni contenuto creato da contributor deve conservare id autore e timestamp
