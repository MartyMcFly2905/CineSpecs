<?php
session_start();

if (isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    header('Location: dashboard.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login · CineSpecs</title>
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
            <p class="header-meta">Accesso utente</p>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Tema scuro</button>
        </div>
    </div>
</header>

<main>
    <div class="container auth-container">
        <section class="auth-shell" aria-labelledby="login-title">
            <div class="auth-intro">
                <p class="auth-eyebrow">CineSpecs</p>
                <h1 id="login-title">Accedi al tuo archivio</h1>
                <p class="auth-lead">Entra con il tuo account per raggiungere la dashboard e continuare a lavorare sul catalogo.</p>
                <p class="auth-note">Non hai un account? <a href="register.php">Registrati</a></p>
            </div>

            <section class="search-panel auth-card" aria-label="Form di login">
                <form id="login-form" class="auth-form login-form" novalidate>
                    <input type="hidden" name="action" value="login">

                    <div class="field-group">
                        <label for="login-username">Username</label>
                        <input type="text" name="username" id="login-username" required>
                    </div>

                    <div class="field-group">
                        <label for="login-password">Password</label>
                        <input type="password" name="password" id="login-password" required>
                    </div>

                    <div class="field-group auth-submit login-form__submit">
                        <button type="submit">Accedi</button>
                    </div>
                </form>

                <p id="login-message" class="auth-feedback" aria-live="polite"></p>
            </section>
        </section>
    </div>
</main>

<script src="../js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/../js/theme-toggle.js'); ?>"></script>
<script src="../js/validation.js?v=<?php echo filemtime(__DIR__ . '/../js/validation.js'); ?>"></script>
<script>
const loginForm = document.getElementById('login-form');
const loginMessage = document.getElementById('login-message');

loginForm.addEventListener('submit', async function (event) {
    event.preventDefault();
    loginMessage.textContent = 'Accesso in corso...';

    try {
        const response = await fetch('auth.php', {
            method: 'POST',
            body: new FormData(loginForm)
        });

        const result = await response.json();
        loginMessage.textContent = result.message;

        if (result.success) {
            window.location.href = 'dashboard.php';
        }
    } catch (error) {
        loginMessage.textContent = 'Errore di rete. Riprova.';
    }
});
</script>
</body>
</html>
