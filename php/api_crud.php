<?php
require __DIR__ . '/config.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

function send_json(bool $success, string $message, int $statusCode, array $data = []): void
{
    http_response_code($statusCode);
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data' => $data,
    ]);
    exit;
}

function request_value(string $key): ?string
{
    $source = $_SERVER['REQUEST_METHOD'] === 'GET' ? INPUT_GET : INPUT_POST;
    $value = filter_input($source, $key, FILTER_DEFAULT);

    if ($value === null) {
        return null;
    }

    return trim($value);
}

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

function can_use_all_scope(): bool
{
    return is_admin() && request_value('scope') === 'all';
}

function sort_direction(): string
{
    return request_value('direction') === 'asc' ? 'ASC' : 'DESC';
}

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

function pagination_data(array $items, int $total, int $page, int $pageSize): array
{
    $totalPages = max(1, (int) ceil($total / $pageSize));

    return [
        'items' => $items,
        'total' => $total,
        'page' => $page,
        'page_size' => $pageSize,
        'total_pages' => $totalPages,
        'has_prev' => $page > 1,
        'has_next' => $page < $totalPages,
    ];
}

function content_order(string $action, bool $showAll): string
{
    $sort = request_value('sort') ?? '';
    $direction = sort_direction();
    $orders = [
        'list_my_films' => [
            'name' => "f.titolo $direction, f.anno_uscita DESC, f.id_film $direction",
            'film' => "f.titolo $direction, f.anno_uscita DESC, f.id_film $direction",
            'date' => "f.creato_il $direction, f.id_film $direction",
            'author' => "u.username $direction, f.creato_il DESC, f.id_film DESC",
        ],
        'list_my_frames' => [
            'name' => "fr.descrizione_scena $direction, fr.timestamp_frame $direction, fr.id_frame $direction",
            'film' => "f.titolo $direction, fr.timestamp_frame $direction, fr.id_frame $direction",
            'votes' => "net_votes $direction, upvotes $direction, downvotes ASC, fr.id_frame DESC",
            'date' => "fr.creato_il $direction, fr.id_frame $direction",
            'author' => "u.username $direction, fr.creato_il DESC, fr.id_frame DESC",
        ],
        'list_my_tags' => [
            'name' => "h.nome_modello $direction, h.produttore $direction, t.id_tag $direction",
            'film' => "f.titolo $direction, fr.timestamp_frame $direction, t.id_tag $direction",
            'votes' => "net_votes $direction, upvotes $direction, downvotes ASC, t.id_tag DESC",
            'date' => "t.creato_il $direction, t.id_tag $direction",
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

function delete_owned(PDO $pdo, string $table, string $idColumn, string $ownerColumn, int $id, int $userId): void
{
    $where = is_admin() ? "$idColumn = ?" : "$idColumn = ? AND $ownerColumn = ?";
    $params = is_admin() ? [$id] : [$id, $userId];

    $stmt = $pdo->prepare("DELETE FROM $table WHERE $where");
    $stmt->execute($params);

    if ($stmt->rowCount() < 1) {
        send_json(false, 'Contenuto non trovato o non autorizzato.', 404);
    }
}

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    send_json(false, 'Sessione non valida. Effettua il login.', 401);
}

$action = request_value('action');

if ($action === null || $action === '') {
    send_json(false, 'Azione mancante.', 400);
}

$userId = (int) $_SESSION['id_utente'];
$pageSize = 10;
$page = page_number();
$offset = ($page - 1) * $pageSize;

try {
    if ($action === 'list_my_films') {
        require_method('GET');

        $showAll = can_use_all_scope();
        $where = $showAll ? '' : 'WHERE f.id_utente_creatore = ?';
        $orderBy = content_order($action, $showAll);
        $stmt = $pdo->prepare(
            "SELECT
                f.id_film,
                f.titolo,
                f.anno_uscita,
                f.regista,
                f.creato_il,
                u.username AS autore
             FROM FILM f
             INNER JOIN UTENTI u ON f.id_utente_creatore = u.id_utente
             $where
             $orderBy
             LIMIT $pageSize OFFSET $offset"
        );
        $stmt->execute($showAll ? [] : [$userId]);
        $items = $stmt->fetchAll();

        $countStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM FILM f
             $where"
        );
        $countStmt->execute($showAll ? [] : [$userId]);
        $total = (int) $countStmt->fetchColumn();

        send_json(true, 'Film caricati.', 200, pagination_data($items, $total, $page, $pageSize));
    }

    if ($action === 'list_my_frames') {
        require_method('GET');

        $showAll = can_use_all_scope();
        $where = $showAll ? '' : 'WHERE fr.id_utente_creatore = ?';
        $orderBy = content_order($action, $showAll);
        $stmt = $pdo->prepare(
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
             FROM FRAME fr
             INNER JOIN FILM f ON fr.id_film = f.id_film
             INNER JOIN UTENTI u ON fr.id_utente_creatore = u.id_utente
             LEFT JOIN FRAME_VOTI fv ON fv.id_frame = fr.id_frame
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
             LIMIT $pageSize OFFSET $offset"
        );
        $stmt->execute($showAll ? [] : [$userId]);
        $items = $stmt->fetchAll();

        $countStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM FRAME fr
             $where"
        );
        $countStmt->execute($showAll ? [] : [$userId]);
        $total = (int) $countStmt->fetchColumn();

        send_json(true, 'Frame caricati.', 200, pagination_data($items, $total, $page, $pageSize));
    }

    if ($action === 'list_my_tags') {
        require_method('GET');

        $showAll = can_use_all_scope();
        $where = $showAll ? '' : 'WHERE t.id_utente = ?';
        $orderBy = content_order($action, $showAll);
        $stmt = $pdo->prepare(
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
             FROM TAGS t
             INNER JOIN HARDWARE h ON t.id_hardware = h.id_hardware
             INNER JOIN FRAME fr ON t.id_frame = fr.id_frame
             INNER JOIN FILM f ON fr.id_film = f.id_film
             INNER JOIN UTENTI u ON t.id_utente = u.id_utente
             LEFT JOIN TAG_VOTI tv ON tv.id_tag = t.id_tag
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
             LIMIT $pageSize OFFSET $offset"
        );
        $stmt->execute($showAll ? [] : [$userId]);
        $items = $stmt->fetchAll();

        $countStmt = $pdo->prepare(
            "SELECT COUNT(*)
             FROM TAGS t
             $where"
        );
        $countStmt->execute($showAll ? [] : [$userId]);
        $total = (int) $countStmt->fetchColumn();

        send_json(true, 'Tag caricati.', 200, pagination_data($items, $total, $page, $pageSize));
    }

    if ($action === 'delete_film') {
        require_method('POST');
        $id = positive_id('id_film');
        delete_owned($pdo, 'FILM', 'id_film', 'id_utente_creatore', $id, $userId);
        send_json(true, 'Film eliminato.', 200);
    }

    if ($action === 'delete_frame') {
        require_method('POST');
        $id = positive_id('id_frame');
        delete_owned($pdo, 'FRAME', 'id_frame', 'id_utente_creatore', $id, $userId);
        send_json(true, 'Frame eliminato.', 200);
    }

    if ($action === 'delete_tag') {
        require_method('POST');
        $id = positive_id('id_tag');
        delete_owned($pdo, 'TAGS', 'id_tag', 'id_utente', $id, $userId);
        send_json(true, 'Tag eliminato.', 200);
    }

    send_json(false, 'Azione non valida.', 400);
} catch (PDOException $e) {
    error_log($e->getMessage());
    send_json(false, 'Errore durante l\'operazione.', 500);
}
