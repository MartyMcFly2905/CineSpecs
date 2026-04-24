<?php
session_start();

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    header('Location: login.php');
    exit;
}

require __DIR__ . '/php/config.php';

$userId = (int) $_SESSION['id_utente'];
$sessionUsername = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$currentYear = (int) date('Y');

$feedbackType = '';
$feedbackMessage = '';
$activeForm = '';

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

function normalizzaTimestamp(string $value): string
{
    $value = trim($value);

    if ($value === '') {
        return '';
    }

    if (!preg_match('/^(?:[01]\d|2[0-3]):[0-5]\d(?::[0-5]\d)?$/', $value)) {
        return '';
    }

    if (strlen($value) === 5) {
        return $value . ':00';
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

    $absoluteTargetDir = __DIR__ . '/' . $targetDir;

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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $errors = [];
    $activeForm = $action;

    try {
        if ($action === 'create_film') {
            $filmData['titolo'] = pulisciTesto($_POST['titolo'] ?? '');
            $filmData['anno_uscita'] = pulisciTesto($_POST['anno_uscita'] ?? '');
            $filmData['regista'] = pulisciTesto($_POST['regista'] ?? '');
            $filmData['sinossi'] = pulisciTesto($_POST['sinossi'] ?? '');

            if ($filmData['titolo'] === '') {
                $errors[] = 'Inserisci il titolo del film.';
            } elseif (mb_strlen($filmData['titolo']) > 150) {
                $errors[] = 'Il titolo del film e troppo lungo.';
            }

            if (!annoValido($filmData['anno_uscita'], $currentYear)) {
                $errors[] = 'Inserisci un anno di uscita valido.';
            }

            if ($filmData['regista'] === '') {
                $errors[] = 'Inserisci il regista del film.';
            } elseif (mb_strlen($filmData['regista']) > 100) {
                $errors[] = 'Il nome del regista e troppo lungo.';
            }

            if ($filmData['sinossi'] !== '' && mb_strlen($filmData['sinossi']) > 4000) {
                $errors[] = 'La sinossi e troppo lunga.';
            }

            if (!$errors) {
                $duplicateStmt = $pdo->prepare(
                    'SELECT id_film
                     FROM FILM
                     WHERE LOWER(titolo) = LOWER(?)
                       AND anno_uscita = ?
                       AND LOWER(regista) = LOWER(?)
                     LIMIT 1'
                );
                $duplicateStmt->execute([
                    $filmData['titolo'],
                    (int) $filmData['anno_uscita'],
                    $filmData['regista'],
                ]);

                if ($duplicateStmt->fetch()) {
                    $errors[] = 'Questo film e gia presente nel catalogo.';
                }
            }

            $coverPath = null;

            if (!$errors) {
                $coverPath = caricaImmagine('copertina', 'assets/covers', 'cover', false, $errors);
            }

            if (!$errors) {
                $insertFilm = $pdo->prepare(
                    'INSERT INTO FILM (id_utente_creatore, titolo, anno_uscita, regista, sinossi, copertina_path)
                     VALUES (?, ?, ?, ?, ?, ?)'
                );
                $insertFilm->execute([
                    $userId,
                    $filmData['titolo'],
                    (int) $filmData['anno_uscita'],
                    $filmData['regista'],
                    $filmData['sinossi'] !== '' ? $filmData['sinossi'] : null,
                    $coverPath,
                ]);

                header('Location: dashboard.php?success=film&tab=create_film');
                exit;
            }
        } elseif ($action === 'create_frame') {
            $frameData['id_film'] = pulisciTesto($_POST['id_film'] ?? '');
            $frameData['timestamp_frame'] = pulisciTesto($_POST['timestamp_frame'] ?? '');
            $frameData['descrizione_scena'] = pulisciTesto($_POST['descrizione_scena'] ?? '');

            $filmId = filter_var($frameData['id_film'], FILTER_VALIDATE_INT, [
                'options' => ['min_range' => 1],
            ]);

            if ($filmId === false) {
                $errors[] = 'Seleziona un film valido a cui associare il frame.';
            }

            $timestampNormalizzato = normalizzaTimestamp($frameData['timestamp_frame']);

            if ($timestampNormalizzato === '') {
                $errors[] = 'Inserisci un timestamp valido nel formato HH:MM o HH:MM:SS.';
            }

            if ($frameData['descrizione_scena'] !== '' && mb_strlen($frameData['descrizione_scena']) > 255) {
                $errors[] = 'La descrizione del frame supera i 255 caratteri.';
            }

            if (!$errors) {
                $filmStmt = $pdo->prepare('SELECT id_film, titolo FROM FILM WHERE id_film = ? LIMIT 1');
                $filmStmt->execute([$filmId]);
                $selectedFilm = $filmStmt->fetch();

                if (!$selectedFilm) {
                    $errors[] = 'Il film selezionato non esiste piu nel database.';
                }
            }

            if (!$errors) {
                $duplicateFrameStmt = $pdo->prepare(
                    'SELECT id_frame
                     FROM FRAME
                     WHERE id_film = ?
                       AND timestamp_frame = ?
                     LIMIT 1'
                );
                $duplicateFrameStmt->execute([$filmId, $timestampNormalizzato]);

                if ($duplicateFrameStmt->fetch()) {
                    $errors[] = 'Esiste gia un frame per questo film con lo stesso timestamp.';
                }
            }

            $framePath = null;

            if (!$errors) {
                $framePath = caricaImmagine('immagine_frame', 'assets/frames', 'frame', true, $errors);
            }

            if (!$errors) {
                $insertFrame = $pdo->prepare(
                    'INSERT INTO FRAME (id_film, id_utente_creatore, immagine_path, timestamp_frame, descrizione_scena)
                     VALUES (?, ?, ?, ?, ?)'
                );
                $insertFrame->execute([
                    $filmId,
                    $userId,
                    $framePath,
                    $timestampNormalizzato,
                    $frameData['descrizione_scena'] !== '' ? $frameData['descrizione_scena'] : null,
                ]);

                header('Location: dashboard.php?success=frame&tab=create_frame');
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
    } elseif ($success === 'frame') {
        $feedbackType = 'success';
        $feedbackMessage = 'Frame caricato correttamente e associato al film selezionato.';
    }
}

$allowedTabs = [
    'create_film',
    'create_frame',
];

$requestedTab = $_GET['tab'] ?? '';
$activePanel = in_array($activeForm, $allowedTabs, true) ? $activeForm : $requestedTab;

if (!in_array($activePanel, $allowedTabs, true)) {
    $activePanel = 'create_film';
}

$filmsForSelect = [];
$stats = [
    'film' => 0,
    'frame' => 0,
];

try {
    $filmListStmt = $pdo->query(
        'SELECT id_film, titolo, anno_uscita, regista
         FROM FILM
         ORDER BY titolo ASC, anno_uscita DESC'
    );
    $filmsForSelect = $filmListStmt->fetchAll();

    $statsQueries = [
        'film' => 'SELECT COUNT(*) FROM FILM WHERE id_utente_creatore = ?',
        'frame' => 'SELECT COUNT(*) FROM FRAME WHERE id_utente_creatore = ?',
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
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard contributor · CineSpecs</title>
    <script src="js/theme-init.js?v=<?php echo filemtime(__DIR__ . '/js/theme-init.js'); ?>"></script>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
    <link rel="stylesheet" href="css/layout.css?v=<?php echo filemtime(__DIR__ . '/css/layout.css'); ?>">
</head>
<body>
<header class="site-header">
    <div class="container header-inner">
        <a href="index.php" class="brand">
            <img
                src="assets/icons/logo.png"
                alt="CineSpecs"
                class="brand-logo"
                id="brand-logo"
                data-light-logo="assets/icons/logo_dark.png"
                data-dark-logo="assets/icons/logo.png"
            >
            <span class="visually-hidden">CineSpecs</span>
        </a>
        <div class="header-actions">
            <p class="header-meta">Area contributor</p>
            <div class="header-auth">
                <span class="header-user">Ciao, <?php echo $sessionUsername; ?></span>
                <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                    <a href="admin.php">Admin</a>
                <?php endif; ?>
                <a href="logout.php">Logout</a>
            </div>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Tema scuro</button>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <section class="search-panel dashboard-hero" aria-labelledby="dashboard-title">
            <div class="grid-heading">
                <h1 id="dashboard-title">Dashboard contributor</h1>
            </div>
            <p class="dashboard-note">I tag sui frame verranno gestiti in una fase successiva. Qui puoi preparare il catalogo con i contenuti base.</p>
        </section>

        <section class="dashboard-stats" aria-label="Statistiche contributi">
            <article class="dashboard-stat">
                <span class="dashboard-stat__label">Film inseriti</span>
                <strong class="dashboard-stat__value"><?php echo $stats['film']; ?></strong>
            </article>
            <article class="dashboard-stat">
                <span class="dashboard-stat__label">Frame caricati</span>
                <strong class="dashboard-stat__value"><?php echo $stats['frame']; ?></strong>
            </article>
        </section>

        <?php if ($feedbackMessage !== ''): ?>
            <p class="dashboard-feedback dashboard-feedback--<?php echo htmlspecialchars($feedbackType, ENT_QUOTES, 'UTF-8'); ?>" aria-live="polite">
                <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <section class="dashboard-switcher" aria-label="Scegli contenuto da inserire">
            <button
                type="button"
                class="dashboard-switcher__button<?php echo $activePanel === 'create_film' ? ' dashboard-switcher__button--active' : ''; ?>"
                data-dashboard-target="create_film"
                aria-pressed="<?php echo $activePanel === 'create_film' ? 'true' : 'false'; ?>"
            >
                Nuovo film
            </button>
            <button
                type="button"
                class="dashboard-switcher__button<?php echo $activePanel === 'create_frame' ? ' dashboard-switcher__button--active' : ''; ?>"
                data-dashboard-target="create_frame"
                aria-pressed="<?php echo $activePanel === 'create_frame' ? 'true' : 'false'; ?>"
            >
                Nuovo frame
            </button>
        </section>

        <section class="dashboard-panels" aria-label="Form contributor" data-dashboard-active="<?php echo htmlspecialchars($activePanel, ENT_QUOTES, 'UTF-8'); ?>">
            <section
                class="search-panel contributor-card dashboard-panel<?php echo $activePanel === 'create_film' ? ' dashboard-panel--active' : ''; ?>"
                aria-labelledby="film-form-title"
                data-dashboard-panel="create_film"
                <?php echo $activePanel === 'create_film' ? '' : 'hidden'; ?>
            >
                <div class="grid-heading contributor-card__heading">
                    <h2 id="film-form-title">Nuovo film</h2>
                    <p>Controllo duplicati su titolo, anno e regista</p>
                </div>
                <form
                    id="film-form"
                    class="contributor-form"
                    method="post"
                    action="dashboard.php"
                    enctype="multipart/form-data"
                    novalidate
                >
                    <input type="hidden" name="action" value="create_film">

                    <div class="field-group">
                        <label for="film-title">Titolo</label>
                        <input type="text" name="titolo" id="film-title" maxlength="150" required value="<?php echo htmlspecialchars($filmData['titolo'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="field-group">
                        <label for="film-year">Anno di uscita</label>
                        <input type="number" name="anno_uscita" id="film-year" min="1888" max="<?php echo $currentYear; ?>" required value="<?php echo htmlspecialchars($filmData['anno_uscita'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="field-group field-group--full">
                        <label for="film-director">Regista</label>
                        <input type="text" name="regista" id="film-director" maxlength="100" required value="<?php echo htmlspecialchars($filmData['regista'], ENT_QUOTES, 'UTF-8'); ?>">
                    </div>

                    <div class="field-group field-group--full">
                        <label for="film-cover">Copertina</label>
                        <input type="file" name="copertina" id="film-cover" accept=".jpg,.jpeg,.png,.webp">
                    </div>

                    <div class="field-group field-group--full">
                        <label for="film-synopsis">Sinossi</label>
                        <textarea name="sinossi" id="film-synopsis" rows="5"><?php echo htmlspecialchars($filmData['sinossi'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="field-group field-group--full contributor-actions">
                        <button type="submit">Salva film</button>
                    </div>
                </form>
            </section>

            <section
                class="search-panel contributor-card contributor-card--wide dashboard-panel<?php echo $activePanel === 'create_frame' ? ' dashboard-panel--active' : ''; ?>"
                aria-labelledby="frame-form-title"
                data-dashboard-panel="create_frame"
                <?php echo $activePanel === 'create_frame' ? '' : 'hidden'; ?>
            >
                <div class="grid-heading contributor-card__heading">
                    <h2 id="frame-form-title">Nuovo frame</h2>
                    <p>Associato a un film gia presente nel database</p>
                </div>

                <?php if (!$filmsForSelect): ?>
                    <div class="empty-state">Prima inserisci almeno un film, poi potrai caricare un frame associato.</div>
                <?php else: ?>
                    <form
                        id="frame-form"
                        class="contributor-form"
                        method="post"
                        action="dashboard.php"
                        enctype="multipart/form-data"
                        novalidate
                    >
                        <input type="hidden" name="action" value="create_frame">

                        <div class="field-group field-group--full">
                            <label for="frame-film">Film</label>
                            <select name="id_film" id="frame-film" required>
                                <option value="">Seleziona un film esistente</option>
                                <?php foreach ($filmsForSelect as $film): ?>
                                    <option value="<?php echo (int) $film['id_film']; ?>" <?php echo ((int) $frameData['id_film'] === (int) $film['id_film']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>
                                        (<?php echo (int) $film['anno_uscita']; ?>)
                                        · <?php echo htmlspecialchars($film['regista'], ENT_QUOTES, 'UTF-8'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="field-group">
                            <label for="frame-timestamp">Timestamp</label>
                            <input type="time" name="timestamp_frame" id="frame-timestamp" step="1" required value="<?php echo htmlspecialchars($frameData['timestamp_frame'], ENT_QUOTES, 'UTF-8'); ?>">
                        </div>

                        <div class="field-group">
                            <label for="frame-image">Immagine frame</label>
                            <input type="file" name="immagine_frame" id="frame-image" accept=".jpg,.jpeg,.png,.webp" required>
                        </div>

                        <div class="field-group field-group--full">
                            <label for="frame-description">Descrizione scena</label>
                            <textarea name="descrizione_scena" id="frame-description" rows="4" maxlength="255"><?php echo htmlspecialchars($frameData['descrizione_scena'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="field-group field-group--full contributor-actions">
                            <button type="submit">Salva frame</button>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </section>
    </div>
</main>

<script src="js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/js/theme-toggle.js'); ?>"></script>
<script src="js/validation.js?v=<?php echo filemtime(__DIR__ . '/js/validation.js'); ?>"></script>
</body>
</html>
