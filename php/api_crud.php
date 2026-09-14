<?php
// crud per film, frame e tag (liste ed eliminazione)
require __DIR__ . '/config.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

function request_value(string $key): ?string
{
    $source = $_SERVER['REQUEST_METHOD'] === 'GET' ? INPUT_GET : INPUT_POST;
    $value = filter_input($source, $key, FILTER_DEFAULT);

    if ($value === null) {
        return null;
    }

    return trim($value);
}

// id numerico > 0 (altrimenti errore 400)
function positive_id(string $key): int
{
    $source = $_SERVER['REQUEST_METHOD'] === 'GET' ? INPUT_GET : INPUT_POST;
    $value = filter_input($source, $key, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($value === false || $value === null) {
        send_json(false, 'ID non valido.', 400);
    }

    return (int) $value;
}

function require_method(string $method): void
{
    if ($_SERVER['REQUEST_METHOD'] !== $method) {
        send_json(false, 'Metodo non consentito.', 405);
    }
}

function is_admin(): bool
{
    return isset($_SESSION['ruolo']) && $_SESSION['ruolo'] === 'admin';
}

// true se admin e ha chiesto scope=all
function can_use_all_scope(): bool
{
    return is_admin() && request_value('scope') === 'all';
}

// direzione asc o desc
function sort_direction(): string
{
    return request_value('direction') === 'asc' ? 'ASC' : 'DESC';
}

// numero di pagina
function page_number(): int
{
    $value = filter_input(INPUT_GET, 'page', FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($value === false || $value === null) {
        return 1;
    }

    return (int) $value;
}

// impagina i risultati per il json
function pagination_data(array $items, int $total, int $page, int $pageSize): array
{
    $totalPages = max(1, (int) ceil($total / $pageSize));

    return [
        'items'       => $items,
        'total'       => $total,
        'page'        => $page,
        'page_size'   => $pageSize,
        'total_pages' => $totalPages,
        'has_prev'    => $page > 1,
        'has_next'    => $page < $totalPages,
    ];
}

// clausola order by in base ai filtri richiesti
function content_order(string $action, bool $showAll): string
{
    $sort = request_value('sort') ?? '';
    $direction = sort_direction();
    $orders = [
        'list_my_films' => [
            'name'   => "f.titolo $direction, f.anno_uscita DESC, f.id_film $direction",
            'film'   => "f.titolo $direction, f.anno_uscita DESC, f.id_film $direction",
            'date'   => "f.creato_il $direction, f.id_film $direction",
            'author' => "u.username $direction, f.creato_il DESC, f.id_film DESC",
        ],
        'list_my_frames' => [
            'name'   => "fr.descrizione_scena $direction, fr.timestamp_frame $direction, fr.id_frame $direction",
            'film'   => "f.titolo $direction, fr.timestamp_frame $direction, fr.id_frame $direction",
            'votes'  => "net_votes $direction, upvotes $direction, downvotes ASC, fr.id_frame DESC",
            'date'   => "fr.creato_il $direction, fr.id_frame $direction",
            'author' => "u.username $direction, fr.creato_il DESC, fr.id_frame DESC",
        ],
        'list_my_tags' => [
            'name'   => "h.nome_modello $direction, h.produttore $direction, t.id_tag $direction",
            'film'   => "f.titolo $direction, fr.timestamp_frame $direction, t.id_tag $direction",
            'votes'  => "net_votes $direction, upvotes $direction, downvotes ASC, t.id_tag DESC",
            'date'   => "t.creato_il $direction, t.id_tag $direction",
            'author' => "u.username $direction, t.creato_il DESC, t.id_tag DESC",
        ],
    ];

    if (isset($orders[$action][$sort])) {
        return 'ORDER BY ' . $orders[$action][$sort];
    }

    if ($action === 'list_my_frames' && $showAll) {
        return 'ORDER BY net_votes ASC, downvotes DESC, fr.creato_il DESC, fr.id_frame DESC';
    }

    if ($action === 'list_my_tags' && $showAll) {
        return 'ORDER BY net_votes ASC, downvotes DESC, t.creato_il DESC, t.id_tag DESC';
    }

    if ($action === 'list_my_films') {
        return "ORDER BY f.creato_il $direction, f.id_film $direction";
    }

    if ($action === 'list_my_frames') {
        return "ORDER BY fr.creato_il $direction, fr.id_frame $direction";
    }

    return "ORDER BY t.creato_il $direction, t.id_tag $direction";
}

// cancella il record controllando l'autore (se non si è admin)
function delete_owned(PDO $pdo, string $table, string $idColumn, string $ownerColumn, int $id, int $userId): void
{
    $where  = is_admin() ? "$idColumn = ?" : "$idColumn = ? AND $ownerColumn = ?";
    $params = is_admin() ? [$id] : [$id, $userId];

    $stmt = db_query($pdo, "DELETE FROM $table WHERE $where", $params);

    if ($stmt->rowCount() < 1) {
        send_json(false, 'Contenuto non trovato o non autorizzato.', 404);
    }
}

// controllo sessione utente
if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    send_json(false, 'Sessione non valida. Effettua il login.', 401);
}

$action = request_value('action');

if ($action === null || $action === '') {
    send_json(false, 'Azione mancante.', 400);
}

$userId   = (int) $_SESSION['id_utente'];
$pageSize = 10;
$page     = page_number();
$offset   = ($page - 1) * $pageSize;

try {
    // lista film
    if ($action === 'list_my_films') {
        require_method('GET');

        $showAll = can_use_all_scope();
        $where   = $showAll ? '' : 'WHERE f.id_utente_creatore = ?';
        $orderBy = content_order($action, $showAll);
        $params  = $showAll ? [] : [$userId];

        $items = db_query($pdo,
            "SELECT
                f.id_film,
                f.titolo,
                f.anno_uscita,
                f.regista,
                f.creato_il,
                u.username AS autore
             FROM film f
             INNER JOIN utenti u ON f.id_utente_creatore = u.id_utente
             $where
             $orderBy
             LIMIT $pageSize OFFSET $offset",
            $params
        )->fetchAll();

        $total = (int) db_query($pdo,
            "SELECT COUNT(*)
             FROM film f
             $where",
            $params
        )->fetchColumn();

        send_json(true, 'Film caricati.', 200, pagination_data($items, $total, $page, $pageSize));
    }

    // lista frame con voti
    if ($action === 'list_my_frames') {
        require_method('GET');

        $showAll = can_use_all_scope();
        $where   = $showAll ? '' : 'WHERE fr.id_utente_creatore = ?';
        $orderBy = content_order($action, $showAll);
        $params  = $showAll ? [] : [$userId];

        $items = db_query($pdo,
            "SELECT
                fr.id_frame,
                fr.timestamp_frame,
                fr.descrizione_scena,
                fr.creato_il,
                f.titolo AS film_titolo,
                f.anno_uscita,
                u.username AS autore,
                COALESCE(SUM(fv.upvote), 0) AS upvotes,
                COALESCE(SUM(fv.downvote), 0) AS downvotes,
                COALESCE(SUM(fv.upvote), 0) - COALESCE(SUM(fv.downvote), 0) AS net_votes
             FROM frame fr
             INNER JOIN film f ON fr.id_film = f.id_film
             INNER JOIN utenti u ON fr.id_utente_creatore = u.id_utente
             LEFT JOIN frame_voti fv ON fv.id_frame = fr.id_frame
             $where
             GROUP BY
                fr.id_frame,
                fr.timestamp_frame,
                fr.descrizione_scena,
                fr.creato_il,
                f.titolo,
                f.anno_uscita,
                u.username
             $orderBy
             LIMIT $pageSize OFFSET $offset",
            $params
        )->fetchAll();

        $total = (int) db_query($pdo,
            "SELECT COUNT(*)
             FROM frame fr
             $where",
            $params
        )->fetchColumn();

        send_json(true, 'Frame caricati.', 200, pagination_data($items, $total, $page, $pageSize));
    }

    // lista tag con dettagli hardware e voti
    if ($action === 'list_my_tags') {
        require_method('GET');

        $showAll = can_use_all_scope();
        $where   = $showAll ? '' : 'WHERE t.id_utente = ?';
        $orderBy = content_order($action, $showAll);
        $params  = $showAll ? [] : [$userId];

        $items = db_query($pdo,
            "SELECT
                t.id_tag,
                t.creato_il,
                h.nome_modello,
                h.produttore,
                fr.timestamp_frame,
                f.titolo AS film_titolo,
                u.username AS autore,
                COALESCE(SUM(tv.upvote), 0) AS upvotes,
                COALESCE(SUM(tv.downvote), 0) AS downvotes,
                COALESCE(SUM(tv.upvote), 0) - COALESCE(SUM(tv.downvote), 0) AS net_votes
             FROM tags t
             INNER JOIN hardware h ON t.id_hardware = h.id_hardware
             INNER JOIN frame fr ON t.id_frame = fr.id_frame
             INNER JOIN film f ON fr.id_film = f.id_film
             INNER JOIN utenti u ON t.id_utente = u.id_utente
             LEFT JOIN tag_voti tv ON tv.id_tag = t.id_tag
             $where
             GROUP BY
                t.id_tag,
                t.creato_il,
                h.nome_modello,
                h.produttore,
                fr.timestamp_frame,
                f.titolo,
                u.username
             $orderBy
             LIMIT $pageSize OFFSET $offset",
            $params
        )->fetchAll();

        $total = (int) db_query($pdo,
            "SELECT COUNT(*)
             FROM tags t
             $where",
            $params
        )->fetchColumn();

        send_json(true, 'Tag caricati.', 200, pagination_data($items, $total, $page, $pageSize));
    }

    // elimina film
    if ($action === 'delete_film') {
        require_method('POST');
        $id = positive_id('id_film');
        delete_owned($pdo, 'film', 'id_film', 'id_utente_creatore', $id, $userId);
        send_json(true, 'Film eliminato.', 200);
    }

    // elimina frame
    if ($action === 'delete_frame') {
        require_method('POST');
        $id = positive_id('id_frame');
        delete_owned($pdo, 'frame', 'id_frame', 'id_utente_creatore', $id, $userId);
        send_json(true, 'Frame eliminato.', 200);
    }

    // elimina tag
    if ($action === 'delete_tag') {
        require_method('POST');
        $id = positive_id('id_tag');
        delete_owned($pdo, 'tags', 'id_tag', 'id_utente', $id, $userId);
        send_json(true, 'Tag eliminato.', 200);
    }

    send_json(false, 'Azione non valida.', 400);
} catch (PDOException $e) {
    error_log($e->getMessage());
    send_json(false, 'Errore durante l\'operazione.', 500);
}
