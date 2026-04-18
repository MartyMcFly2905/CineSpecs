<?php
require __DIR__ . '/php/config.php';

$searchTerm = trim($_GET['q'] ?? '');
$yearFilter = filter_input(INPUT_GET, 'year', FILTER_VALIDATE_INT);
$films = [];
$availableYears = [];
$dbError = false;

try {
    $yearStmt = $pdo->query('SELECT DISTINCT anno_uscita FROM FILM WHERE anno_uscita IS NOT NULL ORDER BY anno_uscita DESC');
    $availableYears = $yearStmt->fetchAll(PDO::FETCH_COLUMN) ?: [];

    $query = 'SELECT id_film, titolo, anno_uscita, regista, copertina_path FROM FILM';
    $conditions = [];
    $params = [];

    if ($searchTerm !== '') {
        $conditions[] = 'titolo LIKE :search';
        $params[':search'] = '%' . $searchTerm . '%';
    }

    if ($yearFilter) {
        $conditions[] = 'anno_uscita = :anno';
        $params[':anno'] = $yearFilter;
    }

    if ($conditions) {
        $query .= ' WHERE ' . implode(' AND ', $conditions);
    }

    $query .= ' ORDER BY anno_uscita DESC, titolo ASC';

    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    $films = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $dbError = true;
}
?>
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>CineSpecs · Archivio hardware cinematografico</title>
    <script>
        (function () {
            var savedTheme = '';

            try {
                savedTheme = localStorage.getItem('cinespecs-theme');
            } catch (error) {
                savedTheme = '';
            }

            if (savedTheme === 'dark') {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        }());
    </script>
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
            <p class="header-meta">Hardware usato nei film</p>
            <button class="theme-toggle" id="theme-toggle" type="button" aria-pressed="false">Tema scuro</button>
        </div>
    </div>
</header>
    <main>
        <div class="container">
            <section class="search-panel" aria-labelledby="search-title">
                <div class="grid-heading">
                    <h2 id="search-title">Ricerca catalogo</h2>
                    <p>Filtra per titolo o anno e trova i film schedati</p>
                </div>
                <form method="get" action="index.php">
                    <div class="field-group">
                        <label for="search-input">Titolo o parola chiave</label>
                        <input
                            type="text"
                            name="q"
                            id="search-input"
                            placeholder="Es. Alien, Blade Runner"
                            value="<?php echo htmlspecialchars($searchTerm, ENT_QUOTES, 'UTF-8'); ?>"
                        >
                    </div>
                    <div class="field-group">
                        <label for="year-filter">Anno</label>
                        <select name="year" id="year-filter">
                            <option value="">Tutti gli anni</option>
                            <?php foreach ($availableYears as $yearOption): ?>
                                <option value="<?php echo (int) $yearOption; ?>" <?php echo ((int) $yearOption === (int) $yearFilter) ? 'selected' : ''; ?>>
                                    <?php echo (int) $yearOption; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="field-group">
                        <label class="visually-hidden" for="submit-btn">Avvia ricerca</label>
                        <button id="submit-btn" type="submit">Aggiorna catalogo</button>
                    </div>
                </form>
            </section>

            <section aria-labelledby="catalog-title">
                <div class="grid-heading">
                    <h2 id="catalog-title">Catalogo film</h2>
                    <p><?php echo count($films); ?> risultati</p>
                </div>

                <?php if ($dbError): ?>
                    <div class="empty-state">Impossibile caricare il catalogo. Riprova più tardi.</div>
                <?php elseif (!$films): ?>
                    <div class="empty-state">Nessun film corrisponde ai criteri attuali.</div>
                <?php else: ?>
                    <ul class="film-grid">
                        <?php foreach ($films as $film): ?>
                            <li>
                                <article class="film-card">
                                    <figure>
                                        <?php if (!empty($film['copertina_path'])): ?>
                                            <img
                                                src="<?php echo htmlspecialchars($film['copertina_path'], ENT_QUOTES, 'UTF-8'); ?>"
                                                alt="Locandina di <?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?>"
                                            >
                                        <?php else: ?>
                                            <span class="film-card__placeholder">Cover non disponibile</span>
                                        <?php endif; ?>
                                    </figure>
                                    <div>
                                        <h3><?php echo htmlspecialchars($film['titolo'], ENT_QUOTES, 'UTF-8'); ?></h3>
                                        <div class="film-meta">
                                            <span><?php echo $film['anno_uscita'] ? (int) $film['anno_uscita'] : 'Anno n/d'; ?></span>
                                            <span><?php echo $film['regista'] ? htmlspecialchars($film['regista'], ENT_QUOTES, 'UTF-8') : 'Regista n/d'; ?></span>
                                        </div>
                                    </div>
                                    <div class="film-actions">
                                        <a href="frame_viewer.php?film=<?php echo (int) $film['id_film']; ?>">Apri frame</a>
                                    </div>
                                </article>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
            </section>
        </div>
    </main>
    <script src="js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/js/theme-toggle.js'); ?>"></script>
</body>
</html>
