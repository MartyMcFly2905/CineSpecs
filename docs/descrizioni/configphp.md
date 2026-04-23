# Database Connection – `php/config.php`

## Scopo del file

Il file `php/config.php` gestisce la connessione al database MySQL tramite PDO.

Questo file deve essere incluso (`require` o `include`) in tutti i file PHP che necessitano accesso al database.

Alla fine dell'esecuzione, rende disponibile la variabile globale:

```php
$pdo
```

---

## Codice completo

```php
<?php

// Configurazione database
$dbHost = '127.0.0.1';
$dbName = 'cinespecs';
$dbUser = 'root';
$dbPass = '';

// DSN
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $dbUser, $dbPass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    error_log("Errore connessione DB: " . $e->getMessage());
    exit("Errore di connessione al database.");
}
```

---

## Variabili di configurazione

| Variabile | Descrizione       | Valore di default |
| --------- | ----------------- | ----------------- |
| `$dbHost` | Host del database | `127.0.0.1`       |
| `$dbName` | Nome del database | `cinespecs`       |
| `$dbUser` | Username MySQL    | `root`            |
| `$dbPass` | Password MySQL    | `''`              |

### Note

* Questi valori possono essere modificati in base all'ambiente (locale / VM / server)
* Per la consegna potrebbe essere necessario adattarli alla configurazione della VM del corso

---

## DSN (Data Source Name)

```php
$dsn = "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4";
```

### Spiegazione

* `mysql:` → driver PDO
* `host` → indirizzo del database
* `dbname` → database selezionato
* `charset=utf8mb4` → supporto completo Unicode (raccomandato)

---

## Creazione connessione PDO

```php
$pdo = new PDO($dsn, $dbUser, $dbPass, [...]);
```

### Opzioni utilizzate

#### 1. Error mode

```php
PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
```

* Lancia eccezioni in caso di errore
* Permette gestione centralizzata con `try/catch`

#### 2. Fetch mode

```php
PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
```

* I risultati delle query sono restituiti come array associativi
* Esempio:

```php
$row['username']
```

#### 3. Prepared statements reali

```php
PDO::ATTR_EMULATE_PREPARES => false
```

* Disabilita emulazione lato PHP
* Usa prepared statements reali del database
* Migliora sicurezza contro SQL injection

---

## Gestione errori

```php
catch (PDOException $e) {
    error_log("Errore connessione DB: " . $e->getMessage());
    exit("Errore di connessione al database.");
}
```

### Comportamento

* L’errore dettagliato viene scritto nei log del server (`error_log`)
* L’utente vede solo un messaggio generico
* L’esecuzione viene interrotta (`exit`)

### Motivazione

* Evitare di esporre informazioni sensibili (password, struttura DB)
* Garantire sicurezza dell'applicazione

---

## Utilizzo nel progetto

### Inclusione

Ogni file PHP che usa il database deve includere questo file:

```php
require_once __DIR__ . '/config.php';
```

### Uso della connessione

Esempio:

```php
$stmt = $pdo->prepare("SELECT * FROM UTENTI WHERE username = ?");
$stmt->execute([$username]);
$user = $stmt->fetch();
```

---

## Regole importanti

* Non creare più connessioni PDO nello stesso file
* Usare sempre `$pdo` fornito da questo file
* Non modificare `$pdo` dopo la creazione
* Non usare `mysqli` o altre API

---

## Sicurezza

Questo file contribuisce alla sicurezza del progetto:

* Usa prepared statements reali
* Non espone errori sensibili all’utente
* Centralizza la connessione al database

---

## Decisioni progettuali

| Scelta            | Motivazione               |
| ----------------- | ------------------------- |
| PDO               | API moderna e sicura      |
| utf8mb4           | supporto Unicode completo |
| Exception mode    | gestione errori chiara    |
| Fetch associativo | codice più leggibile      |
| No classi         | semplicità e chiarezza    |

---

## Possibili miglioramenti futuri

(non necessari per il progetto)

* uso di variabili d’ambiente
* separazione config per ambienti diversi
* connection pooling

---

## Riassunto

Il file `config.php`:

* crea una connessione PDO sicura
* gestisce errori in modo controllato
* fornisce `$pdo` al resto dell'applicazione
* mantiene il codice semplice e coerente con un progetto universitario

---
