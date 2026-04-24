# Workflow di sviluppo – CineSpecs (Fedora + XAMPP)

Guida operativa per lavorare, testare e mantenere il progetto compatibile con l’ambiente del corso.
L'agente AI non deve seguirla, è una guida personale per trovare i comandi al volo.

---

# 🟢 1. Avvio sessione di lavoro

## Avvia XAMPP

```bash
sudo /opt/lampp/lampp start
```

## Verifica servizi

```bash
sudo /opt/lampp/lampp status
```

✔️ Devono essere attivi:

* Apache
* MySQL

---

## Apri progetto

```bash
cd /opt/lampp/htdocs/CineSpecs
```

Apri editor (Cursor):

```bash
cursor .
```

---

## Apri nel browser

```text
http://localhost/CineSpecs/
```

---

# 🟡 2. Workflow di sviluppo (per ogni sessione)

## Step 1 — Leggi contesto

* `AGENTS.md`
* `docs/` rilevanti
* `LOG.md`

---

## Step 2 — Definisci task

✔️ Un solo task per sessione
Esempi:

* login
* form registrazione
* query film

---

## Step 3 — Implementa

* modifica solo file necessari
* codice semplice
* niente over-engineering

---

## Step 4 — Test immediato (fondamentale)

Ogni volta che scrivi codice:

### ✔ Test PHP

Apri nel browser:

```text
http://localhost/CineSpecs/nomefile.php
```

---

### ✔ Test database (se coinvolto)

```bash
/opt/lampp/bin/mysql -u root
```

Poi:

```sql
USE cinespecs;
SELECT * FROM tabella;
```

---

### ✔ Test log errori PHP

```bash
tail -f /opt/lampp/logs/php_error_log
```

---

# 🔵 3. Test database

## Import database

```bash
/opt/lampp/bin/mysql -u root < setup_database.sql
```

---

## Accesso manuale DB

```bash
/opt/lampp/bin/mysql -u root
```

---

## Comandi utili

```sql
SHOW DATABASES;
USE cinespecs;
SHOW TABLES;

SELECT * FROM UTENTI;
SELECT * FROM FILM;
SELECT * FROM HARDWARE;
SELECT * FROM FRAME;
SELECT * FROM TAGS;
```

---

## Test vincoli

### Foreign key

```sql
INSERT INTO FRAME (id_film, immagine_path)
VALUES (999, 'test.jpg');
```

✔️ Deve fallire

---

### UNIQUE

```sql
INSERT INTO TAGS (id_frame, id_hardware, id_utente, coord_x, coord_y)
VALUES (1, 1, 1, 40, 40);
```

✔️ Deve fallire

---

### CHECK coordinate

```sql
INSERT INTO TAGS (id_frame, id_hardware, id_utente, coord_x, coord_y)
VALUES (1, 1, 1, 150, 50);
```

✔️ Deve fallire (se supportato)

---

# 🟣 4. Test PHP + PDO

## Test base connessione

File: `test_db.php`

```php
<?php
require_once __DIR__ . '/php/config.php';

echo "Connessione OK";
```

---

## Test query

```php
<?php
require_once __DIR__ . '/php/config.php';

$stmt = $pdo->query("SELECT COUNT(*) AS totale FROM FILM");
$row = $stmt->fetch();

echo $row['totale'];
```

---

## Test errore DB

Modifica temporaneamente:

```php
$dbName = 'errore';
```

✔️ Deve mostrare errore generico

---

# 🟠 5. Debug

## Log Apache

```bash
tail -f /opt/lampp/logs/error_log
```

## Log PHP

```bash
tail -f /opt/lampp/logs/php_error_log
```

---

# 🔴 6. Git workflow

## Prima di iniziare

```bash
git pull
```

## Dopo ogni sessione

```bash
git add .
git commit -m "feat: descrizione breve"
```

---

# ⚫ 7. Fine sessione

## Spegni XAMPP

```bash
sudo /opt/lampp/lampp stop
```

---

## Verifica

```bash
sudo /opt/lampp/lampp status
```

---

# ⚠️ 8. Regole fondamentali

* usare sempre XAMPP (compatibilità corso)
* NON usare percorsi assoluti
* usare solo path relativi
* testare sempre dopo ogni modifica
* validare sempre input lato server
* usare sempre prepared statements

---

# 🧠 9. Checklist veloce sessione

* [ ] XAMPP avviato
* [ ] progetto in htdocs
* [ ] task definito
* [ ] codice scritto
* [ ] test browser ok
* [ ] test DB ok
* [ ] log puliti
* [ ] commit fatto
* [ ] XAMPP spento

---

# 🚀 10. Regola d’oro

👉 **Se non è testato su XAMPP, non è fatto.**

---
