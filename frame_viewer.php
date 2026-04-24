<?php
session_start();

require __DIR__ . '/php/config.php';

$filmId = filter_input(INPUT_GET, 'film', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

$film = null;
$frames = [];
$dbError = false;
$message = '';
$isLoggedIn = isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo']);
$sessionUsername = $isLoggedIn ? htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8') : '';

if (!$filmId) {
    $message = 'Parametro film non valido.';
} else {
    try {
        $filmStmt = $pdo->prepare(
            'SELECT
                f.id_film,
                f.titolo,
                f.anno_uscita,
                f.regista,
                f.creato_il,
                u.username AS autore_film
             FROM FILM f
             INNER JOIN UTENTI u ON f.id_utente_creatore = u.id_utente
             WHERE f.id_film = ?'
        );
        $filmStmt->execute([$filmId]);
        $film = $filmStmt->fetch();

        if ($film) {
            $frameStmt = $pdo->prepare(
                'SELECT
                    fr.id_frame,
                    fr.immagine_path,
                    fr.timestamp_frame,
                    fr.descrizione_scena,
                    fr.creato_il,
                    u.username AS autore_frame
                 FROM FRAME fr
                 INNER JOIN UTENTI u ON fr.id_utente_creatore = u.id_utente
                 WHERE fr.id_film = ?
                 ORDER BY fr.timestamp_frame IS NULL, fr.timestamp_frame ASC, fr.id_frame ASC'
            );
            $frameStmt->execute([$filmId]);
            $frames = $frameStmt->fetchAll();
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
    <script src="js/theme-init.js?v=<?php echo filemtime(__DIR__ . '/js/theme-init.js'); ?>"></script>
    <link rel="stylesheet" href="css/style.css?v=<?php echo filemtime(__DIR__ . '/css/style.css'); ?>">
    <link rel="stylesheet" href="css/layout.css?v=<?php echo filemtime(__DIR__ . '/css/layout.css'); ?>">
    <link rel="stylesheet" href="css/components.css?v=<?php echo filemtime(__DIR__ . '/css/components.css'); ?>">
</head>
<body class="viewer-page">
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
            <p class="header-meta">Frame viewer</p>
            <div class="header-auth">
                <?php if ($isLoggedIn): ?>
                    <span class="header-user">Ciao, <?php echo $sessionUsername; ?></span>
                    <a href="dashboard.php">Dashboard</a>
                    <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                        <a href="admin.php">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
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
            <a href="index.php">Torna al catalogo</a>
        </p>

        <?php if ($message): ?>
            <div class="empty-state"><?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?></div>
        <?php endif; ?>

        <?php if ($film && $mainFrame): ?>
            <section class="viewer-header" aria-labelledby="viewer-title">
                <div>
                    <h1 id="viewer-title"><?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?></h1>
                    <p>
                        <?php echo (int) $film['anno_uscita']; ?>
                        ·
                        <?php echo htmlspecialchars($film['regista'], ENT_QUOTES, 'UTF-8'); ?>
                    </p>
                </div>
                <p><?php echo count($frames); ?> frame disponibili</p>
            </section>

            <section class="viewer-layout" aria-label="Viewer frame">
                <div class="viewer-main">
                    <figure class="frame-stage">
                        <div class="frame-tag-layer">
                            <img
                                id="frame-image"
                                src="<?php echo htmlspecialchars($mainFrame['immagine_path'], ENT_QUOTES, 'UTF-8'); ?>"
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
                            </div>
                        </figcaption>
                    </figure>
                </div>

                <aside class="hardware-sidebar" id="hardware-sidebar" aria-label="Dettagli prop">
                    <div class="hardware-sidebar-header">
                        <div class="hardware-sidebar-info">
                            <h2 id="sidebar-title" class="hardware-sidebar-title">Prop</h2>
                            <div class="hardware-sidebar-meta" id="sidebar-meta">
                                <span class="hardware-sidebar-meta__label">Seleziona un tag</span>
                            </div>
                        </div>
                        <div class="hardware-sidebar-votes" id="sidebar-votes" aria-label="Voti del tag">
                            <span class="hardware-vote-badge hardware-vote-badge--up" aria-label="Upvote: 0">
                                <span class="hardware-vote-badge__icon" aria-hidden="true">▲</span>
                                <span class="hardware-vote-badge__count">0</span>
                            </span>
                            <span class="hardware-vote-badge hardware-vote-badge--down" aria-label="Downvote: 0">
                                <span class="hardware-vote-badge__icon" aria-hidden="true">▼</span>
                                <span class="hardware-vote-badge__count">0</span>
                            </span>
                        </div>
                    </div>
                    <div id="sidebar-content">
                        <p>Seleziona un Pulse-Tag per vedere i dettagli del prop.</p>
                    </div>
                </aside>
            </section>

            <section class="frame-timeline" aria-labelledby="timeline-title">
                <div class="grid-heading">
                    <h2 id="timeline-title">Timeline frame</h2>
                </div>

                <ol class="timeline-list">
                    <?php foreach ($frames as $index => $frame): ?>
                        <li>
                            <article
                                class="timeline-item<?php echo $index === 0 ? ' timeline-item--active' : ''; ?>"
                                role="button"
                                tabindex="0"
                                data-frame-id="<?php echo (int) $frame['id_frame']; ?>"
                                data-frame-src="<?php echo htmlspecialchars($frame['immagine_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-frame-timestamp="<?php echo $frame['timestamp_frame'] ? htmlspecialchars($frame['timestamp_frame'], ENT_QUOTES, 'UTF-8') : 'Timestamp non disponibile'; ?>"
                                data-frame-description="<?php echo !empty($frame['descrizione_scena']) ? htmlspecialchars($frame['descrizione_scena'], ENT_QUOTES, 'UTF-8') : ''; ?>"
                                data-frame-author="Aggiunto da: <?php echo htmlspecialchars($frame['autore_frame'], ENT_QUOTES, 'UTF-8'); ?>"
                                data-frame-alt="Frame di <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>"
                            >
                                <img
                                    src="<?php echo htmlspecialchars($frame['immagine_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                    alt="Miniatura frame <?php echo $index + 1; ?> di <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                >
                                <div>
                                    <h3>Frame <?php echo $index + 1; ?></h3>
                                    <p>
                                        <?php echo $frame['timestamp_frame'] ? htmlspecialchars($frame['timestamp_frame'], ENT_QUOTES, 'UTF-8') : 'Timestamp n/d'; ?>
                                    </p>
                                    <p class="timeline-credit">
                                        <?php echo htmlspecialchars($frame['autore_frame'], ENT_QUOTES, 'UTF-8'); ?>
                                    </p>
                                </div>
                            </article>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </section>
        <?php endif; ?>
    </div>
</main>

<script src="js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/js/theme-toggle.js'); ?>"></script>
<script src="js/viewer.js?v=<?php echo filemtime(__DIR__ . '/js/viewer.js'); ?>"></script>
</body>
</html>
