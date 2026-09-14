<?php
// viewer frame e tag del film

session_start();

require __DIR__ . '/config.php';

// id film da get
$filmId = filter_input(INPUT_GET, 'film', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

$film = null;
$frames = [];
$hardwareOptions = [];
$dbError = false;
$message = '';
$isLoggedIn = isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo']);
$sessionUsername = $isLoggedIn ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : '';

if (!$filmId) {
    $message = 'Parametro film non valido.';
} else {
    try {
        // dati film
        $filmStmt = $pdo->prepare(
            'SELECT
                f.id_film,
                f.titolo,
                f.anno_uscita,
                f.regista,
                f.creato_il,
                u.username AS autore_film
             FROM film f
             INNER JOIN utenti u ON f.id_utente_creatore = u.id_utente
             WHERE f.id_film = ?'
        );
        $filmStmt->execute([$filmId]);
        $film = $filmStmt->fetch();

        if ($film) {
            // frame del film e conteggio voti
            $frameStmt = $pdo->prepare(
                'SELECT
                    fr.id_frame,
                    fr.immagine_path,
                    fr.timestamp_frame,
                    fr.descrizione_scena,
                    fr.creato_il,
                    u.username AS autore_frame,
                    COALESCE(SUM(fv.upvote), 0) AS frame_upvotes,
                    COALESCE(SUM(fv.downvote), 0) AS frame_downvotes
                 FROM frame fr
                 INNER JOIN utenti u ON fr.id_utente_creatore = u.id_utente
                 LEFT JOIN frame_voti fv ON fv.id_frame = fr.id_frame
                 WHERE fr.id_film = ?
                 GROUP BY
                    fr.id_frame,
                    fr.immagine_path,
                    fr.timestamp_frame,
                    fr.descrizione_scena,
                    fr.creato_il,
                    u.username
                 ORDER BY fr.timestamp_frame IS NULL, fr.timestamp_frame ASC, fr.id_frame ASC'
            );
            $frameStmt->execute([$filmId]);
            $frames = $frameStmt->fetchAll();

            if ($isLoggedIn) {
                // lista hardware per la select dei tag
                $hardwareStmt = $pdo->query(
                    'SELECT id_hardware, nome_modello, produttore
                     FROM hardware
                     ORDER BY produttore ASC, nome_modello ASC'
                );
                $hardwareOptions = $hardwareStmt->fetchAll();
            }
        } else {
            $message = 'Film non trovato.';
        }
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $dbError = true;
        $message = 'Impossibile caricare il viewer. Riprova più tardi.';
    }
}

$mainFrame = $frames[0] ?? null;

if ($film && !$dbError && !$mainFrame) {
    $message = 'Questo film non ha ancora frame disponibili.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>
        <?php if ($film): ?>
            <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?> · CineSpecs
        <?php else: ?>
            Frame viewer · CineSpecs
        <?php endif; ?>
    </title>
    <script src="../js/theme-init.js?v=<?php echo filemtime(__DIR__ . '/../js/theme-init.js'); ?>"></script>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
    <link rel="stylesheet" href="../css/layout.css?v=<?php echo filemtime(__DIR__ . '/../css/layout.css'); ?>">
    <link rel="stylesheet" href="../css/components.css?v=<?php echo filemtime(__DIR__ . '/../css/components.css'); ?>">
</head>
<body class="viewer-page">
<header class="site-header">
    <div class="container header-inner">
        <a href="../index.php" class="brand">
            <img
                src="../assets/icons/logo.png"
                alt="CineSpecs"
                class="brand-logo"
                id="brand-logo"
                data-light-logo="../assets/icons/logo_dark.png"
                data-dark-logo="../assets/icons/logo.png"
            >
            <span class="visually-hidden">CineSpecs</span>
        </a>
        <div class="header-actions">
            <p class="header-meta">Frame viewer</p>
            <div class="header-auth">
                <?php if ($isLoggedIn): ?>
                    <span class="header-user">Ciao, <?php echo $sessionUsername; ?></span>
                    <nav class="header-auth-pill" aria-label="Azioni account">
                        <a href="dashboard.php">Dashboard</a>
                        <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                            <a href="admin.php">Admin</a>
                        <?php endif; ?>
                        <a href="logout.php">Logout</a>
                    </nav>
                <?php else: ?>
                    <details class="auth-menu">
                        <summary class="theme-toggle">Login</summary>
                        <div class="auth-menu-panel">
                            <a href="login.php">Accedi</a>
                            <a href="register.php">Registrati</a>
                        </div>
                    </details>
                <?php endif; ?>
            </div>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Tema scuro</button>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <p class="viewer-back-link">
            <a href="../index.php">Torna al catalogo</a>
        </p>

        <?php if ($message): ?>
            <div class="empty-state"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($film && $mainFrame): ?>
            <!-- header film e bottone per aggiungere tag -->
            <section class="viewer-header" aria-labelledby="viewer-title">
                <div>
                    <h1 id="viewer-title"><?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p>
                        <?php echo (int) $film['anno_uscita']; ?>
                        ·
                        <?php echo htmlspecialchars($film['regista'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <div class="viewer-actions">
                    <p><?php echo count($frames); ?> frame disponibili</p>
                    <?php if ($isLoggedIn): ?>
                        <button
                            type="button"
                            id="inspection-toggle"
                            class="inspection-toggle"
                            aria-pressed="false"
                        >
                            Aggiungi tag
                        </button>
                    <?php endif; ?>
                </div>
            </section>

            <section class="viewer-layout" aria-label="Viewer frame">
                <!-- fotogramma e layer tag -->
                <div class="viewer-main">
                    <figure class="frame-stage">
                        <div class="frame-tag-layer">
                            <img
                                id="frame-image"
                                src="<?php echo htmlspecialchars('../' . $mainFrame['immagine_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                alt="Frame di <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-frame-id="<?php echo (int) $mainFrame['id_frame']; ?>"
                            >
                            <div id="tag-layer"></div>
                        </div>
                        <figcaption>
                            <p id="frame-description" class="frame-description">
                                <?php echo !empty($mainFrame['descrizione_scena']) ? htmlspecialchars($mainFrame['descrizione_scena'], ENT_QUOTES, 'UTF-8') : ''; ?>
                            </p>
                            <div class="frame-meta-row">
                                <span id="frame-timestamp" class="frame-meta-pill">
                                    <?php echo $mainFrame['timestamp_frame'] ? htmlspecialchars($mainFrame['timestamp_frame'], ENT_QUOTES, 'UTF-8') : 'Timestamp non disponibile'; ?>
                                </span>
                                <span id="frame-author" class="frame-meta-pill frame-meta-pill--author">
                                    Aggiunto da: <?php echo htmlspecialchars($mainFrame['autore_frame'], ENT_QUOTES, 'UTF-8'); ?>
                                </span>
                                <div
                                    id="frame-votes"
                                    class="vote-controls"
                                    role="group"
                                    data-frame-id="<?php echo (int) $mainFrame['id_frame']; ?>"
                                    data-frame-upvotes="<?php echo (int) $mainFrame['frame_upvotes']; ?>"
                                    data-frame-downvotes="<?php echo (int) $mainFrame['frame_downvotes']; ?>"
                                    data-vote-target="frame"
                                    data-vote-id="<?php echo (int) $mainFrame['id_frame']; ?>"
                                    data-can-vote="<?php echo $isLoggedIn ? '1' : '0'; ?>"
                                    aria-label="Voti del frame"
                                >
                                    <button type="button" class="vote-button vote-button--up" data-vote="up" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>
                                        <span aria-hidden="true">▲</span>
                                        <span class="vote-button__count" data-vote-count="up"><?php echo (int) $mainFrame['frame_upvotes']; ?></span>
                                    </button>
                                    <button type="button" class="vote-button vote-button--down" data-vote="down" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>
                                        <span aria-hidden="true">▼</span>
                                        <span class="vote-button__count" data-vote-count="down"><?php echo (int) $mainFrame['frame_downvotes']; ?></span>
                                    </button>
                                 </div>
                            </div>
                        </figcaption>
                    </figure>
                </div>

                <!-- sidebar dettagli prop e voti -->
                <aside class="hardware-sidebar" id="hardware-sidebar" aria-label="Dettagli prop">
                    <div class="hardware-sidebar-header">
                        <div class="hardware-sidebar-info">
                            <h2 id="sidebar-title" class="hardware-sidebar-title">Prop</h2>
                            <div class="hardware-sidebar-meta" id="sidebar-meta">
                                <span class="hardware-sidebar-meta__label">Seleziona un tag</span>
                            </div>
                        </div>
                        <div
                            class="hardware-sidebar-votes vote-controls"
                            id="sidebar-votes"
                            role="group"
                            data-vote-target="tag"
                            data-vote-id=""
                            data-can-vote="<?php echo $isLoggedIn ? '1' : '0'; ?>"
                            aria-label="Voti del tag"
                        >
                            <button type="button" class="vote-button vote-button--up" data-vote="up" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>
                                <span aria-hidden="true">▲</span>
                                <span class="vote-button__count" data-vote-count="up">0</span>
                            </button>
                            <button type="button" class="vote-button vote-button--down" data-vote="down" <?php echo $isLoggedIn ? '' : 'disabled'; ?>>
                                <span aria-hidden="true">▼</span>
                                <span class="vote-button__count" data-vote-count="down">0</span>
                            </button>
                        </div>
                    </div>
                    <div id="sidebar-content">
                        <p>Seleziona un Pulse-Tag per vedere i dettagli del prop.</p>
                    </div>
                </aside>
            </section>

            <?php if ($isLoggedIn): ?>
                <template id="hardware-options-template">
                    <?php foreach ($hardwareOptions as $hardware): ?>
                        <option value="<?php echo (int) $hardware['id_hardware']; ?>">
                            <?php echo htmlspecialchars($hardware['produttore'] . ' · ' . $hardware['nome_modello'], ENT_QUOTES, 'UTF-8'); ?>
                        </option>
                    <?php endforeach; ?>
                </template>
            <?php endif; ?>

            <!-- timeline dei frame -->
            <section class="frame-timeline" aria-labelledby="timeline-title">
                <div class="grid-heading">
                    <div class="heading-with-action">
                        <h2 id="timeline-title">Timeline frame</h2>
                        <a class="heading-add-link" href="dashboard.php?tab=create_frame" aria-label="Aggiungi un frame">+</a>
                    </div>
                </div>

                <ol class="timeline-list">
                    <?php foreach ($frames as $index => $frame): ?>
                        <li>
                            <div
                                class="timeline-item<?php echo $index === 0 ? ' timeline-item--active' : ''; ?>"
                                role="button"
                                tabindex="0"
                                data-frame-id="<?php echo (int) $frame['id_frame']; ?>"
                                data-frame-src="<?php echo htmlspecialchars('../' . $frame['immagine_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-frame-timestamp="<?php echo $frame['timestamp_frame'] ? htmlspecialchars($frame['timestamp_frame'], ENT_QUOTES, 'UTF-8') : 'Timestamp non disponibile'; ?>"
                                data-frame-description="<?php echo !empty($frame['descrizione_scena']) ? htmlspecialchars($frame['descrizione_scena'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                data-frame-author="Aggiunto da: <?php echo htmlspecialchars($frame['autore_frame'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-frame-upvotes="<?php echo (int) $frame['frame_upvotes']; ?>"
                                data-frame-downvotes="<?php echo (int) $frame['frame_downvotes']; ?>"
                                data-frame-alt="Frame di <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <img
                                    src="<?php echo htmlspecialchars('../' . $frame['immagine_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Miniatura frame <?php echo $index + 1; ?> di <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                <div>
                                    <strong class="timeline-item-title">
                                        Frame <?php echo $index + 1; ?>
                                    </strong>
                                    <p>
                                        <?php echo $frame['timestamp_frame'] ? htmlspecialchars($frame['timestamp_frame'], ENT_QUOTES, 'UTF-8') : 'Timestamp n/d'; ?>
                                    </p>
                                    <p class="timeline-credit">
                                        <?php echo htmlspecialchars($frame['autore_frame'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        <?php endif; ?>
    </div>
</main>

<script src="../js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/../js/theme-toggle.js'); ?>"></script>
<script src="../js/viewer.js?v=<?php echo filemtime(__DIR__ . '/../js/viewer.js'); ?>"></script>
</body>
</html>
