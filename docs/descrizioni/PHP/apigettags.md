# `php/api_get_tags.php` - Descrizione tecnica

## Scopo

`api_get_tags.php` e un endpoint PHP usato dal frame viewer per recuperare i Pulse-Tag associati a un frame.

Non produce HTML: restituisce solo JSON, quindi e pensato per essere chiamato tramite `fetch()` da JavaScript.

## Flusso

1. include `php/config.php` per usare la connessione PDO;
2. imposta `Content-Type: application/json`;
3. accetta solo richieste `GET`;
4. legge `id_frame` dalla query string;
5. valida `id_frame` come intero positivo;
6. esegue una query con `JOIN` tra `TAGS`, `HARDWARE`, `UTENTI` e `TAG_VOTI`;
7. calcola separatamente upvote e downvote del tag;
8. restituisce coordinate, autore, data e dati hardware necessari alla sidebar.

## Sicurezza

Il parametro `id_frame` viene validato lato server con `FILTER_VALIDATE_INT`.

La query usa un prepared statement:

```php
$stmt = $pdo->prepare(...);
$stmt->execute([$idFrame]);
```

Questo evita SQL injection e mantiene lo stile coerente con il resto del progetto.

## Risposta JSON

In caso positivo:

```json
{
  "success": true,
  "data": [
    {
      "id_tag": 1,
      "coord_x": 46.5,
      "coord_y": 61.2,
      "creato_il": "2026-04-24 12:00:00",
      "upvotes": 1,
      "downvotes": 0,
      "autore": {
        "id": 1,
        "username": "admin"
      },
      "hardware": {
        "id": 1,
        "nome_modello": "DEC VT100"
      }
    }
  ]
}
```

In caso di errore:

```json
{
  "success": false,
  "message": "Messaggio chiaro",
  "data": []
}
```

## Nota per l'esame

Questo file mostra la separazione tra backend e frontend: PHP recupera dati dal database in modo sicuro, mentre JavaScript li usa per aggiornare l'interfaccia senza ricaricare la pagina.
