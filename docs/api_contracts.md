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

## POST /php/api_vote.php
### Input
- id_tag
- voto (up|down)

## POST /php/api_crud.php
Gestisce inserimento/modifica/eliminazione di film, frame, hardware.
Usare action esplicita e validazione rigorosa.