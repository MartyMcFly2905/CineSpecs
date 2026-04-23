<?php
session_start();

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    header('Location: login.php');
    exit;
}

$sessionUsername = htmlspecialchars($_SESSION['username'], ENT_QUOTES, 'UTF-8');
$sessionRole = htmlspecialchars($_SESSION['ruolo'], ENT_QUOTES, 'UTF-8');
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard · CineSpecs</title>
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
            <p class="header-meta">Area riservata</p>
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
        <section class="search-panel" aria-labelledby="dashboard-title">
            <div class="grid-heading">
                <h1 id="dashboard-title">Dashboard</h1>
                <p>Accesso riservato agli utenti autenticati</p>
            </div>
            <p class="auth-note">Utente attuale: <?php echo $sessionUsername; ?></p>
            <p class="auth-note">Ruolo: <?php echo $sessionRole; ?></p>
            <p><a href="index.php">Torna alla homepage</a></p>
        </section>
    </div>
</main>

<script src="js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/js/theme-toggle.js'); ?>"></script>
</body>
</html>
