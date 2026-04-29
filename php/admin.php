<?php
session_start();

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    header('Location: login.php');
    exit;
}

if ($_SESSION['ruolo'] !== 'admin') {
    header('Location: ../index.php');
    exit;
}

require __DIR__ . '/config.php';

$sessionUsername = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$stats = [
    'film' => 0,
    'frame' => 0,
    'tag' => 0,
];
$feedbackMessage = '';

try {
    $statsQueries = [
        'film' => 'SELECT COUNT(*) FROM FILM',
        'frame' => 'SELECT COUNT(*) FROM FRAME',
        'tag' => 'SELECT COUNT(*) FROM TAGS',
    ];

    foreach ($statsQueries as $key => $query) {
        $stmt = $pdo->query($query);
        $stats[$key] = (int) $stmt->fetchColumn();
    }
} catch (PDOException $e) {
    error_log($e->getMessage());
    $feedbackMessage = 'Impossibile caricare le statistiche admin.';
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin · CineSpecs</title>
    <script src="../js/theme-init.js?v=<?php echo filemtime(__DIR__ . '/../js/theme-init.js'); ?>"></script>
    <link rel="stylesheet" href="../css/style.css?v=<?php echo filemtime(__DIR__ . '/../css/style.css'); ?>">
    <link rel="stylesheet" href="../css/layout.css?v=<?php echo filemtime(__DIR__ . '/../css/layout.css'); ?>">
</head>
<body>
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
            <p class="header-meta">Area admin</p>
            <div class="header-auth">
                <span class="header-user">Ciao, <?php echo $sessionUsername; ?></span>
                <nav class="header-auth-pill" aria-label="Azioni account">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="admin.php" aria-current="page">Admin</a>
                    <a href="logout.php">Logout</a>
                </nav>
            </div>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Tema scuro</button>
        </div>
    </div>
</header>

<main>
    <div class="container">
        <section class="search-panel dashboard-hero" aria-labelledby="admin-title">
            <div class="grid-heading">
                <h1 id="admin-title">Dashboard admin</h1>
                <p>Moderazione catalogo</p>
            </div>
            <p class="dashboard-note">Qui controlli i contenuti inseriti nel catalogo. I tag sono ordinati dando priorita a quelli con punteggio piu basso.</p>
        </section>

        <section class="dashboard-stats" aria-label="Statistiche catalogo">
            <button
                type="button"
                class="dashboard-stat dashboard-stat--button"
                data-content-action="list_my_films"
                data-content-scope="all"
                aria-controls="my-content-panel"
            >
                <span class="dashboard-stat__label">Film totali</span>
                <strong class="dashboard-stat__value"><?php echo $stats['film']; ?></strong>
            </button>
            <button
                type="button"
                class="dashboard-stat dashboard-stat--button"
                data-content-action="list_my_frames"
                data-content-scope="all"
                aria-controls="my-content-panel"
            >
                <span class="dashboard-stat__label">Frame totali</span>
                <strong class="dashboard-stat__value"><?php echo $stats['frame']; ?></strong>
            </button>
            <button
                type="button"
                class="dashboard-stat dashboard-stat--button"
                data-content-action="list_my_tags"
                data-content-scope="all"
                aria-controls="my-content-panel"
            >
                <span class="dashboard-stat__label">Tag da moderare</span>
                <strong class="dashboard-stat__value"><?php echo $stats['tag']; ?></strong>
            </button>
        </section>

        <?php if ($feedbackMessage !== ''): ?>
            <p class="dashboard-feedback dashboard-feedback--error" aria-live="polite">
                <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <section class="search-panel my-content-panel" id="my-content-panel" aria-labelledby="admin-content-title">
            <div class="grid-heading">
                <div>
                    <h2 id="admin-content-title">Contenuti del catalogo</h2>
                    <p id="my-content-status" aria-live="polite">Seleziona una pillola per vedere l'elenco.</p>
                </div>
            </div>
            <div id="my-content-list" class="my-content-list" data-current-action=""></div>
        </section>
    </div>
</main>

<script src="../js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/../js/theme-toggle.js'); ?>"></script>
<script src="../js/validation.js?v=<?php echo filemtime(__DIR__ . '/../js/validation.js'); ?>"></script>
</body>
</html>
