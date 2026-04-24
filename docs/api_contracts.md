# API Contracts

## GET /php/api_get_tags.php?id_frame=INT
Restituisce i tag del frame con dati hardware collegati.

### Response JSON
{
  "success": true,
  "data": [
    {
      "id_tag": 1,
      "coord_x": 45.5,
      "coord_y": 60.2,
      "upvotes": 3,
      "downvotes": 1,
      "user_vote": "up",
      "autore": {
        "id": 2,
        "username": "marta"
      },
      "hardware": {
        "id": 3,
        "nome_modello": "DEC VT100",
        "produttore": "Digital Equipment Corporation",
        "anno_rilascio": 1978,
        "descrizione": "..."
      }
    }
  ]
}

## POST /php/api_save_tag.php
### Input
- id_frame
- id_hardware
- coord_x
- coord_y

### Requisiti
- utente autenticato
- coordinate in percentuale
- validazione server-side
- ritorna JSON
- usato dal viewer in modalita contributor
- l'autore del tag e preso dalla sessione, non dal client

### Response JSON minima
{
  "success": true,
  "message": "Tag salvato.",
  "data": {
    "id_tag": 12
  }
}

## POST /php/api_vote.php
### Input
- id_tag
- vote (`up`|`down`)

### Requisiti
- utente autenticato
- un solo voto per utente per tag
- aggiornamento o sostituzione del voto esistente
- ritorna JSON con punteggio aggiornato

### Response JSON minima
{
  "success": true,
  "message": "Voto registrato.",
  "data": {
    "id_tag": 12,
    "upvotes": 4,
    "downvotes": 1,
    "user_vote": "down"
  }
}

## POST /php/api_crud.php
Gestisce solo le azioni realmente necessarie a dashboard contributor e admin.

### Action minime previste
- `create_film`
- `create_frame`
- `list_my_content`
- `delete_tag` solo per autore o admin
- `delete_content` per moderazione admin, se implementato

### Regole
- usare parametro `action` esplicito
- controllare sessione in ogni richiesta protetta
- controllare proprieta del contenuto o ruolo admin
- l'autore di film, frame e hardware deve essere preso dalla sessione
- validazione rigorosa lato server
- JSON uniforme con `success`, `message`, `data`
