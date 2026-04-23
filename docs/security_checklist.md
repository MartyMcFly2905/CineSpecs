# Security Checklist

## Autenticazione
- password_hash()
- password_verify()
- session_regenerate_id() dopo login

## Autorizzazione
- controllare ruolo admin dove richiesto
- controllare login sugli endpoint protetti

## Input e output
- validazione lato server sempre
- cast espliciti per interi e float
- htmlspecialchars in output HTML
- prepared statements ovunque

## Richieste HTTP
- controllare metodo atteso
- gestire errori con status code
- prevedere CSRF token nei form sensibili

## File e path
- usare percorsi relativi controllati
- non fidarsi mai di nomi file da input