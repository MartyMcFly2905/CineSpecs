<?php
require __DIR__ . '/php/config.php';

$filmId = filter_input(INPUT_GET, 'film', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

$film = null;
$frames = [];
$dbError = false;
$message = '';

if (!$filmId) {
    $message = 'Parametro film non valido.';
} else {
    try {
        $filmStmt = $pdo->prepare('SELECT id_film, titolo, anno_uscita, regista FROM FILM WHERE id_film = ?');
        $filmStmt->execute([$filmId]);
        $film = $filmStmt->fetch();

        if ($film) {
            $frameStmt = $pdo->prepare(
                'SELECT id_frame, immagine_path, timestamp_frame, descrizione_scena
                 FROM FRAME
                 WHERE id_film = ?
                 ORDER BY timestamp_frame IS NULL, timestamp_frame ASC, id_frame ASC'
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
                            <span id="frame-timestamp">
                                <?php echo $mainFrame['timestamp_frame'] ? htmlspecialchars($mainFrame['timestamp_frame'], ENT_QUOTES, 'UTF-8') : 'Timestamp non disponibile'; ?>
                            </span>
                            <span id="frame-description"><?php echo !empty($mainFrame['descrizione_scena']) ? htmlspecialchars($mainFrame['descrizione_scena'], ENT_QUOTES, 'UTF-8') : ''; ?></span>
                        </figcaption>
                    </figure>
                </div>

                <aside class="hardware-sidebar" id="hardware-sidebar" aria-labelledby="hardware-title">
                    <h2 id="hardware-title">Hardware</h2>
                    <div id="sidebar-content">
                        <p>Seleziona un Pulse-Tag per vedere i dettagli hardware.</p>
                    </div>
                </aside>
            </section>

            <section class="frame-timeline" aria-labelledby="timeline-title">
                <div class="grid-heading">
                    <h2 id="timeline-title">Timeline frame</h2>
                    <p>Ordine per timestamp</p>
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
