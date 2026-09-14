<?php
// controller dashboard contributor (gestione film e frame)

session_start();

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/config.php';

$userId = (int) $_SESSION['id_utente'];
$isAdmin = $_SESSION['ruolo'] === 'admin';
$sessionUsername = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$currentYear = (int) date('Y');

$feedbackType = '';
$feedbackMessage = '';
$activeForm = '';
$editFilmId = 0;
$editFrameId = 0;

$filmData = [
    'titolo' => '',
    'anno_uscita' => '',
    'regista' => '',
    'sinossi' => '',
];

$frameData = [
    'id_film' => '',
    'timestamp_frame' => '',
    'descrizione_scena' => '',
];

// funzioni di utilità e validazione

function pulisciTesto(?string $value): string
{
    return trim((string) $value);
}

function annoValido(string $value, int $currentYear): bool
{
    if ($value === '' || !preg_match('/^[0-9]{4}$/', $value)) {
        return false;
    }

    $year = (int) $value;

    return $year >= 1888 && $year <= $currentYear;
}

function validaDatiFilm(array $dati, int $currentYear): array
{
    $errors = [];

    if (($dati['titolo'] ?? '') === '') {
        $errors[] = 'Inserisci il titolo del film.';
    } elseif (mb_strlen($dati['titolo']) > 150) {
        $errors[] = 'Il titolo del film e troppo lungo.';
    }

    if (!annoValido((string) ($dati['anno_uscita'] ?? ''), $currentYear)) {
        $errors[] = 'Inserisci un anno di uscita valido.';
    }

    if (($dati['regista'] ?? '') === '') {
        $errors[] = 'Inserisci il regista del film.';
    } elseif (mb_strlen($dati['regista']) > 100) {
        $errors[] = 'Il nome del regista e troppo lungo.';
    }

    if (($dati['sinossi'] ?? '') !== '' && mb_strlen($dati['sinossi']) > 200) {
        $errors[] = 'La sinossi e troppo lunga (massimo 200 caratteri).';
    }

    return $errors;
}

function validaDatiFrame(array $dati): array
{
    $errors = [];

    if (filter_var($dati['id_film'] ?? null, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) === false) {
        $errors[] = 'Seleziona un film valido a cui associare il frame.';
    }

    if (normalizzaTimestamp((string) ($dati['timestamp_frame'] ?? '')) === '') {
        $errors[] = 'Inserisci un timestamp valido nel formato HH:MM o HH:MM:SS.';
    }

    if (($dati['descrizione_scena'] ?? '') !== '' && mb_strlen($dati['descrizione_scena']) > 255) {
        $errors[] = 'La descrizione del frame supera i 255 caratteri.';
    }

    return $errors;
}

function normalizzaTimestamp(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d:[0-5]\d$/', $value)) {
        return '';
    }

    return $value;
}

function caricaImmagine(string $fieldName, string $targetDir, string $prefix, bool $required, array &$errors): ?string
{
    if (!isset($_FILES[$fieldName]) || $_FILES[$fieldName]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            $errors[] = 'Seleziona un file immagine valido.';
        }

        return null;
    }

    $file = $_FILES[$fieldName];

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors[] = 'Caricamento immagine non riuscito.';
        return null;
    }

    if ($file['size'] > 5 * 1024 * 1024) {
        $errors[] = 'L\'immagine supera il limite di 5 MB.';
        return null;
    }

    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    $allowedTypes = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    if (!isset($allowedTypes[$mimeType])) {
        $errors[] = 'Formato immagine non supportato. Usa JPG, PNG o WEBP.';
        return null;
    }

    $absoluteTargetDir = __DIR__ . '/../' . $targetDir;

    if (!is_dir($absoluteTargetDir) && !mkdir($absoluteTargetDir, 0775, true)) {
        $errors[] = 'Impossibile preparare la cartella di upload.';
        return null;
    }

    $filename = $prefix . '-' . date('YmdHis') . '-' . bin2hex(random_bytes(4)) . '.' . $allowedTypes[$mimeType];
    $absolutePath = $absoluteTargetDir . '/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $absolutePath)) {
        $errors[] = 'Impossibile salvare l\'immagine caricata.';
        return null;
    }

    return $targetDir . '/' . $filename;
}

function formDaAzione(string $action): string
{
    if ($action === 'create_frame' || $action === 'update_frame') {
        return 'create_frame';
    }

    if ($action === 'create_film' || $action === 'update_film') {
        return 'create_film';
    }

    return '';
}

function trovaFilmDuplicato(PDO $pdo, string $titolo, int $annoUscita, string $regista, ?int $excludeFilmId = null): bool
{
    if ($excludeFilmId === null) {
        $stmt = $pdo->prepare(
            'SELECT id_film
             FROM film
             WHERE LOWER(titolo) = LOWER(?)
               AND anno_uscita = ?
               AND LOWER(regista) = LOWER(?)
             LIMIT 1'
        );
        $stmt->execute([$titolo, $annoUscita, $regista]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT id_film
             FROM film
             WHERE LOWER(titolo) = LOWER(?)
               AND anno_uscita = ?
               AND LOWER(regista) = LOWER(?)
               AND id_film <> ?
             LIMIT 1'
        );
        $stmt->execute([$titolo, $annoUscita, $regista, $excludeFilmId]);
    }

    return (bool) $stmt->fetch();
}

function trovaFilmPerModifica(PDO $pdo, int $filmId, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT copertina_path
         FROM film
         WHERE id_film = ? AND id_utente_creatore = ?
         LIMIT 1'
    );
    $stmt->execute([$filmId, $userId]);

    $film = $stmt->fetch();

    return $film ?: null;
}

function trovaFilmPerId(PDO $pdo, int $filmId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT id_film, titolo
         FROM film
         WHERE id_film = ?
         LIMIT 1'
    );
    $stmt->execute([$filmId]);

    $film = $stmt->fetch();

    return $film ?: null;
}

function inserisciFilm(PDO $pdo, int $userId, array $filmData, ?string $coverPath): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO film (id_utente_creatore, titolo, anno_uscita, regista, sinossi, copertina_path)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $userId,
        $filmData['titolo'],
        (int) $filmData['anno_uscita'],
        $filmData['regista'],
        $filmData['sinossi'] !== '' ? $filmData['sinossi'] : null,
        $coverPath,
    ]);

    return (int) $pdo->lastInsertId();
}

function aggiornaFilm(PDO $pdo, int $filmId, int $userId, array $filmData, ?string $coverPath): void
{
    $stmt = $pdo->prepare(
        'UPDATE film
         SET titolo = ?, anno_uscita = ?, regista = ?, sinossi = ?, copertina_path = ?
         WHERE id_film = ? AND id_utente_creatore = ?'
    );
    $stmt->execute([
        $filmData['titolo'],
        (int) $filmData['anno_uscita'],
        $filmData['regista'],
        $filmData['sinossi'] !== '' ? $filmData['sinossi'] : null,
        $coverPath,
        $filmId,
        $userId,
    ]);
}

function trovaFrameDuplicato(PDO $pdo, int $filmId, string $timestampFrame, ?int $excludeFrameId = null): bool
{
    if ($excludeFrameId === null) {
        $stmt = $pdo->prepare(
            'SELECT id_frame
             FROM frame
             WHERE id_film = ?
               AND timestamp_frame = ?
             LIMIT 1'
        );
        $stmt->execute([$filmId, $timestampFrame]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT id_frame
             FROM frame
             WHERE id_film = ?
               AND timestamp_frame = ?
               AND id_frame <> ?
             LIMIT 1'
        );
        $stmt->execute([$filmId, $timestampFrame, $excludeFrameId]);
    }

    return (bool) $stmt->fetch();
}

function trovaFramePerModifica(PDO $pdo, int $frameId, int $userId): ?array
{
    $stmt = $pdo->prepare(
        'SELECT immagine_path
         FROM frame
         WHERE id_frame = ? AND id_utente_creatore = ?
         LIMIT 1'
    );
    $stmt->execute([$frameId, $userId]);

    $frame = $stmt->fetch();

    return $frame ?: null;
}

function inserisciFrame(PDO $pdo, int $filmId, int $userId, string $framePath, string $timestampFrame, ?string $descrizioneScena): int
{
    $stmt = $pdo->prepare(
        'INSERT INTO frame (id_film, id_utente_creatore, immagine_path, timestamp_frame, descrizione_scena)
         VALUES (?, ?, ?, ?, ?)'
    );
    $stmt->execute([
        $filmId,
        $userId,
        $framePath,
        $timestampFrame,
        $descrizioneScena !== '' ? $descrizioneScena : null,
    ]);

    return (int) $pdo->lastInsertId();
}

function aggiornaFrame(PDO $pdo, int $frameId, int $userId, int $filmId, string $framePath, string $timestampFrame, ?string $descrizioneScena): void
{
    $stmt = $pdo->prepare(
        'UPDATE frame
         SET id_film = ?, immagine_path = ?, timestamp_frame = ?, descrizione_scena = ?
         WHERE id_frame = ? AND id_utente_creatore = ?'
    );
    $stmt->execute([
        $filmId,
        $framePath,
        $timestampFrame,
        $descrizioneScena !== '' ? $descrizioneScena : null,
        $frameId,
        $userId,
    ]);
}

// gestione form in post (creazione / modifica)

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $errors = [];
    $activeForm = formDaAzione($action);

    try {
        if ($action === 'create_film') {
            $filmData['titolo'] = pulisciTesto($_POST['titolo'] ?? '');
            $filmData['anno_uscita'] = pulisciTesto($_POST['anno_uscita'] ?? '');
            $filmData['regista'] = pulisciTesto($_POST['regista'] ?? '');
            $filmData['sinossi'] = pulisciTesto($_POST['sinossi'] ?? '');
            $errors = array_merge($errors, validaDatiFilm($filmData, $currentYear));

            if (!$errors) {
                if (trovaFilmDuplicato($pdo, $filmData['titolo'], (int) $filmData['anno_uscita'], $filmData['regista'])) {
                    $errors[] = 'Questo film e gia presente nel catalogo.';
                }
            }

            $coverPath = null;

            if (!$errors) {
                $coverPath = caricaImmagine('copertina', 'assets/covers', 'cover', false, $errors);
            }

            if (!$errors) {
                inserisciFilm($pdo, $userId, $filmData, $coverPath);

                header('Location: dashboard.php?success=film&tab=create_film');
                exit;
            }
        } elseif ($action === 'update_film') {
            $editFilmId = filter_input(INPUT_POST, 'id_film', FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            $filmData['titolo'] = pulisciTesto($_POST['titolo'] ?? '');
            $filmData['anno_uscita'] = pulisciTesto($_POST['anno_uscita'] ?? '');
            $filmData['regista'] = pulisciTesto($_POST['regista'] ?? '');
            $filmData['sinossi'] = pulisciTesto($_POST['sinossi'] ?? '');
            if (!$editFilmId) {
                $errors[] = 'Film da modificare non valido.';
            }

            $errors = array_merge($errors, validaDatiFilm($filmData, $currentYear));

            $currentCoverPath = null;

            if (!$errors) {
                $filmRow = trovaFilmPerModifica($pdo, $editFilmId, $userId);

                if (!$filmRow) {
                    $errors[] = 'Film non trovato o non autorizzato.';
                } else {
                    $currentCoverPath = $filmRow['copertina_path'];
                }
            }

            if (!$errors) {
                if (trovaFilmDuplicato($pdo, $filmData['titolo'], (int) $filmData['anno_uscita'], $filmData['regista'], $editFilmId)) {
                    $errors[] = 'Questo film e gia presente nel catalogo.';
                }
            }

            $coverPath = $currentCoverPath;

            if (!$errors) {
                $newCoverPath = caricaImmagine('copertina', 'assets/covers', 'cover', false, $errors);

                if ($newCoverPath !== null) {
                    $coverPath = $newCoverPath;
                }
            }

            if (!$errors) {
                aggiornaFilm($pdo, $editFilmId, $userId, $filmData, $coverPath);

                header('Location: dashboard.php?success=film_updated&tab=create_film');
                exit;
            }
        } elseif ($action === 'create_frame') {
            $frameData['id_film'] = pulisciTesto($_POST['id_film'] ?? '');
            $frameData['timestamp_frame'] = pulisciTesto($_POST['timestamp_frame'] ?? '');
            $frameData['descrizione_scena'] = pulisciTesto($_POST['descrizione_scena'] ?? '');

            $errors = array_merge($errors, validaDatiFrame($frameData));

            $filmId = filter_var($frameData['id_film'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            $timestampNormalizzato = normalizzaTimestamp($frameData['timestamp_frame']);

            if (!$errors) {
                $selectedFilm = trovaFilmPerId($pdo, (int) $filmId);

                if (!$selectedFilm) {
                    $errors[] = 'Il film selezionato non esiste piu nel database.';
                }
            }

            if (!$errors) {
                if (trovaFrameDuplicato($pdo, (int) $filmId, $timestampNormalizzato)) {
                    $errors[] = 'Esiste gia un frame per questo film con lo stesso timestamp.';
                }
            }

            $framePath = null;

            if (!$errors) {
                $framePath = caricaImmagine('immagine_frame', 'assets/frames', 'frame', true, $errors);
            }

            if (!$errors) {
                inserisciFrame($pdo, (int) $filmId, $userId, $framePath, $timestampNormalizzato, $frameData['descrizione_scena']);

                header('Location: dashboard.php?success=frame&tab=create_frame');
                exit;
            }
        } elseif ($action === 'update_frame') {
            $editFrameId = filter_input(INPUT_POST, 'id_frame', FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);
            $frameData['id_film'] = pulisciTesto($_POST['id_film'] ?? '');
            $frameData['timestamp_frame'] = pulisciTesto($_POST['timestamp_frame'] ?? '');
            $frameData['descrizione_scena'] = pulisciTesto($_POST['descrizione_scena'] ?? '');

            if (!$editFrameId) {
                $errors[] = 'Frame da modificare non valido.';
            }

            $errors = array_merge($errors, validaDatiFrame($frameData));

            $filmId = filter_var($frameData['id_film'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            $timestampNormalizzato = normalizzaTimestamp($frameData['timestamp_frame']);

            $currentFramePath = null;

            if (!$errors) {
                $currentFrame = trovaFramePerModifica($pdo, $editFrameId, $userId);

                if (!$currentFrame) {
                    $errors[] = 'Frame non trovato o non autorizzato.';
                } else {
                    $currentFramePath = $currentFrame['immagine_path'];
                }
            }

            if (!$errors) {
                $selectedFilm = trovaFilmPerId($pdo, (int) $filmId);

                if (!$selectedFilm) {
                    $errors[] = 'Il film selezionato non esiste piu nel database.';
                }
            }

            if (!$errors) {
                if (trovaFrameDuplicato($pdo, (int) $filmId, $timestampNormalizzato, $editFrameId)) {
                    $errors[] = 'Esiste gia un frame per questo film con lo stesso timestamp.';
                }
            }

            $framePath = $currentFramePath;

            if (!$errors) {
                $newFramePath = caricaImmagine('immagine_frame', 'assets/frames', 'frame', false, $errors);

                if ($newFramePath !== null) {
                    $framePath = $newFramePath;
                }
            }

            if (!$errors) {
                aggiornaFrame($pdo, $editFrameId, $userId, (int) $filmId, $framePath, $timestampNormalizzato, $frameData['descrizione_scena']);

                header('Location: dashboard.php?success=frame_updated&tab=create_frame');
                exit;
            }
        } else {
            $errors[] = 'Azione non valida.';
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $errors[] = 'Impossibile completare l\'operazione. Riprova piu tardi.';
    } catch (Throwable $e) {
        error_log($e->getMessage());
        $errors[] = 'Errore inatteso durante il salvataggio.';
    }

    if ($errors) {
        $feedbackType = 'error';
        $feedbackMessage = implode(' ', $errors);
    }
}

$success = $_GET['success'] ?? '';

if ($feedbackMessage === '' && $success !== '') {
    if ($success === 'film') {
        $feedbackType = 'success';
        $feedbackMessage = 'Film salvato correttamente nel catalogo.';
    } elseif ($success === 'film_updated') {
        $feedbackType = 'success';
        $feedbackMessage = 'Film aggiornato correttamente.';
    } elseif ($success === 'frame') {
        $feedbackType = 'success';
        $feedbackMessage = 'Frame caricato correttamente e associato al film selezionato.';
    } elseif ($success === 'frame_updated') {
        $feedbackType = 'success';
        $feedbackMessage = 'Frame aggiornato correttamente.';
    }
}

$allowedTabs = [
    'create_film',
    'create_frame',
];

$requestedEditFilm = null;
$requestedEditFrame = null;

// se in get ci sono id da modificare carico i dati per i form

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $requestedEditFilm = filter_input(INPUT_GET, 'edit_film', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);
    $requestedEditFrame = filter_input(INPUT_GET, 'edit_frame', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    try {
        if ($requestedEditFilm) {
            $filmStmt = $pdo->prepare(
                'SELECT titolo, anno_uscita, regista, sinossi
                 FROM film
                 WHERE id_film = ? AND id_utente_creatore = ?
                 LIMIT 1'
            );
            $filmStmt->execute([$requestedEditFilm, $userId]);
            $filmToEdit = $filmStmt->fetch();

            if ($filmToEdit) {
                $editFilmId = (int) $requestedEditFilm;
                $filmData = [
                    'titolo' => $filmToEdit['titolo'],
                    'anno_uscita' => (string) $filmToEdit['anno_uscita'],
                    'regista' => $filmToEdit['regista'],
                    'sinossi' => $filmToEdit['sinossi'] ?? '',
                ];
                $activeForm = 'create_film';
            } elseif ($feedbackMessage === '') {
                $feedbackType = 'error';
                $feedbackMessage = 'Film non trovato o non autorizzato.';
            }
        } elseif ($requestedEditFrame) {
            $frameStmt = $pdo->prepare(
                'SELECT id_film, timestamp_frame, descrizione_scena
                 FROM frame
                 WHERE id_frame = ? AND id_utente_creatore = ?
                 LIMIT 1'
            );
            $frameStmt->execute([$requestedEditFrame, $userId]);
            $frameToEdit = $frameStmt->fetch();

            if ($frameToEdit) {
                $editFrameId = (int) $requestedEditFrame;
                $frameData = [
                    'id_film' => (string) $frameToEdit['id_film'],
                    'timestamp_frame' => $frameToEdit['timestamp_frame'],
                    'descrizione_scena' => $frameToEdit['descrizione_scena'] ?? '',
                ];
                $activeForm = 'create_frame';
            } elseif ($feedbackMessage === '') {
                $feedbackType = 'error';
                $feedbackMessage = 'Frame non trovato o non autorizzato.';
            }
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());

        if ($feedbackMessage === '') {
            $feedbackType = 'error';
            $feedbackMessage = 'Impossibile caricare il contenuto da modificare.';
        }
    }
}

$requestedTab = $_GET['tab'] ?? '';
$activePanel = in_array($activeForm, $allowedTabs, true) ? $activeForm : $requestedTab;

if (!in_array($activePanel, $allowedTabs, true)) {
    $activePanel = 'create_film';
}

$isEditingFilm = $editFilmId > 0;
$isEditingFrame = $editFrameId > 0;

$filmsForSelect = [];
$stats = [
    'film' => 0,
    'frame' => 0,
    'tag' => 0,
];

// dati per le select e statistiche personali

try {
    $filmListStmt = $pdo->query(
        'SELECT id_film, titolo, anno_uscita, regista
         FROM film
         ORDER BY titolo ASC, anno_uscita DESC'
    );
    $filmsForSelect = $filmListStmt->fetchAll();

    $statsQueries = [
        'film' => 'SELECT COUNT(*) FROM film WHERE id_utente_creatore = ?',
        'frame' => 'SELECT COUNT(*) FROM frame WHERE id_utente_creatore = ?',
        'tag' => 'SELECT COUNT(*) FROM tags WHERE id_utente = ?',
    ];

    foreach ($statsQueries as $key => $query) {
        $stmt = $pdo->prepare($query);
        $stmt->execute([$userId]);
        $stats[$key] = (int) $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());

    if ($feedbackMessage === '') {
        $feedbackType = 'error';
        $feedbackMessage = 'Impossibile caricare tutti i dati della dashboard.';
    }
}
require __DIR__ . '/dashboard_template.php';
