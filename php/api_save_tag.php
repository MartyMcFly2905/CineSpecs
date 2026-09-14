<?php
// salva un tag su un frame (creando l'hardware se serve)
require __DIR__ . '/config.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

function post_value($key)
{
    $value = filter_input(INPUT_POST, $key, FILTER_DEFAULT);

    if ($value === null) {
        return null;
    }

    return trim($value);
}

// valida l'anno (opzionale o intero valido)
function valid_year($value)
{
    if ($value === '') {
        return true;
    }

    if (!preg_match('/^-?[0-9]{1,5}$/', $value)) {
        return false;
    }

    $year = (int) $value;

    return $year >= -32768 && $year <= 32767;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Metodo non consentito. Usa una richiesta POST.', 405);
}

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    send_json(false, 'Devi effettuare il login per aggiungere un tag.', 401);
}

$idFrame      = filter_input(INPUT_POST, 'id_frame', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);
$idHardwareRaw = post_value('id_hardware');
$idHardware    = null;
$coordX        = filter_input(INPUT_POST, 'coord_x', FILTER_VALIDATE_FLOAT);
$coordY        = filter_input(INPUT_POST, 'coord_y', FILTER_VALIDATE_FLOAT);
$nomeModello   = post_value('nome_modello');
$produttore    = post_value('produttore');
$annoRilascio  = post_value('anno_rilascio');
$descrizione   = post_value('descrizione');
$curiosita     = post_value('curiosita');
$propFittizio  = post_value('prop_fittizio') === '1' ? 1 : 0;
$idUtente      = (int) $_SESSION['id_utente'];

if (!$idFrame) {
    send_json(false, 'Frame non valido.', 400);
}

if ($coordX === false || $coordY === false || $coordX < 0 || $coordX > 100 || $coordY < 0 || $coordY > 100) {
    send_json(false, 'Coordinate non valide.', 400);
}

if ($idHardwareRaw !== null && $idHardwareRaw !== '') {
    $idHardware = filter_var($idHardwareRaw, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($idHardware === false) {
        send_json(false, 'Hardware selezionato non valido.', 400);
    }
}

$usesExistingHardware = $idHardware !== null;

if (!$usesExistingHardware) {
    if ($nomeModello === null || $nomeModello === '') {
        send_json(false, 'Nome modello obbligatorio.', 400);
    }

    if ($produttore === null || $produttore === '') {
        send_json(false, 'Produttore obbligatorio.', 400);
    }
}

if ($nomeModello !== null && strlen($nomeModello) > 120) {
    send_json(false, 'Nome modello troppo lungo.', 400);
}

if ($produttore !== null && strlen($produttore) > 120) {
    send_json(false, 'Produttore troppo lungo.', 400);
}

if ($annoRilascio === null) {
    $annoRilascio = '';
}

if (!valid_year($annoRilascio)) {
    send_json(false, 'Anno di produzione non valido.', 400);
}

$annoDb      = $annoRilascio !== '' ? (int) $annoRilascio : null;
$descrizione = $descrizione !== null && $descrizione !== '' ? $descrizione : null;
$curiosita   = $curiosita   !== null && $curiosita   !== '' ? $curiosita   : null;
$coordX      = round((float) $coordX, 2);
$coordY      = round((float) $coordY, 2);

try {
    // controllo che il frame esista
    if (!db_query($pdo, 'SELECT id_frame FROM frame WHERE id_frame = ? LIMIT 1', [$idFrame])->fetch()) {
        send_json(false, 'Frame non trovato.', 404);
    }

    // transazione per hardware e tag
    $pdo->beginTransaction();

    if ($usesExistingHardware) {
        if (!db_query($pdo, 'SELECT id_hardware FROM hardware WHERE id_hardware = ? LIMIT 1', [$idHardware])->fetch()) {
            $pdo->rollBack();
            send_json(false, 'Hardware selezionato non trovato.', 404);
        }
    } else {
        // cerco se esiste già lo stesso modello
        $hardware = db_query($pdo,
            'SELECT id_hardware
             FROM hardware
             WHERE LOWER(nome_modello) = LOWER(?) AND LOWER(produttore) = LOWER(?)
             LIMIT 1',
            [$nomeModello, $produttore]
        )->fetch();

        if ($hardware) {
            // riuso l'hardware trovato
            $idHardware = (int) $hardware['id_hardware'];
        } else {
            // inserisco il nuovo hardware
            db_query($pdo,
                'INSERT INTO hardware
                    (id_utente_creatore, nome_modello, produttore, anno_rilascio, descrizione, curiosita, prop_fittizio)
                 VALUES (?, ?, ?, ?, ?, ?, ?)',
                [$idUtente, $nomeModello, $produttore, $annoDb, $descrizione, $curiosita, $propFittizio]
            );
            $idHardware = (int) $pdo->lastInsertId();
        }
    }

    // controllo tag duplicato nel frame
    if (db_query($pdo,
        'SELECT id_tag FROM tags WHERE id_frame = ? AND id_hardware = ? LIMIT 1',
        [$idFrame, $idHardware]
    )->fetch()) {
        $pdo->rollBack();
        send_json(false, 'Questo hardware e gia taggato nel frame.', 409);
    }

    // inserisco il tag con le coordinate in percentuale
    db_query($pdo,
        'INSERT INTO tags (id_frame, id_hardware, id_utente, coord_x, coord_y)
         VALUES (?, ?, ?, ?, ?)',
        [$idFrame, $idHardware, $idUtente, $coordX, $coordY]
    );
    $idTag = (int) $pdo->lastInsertId();

    $pdo->commit();

    send_json(true, 'Tag salvato.', 201, [
        'id_tag'      => $idTag,
        'id_hardware' => (int) $idHardware,
    ]);
} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    if ($e->getCode() === '23000') {
        send_json(false, 'Questo hardware e gia taggato nel frame.', 409);
    }

    error_log($e->getMessage());
    send_json(false, 'Errore durante il salvataggio del tag.', 500);
}
