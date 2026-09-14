<!-- vista dashboard contributor -->
<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Dashboard contributor · CineSpecs</title>
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
            <p class="header-meta">Area contributor</p>
            <div class="header-auth">
                <span class="header-user">Ciao, <?php echo $sessionUsername; ?></span>
                <nav class="header-auth-pill" aria-label="Azioni account">
                    <a href="dashboard.php" aria-current="page">Dashboard</a>
                    <?php if ($_SESSION['ruolo'] === 'admin'): ?>
                        <a href="admin.php">Admin</a>
                    <?php endif; ?>
                    <a href="logout.php">Logout</a>
                </nav>
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
            <p class="dashboard-note">Qui puoi contribuire al catalogo con film e frame. I tag e i prop si aggiungono dal frame viewer in modalita ispezione.</p>
        </section>

        <section class="dashboard-stats" aria-label="Statistiche contributi">
            <button
                type="button"
                class="dashboard-stat dashboard-stat--button"
                data-content-action="list_my_films"
                aria-controls="my-content-panel"
            >
                <span class="dashboard-stat__label">Film personali</span>
                <strong class="dashboard-stat__value"><?php echo $stats['film']; ?></strong>
            </button>
            <button
                type="button"
                class="dashboard-stat dashboard-stat--button"
                data-content-action="list_my_frames"
                aria-controls="my-content-panel"
            >
                <span class="dashboard-stat__label">Frame personali</span>
                <strong class="dashboard-stat__value"><?php echo $stats['frame']; ?></strong>
            </button>
            <button
                type="button"
                class="dashboard-stat dashboard-stat--button"
                data-content-action="list_my_tags"
                aria-controls="my-content-panel"
            >
                <span class="dashboard-stat__label">Tag personali</span>
                <strong class="dashboard-stat__value"><?php echo $stats['tag']; ?></strong>
            </button>
        </section>

        <?php if ($feedbackMessage !== ''): ?>
            <p class="dashboard-feedback dashboard-feedback--<?php echo htmlspecialchars($feedbackType, ENT_QUOTES, 'UTF-8'); ?>" aria-live="polite">
                <?php echo htmlspecialchars($feedbackMessage, ENT_QUOTES, 'UTF-8'); ?>
            </p>
        <?php endif; ?>

        <section class="search-panel my-content-panel" id="my-content-panel" aria-labelledby="my-content-title">
            <div class="grid-heading">
                <div>
                    <h2 id="my-content-title">I miei contenuti</h2>
                    <p id="my-content-status" aria-live="polite">Seleziona una pillola per vedere l'elenco.</p>
                </div>
            </div>
            <div id="my-content-list" class="my-content-list" data-current-action=""></div>
        </section>

        <section class="dashboard-switcher" aria-label="Scegli contenuto da inserire">
            <button
                type="button"
                class="dashboard-switcher__button<?php echo $activePanel === 'create_film' ? ' dashboard-switcher__button--active' : ''; ?>"
                data-dashboard-target="create_film"
                aria-pressed="<?php echo $activePanel === 'create_film' ? 'true' : 'false'; ?>"
            >
                <?php echo $isEditingFilm ? 'Modifica film' : 'Nuovo film'; ?>
            </button>
            <button
                type="button"
                class="dashboard-switcher__button<?php echo $activePanel === 'create_frame' ? ' dashboard-switcher__button--active' : ''; ?>"
                data-dashboard-target="create_frame"
                aria-pressed="<?php echo $activePanel === 'create_frame' ? 'true' : 'false'; ?>"
            >
                <?php echo $isEditingFrame ? 'Modifica frame' : 'Nuovo frame'; ?>
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
                    <h2 id="film-form-title"><?php echo $isEditingFilm ? 'Modifica film' : 'Nuovo film'; ?></h2>
                    <p><?php echo $isEditingFilm ? 'Aggiorna i dati del film senza perdere i frame collegati' : 'Controllo duplicati su titolo, anno e regista'; ?></p>
                </div>
                <form
                    id="film-form"
                    class="contributor-form"
                    method="post"
                    action="dashboard.php"
                    enctype="multipart/form-data"
                    novalidate
                >
                    <input type="hidden" name="action" value="<?php echo $isEditingFilm ? 'update_film' : 'create_film'; ?>">
                    <?php if ($isEditingFilm): ?>
                        <input type="hidden" name="id_film" value="<?php echo (int) $editFilmId; ?>">
                    <?php endif; ?>

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
                        <?php if ($isEditingFilm): ?>
                            <p class="field-help">Lascia vuoto per mantenere la copertina attuale.</p>
                        <?php endif; ?>
                    </div>

                    <div class="field-group field-group--full">
                        <label for="film-synopsis">Sinossi</label>
                        <textarea name="sinossi" id="film-synopsis" rows="4" maxlength="200" placeholder="Breve descrizione (max 200 caratteri)"><?php echo htmlspecialchars($filmData['sinossi'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                    </div>

                    <div class="field-group field-group--full contributor-actions">
                        <button type="submit"><?php echo $isEditingFilm ? 'Aggiorna film' : 'Salva film'; ?></button>
                        <?php if ($isEditingFilm): ?>
                            <a class="contributor-cancel-link" href="dashboard.php?tab=create_film">Annulla modifica</a>
                        <?php endif; ?>
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
                    <h2 id="frame-form-title"><?php echo $isEditingFrame ? 'Modifica frame' : 'Nuovo frame'; ?></h2>
                    <p><?php echo $isEditingFrame ? 'Aggiorna timestamp, descrizione o immagine senza eliminare i tag collegati' : 'Associato a un film gia presente nel database'; ?></p>
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
                        <input type="hidden" name="action" value="<?php echo $isEditingFrame ? 'update_frame' : 'create_frame'; ?>">
                        <?php if ($isEditingFrame): ?>
                            <input type="hidden" name="id_frame" value="<?php echo (int) $editFrameId; ?>">
                        <?php endif; ?>

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
                            <input type="text" name="timestamp_frame" id="frame-timestamp" placeholder="HH:MM:SS" inputmode="numeric" pattern="^(?:[01]\\d|2[0-3]):[0-5]\\d:[0-5]\\d$" required value="<?php echo htmlspecialchars($frameData['timestamp_frame'], ENT_QUOTES, 'UTF-8'); ?>">
                            
                        </div>

                        <div class="field-group">
                            <label for="frame-image">Immagine frame</label>
                            <input type="file" name="immagine_frame" id="frame-image" accept=".jpg,.jpeg,.png,.webp" <?php echo $isEditingFrame ? '' : 'required'; ?>>
                            <?php if ($isEditingFrame): ?>
                                <p class="field-help">Lascia vuoto per mantenere l'immagine attuale.</p>
                            <?php endif; ?>
                        </div>

                        <div class="field-group field-group--full">
                            <label for="frame-description">Descrizione scena</label>
                            <textarea name="descrizione_scena" id="frame-description" rows="4" maxlength="255"><?php echo htmlspecialchars($frameData['descrizione_scena'], ENT_QUOTES, 'UTF-8'); ?></textarea>
                        </div>

                        <div class="field-group field-group--full contributor-actions">
                            <button type="submit"><?php echo $isEditingFrame ? 'Aggiorna frame' : 'Salva frame'; ?></button>
                            <?php if ($isEditingFrame): ?>
                                <a class="contributor-cancel-link" href="dashboard.php?tab=create_frame">Annulla modifica</a>
                            <?php endif; ?>
                        </div>
                    </form>
                <?php endif; ?>
            </section>
        </section>
    </div>
</main>

<script src="../js/theme-toggle.js?v=<?php echo filemtime(__DIR__ . '/../js/theme-toggle.js'); ?>"></script>
<script src="../js/validation.js?v=<?php echo filemtime(__DIR__ . '/../js/validation.js'); ?>"></script>
</body>
</html>
