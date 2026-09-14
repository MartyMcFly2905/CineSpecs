<?php
// registra o aggiorna voti (upvote/downvote) su frame e tag
require __DIR__ . '/config.php';

session_start();

header('Content-Type: application/json; charset=utf-8');

function post_value(string $key): ?string
{
    $value = filter_input(INPUT_POST, $key, FILTER_DEFAULT);

    if ($value === null) {
        return null;
    }

    return trim($value);
}

function positive_post_id(string $key): int
{
    $value = filter_input(INPUT_POST, $key, FILTER_VALIDATE_INT, [
        'options' => ['min_range' => 1],
    ]);

    if ($value === false || $value === null) {
        send_json(false, 'ID non valido.', 400);
    }

    return (int) $value;
}

// conta totale upvote e downvote
function vote_counts(PDO $pdo, string $table, string $idColumn, int $id): array
{
    $row = db_query($pdo,
        "SELECT
            COALESCE(SUM(upvote), 0) AS upvotes,
            COALESCE(SUM(downvote), 0) AS downvotes
         FROM $table
         WHERE $idColumn = ?",
        [$id]
    )->fetch();

    return [
        'upvotes'   => (int) ($row['upvotes']   ?? 0),
        'downvotes' => (int) ($row['downvotes'] ?? 0),
    ];
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    send_json(false, 'Metodo non consentito. Usa una richiesta POST.', 405);
}

if (!isset($_SESSION['id_utente'], $_SESSION['username'], $_SESSION['ruolo'])) {
    send_json(false, 'Devi effettuare il login per votare.', 401);
}

$targetType = post_value('target_type') ?? 'tag';
$vote       = post_value('vote');
$userId     = (int) $_SESSION['id_utente'];

if ($vote !== 'up' && $vote !== 'down') {
    send_json(false, 'Voto non valido.', 400);
}

$upvote   = $vote === 'up'   ? 1 : 0;
$downvote = $vote === 'down' ? 1 : 0;

try {
    if ($targetType === 'frame') {
        $idFrame = positive_post_id('id_frame');

        // controllo se il frame esiste
        if (!db_query($pdo, 'SELECT id_frame FROM frame WHERE id_frame = ? LIMIT 1', [$idFrame])->fetch()) {
            send_json(false, 'Frame non trovato.', 404);
        }

        // inserisco o aggiorno il voto esistente
        db_query($pdo,
            'INSERT INTO frame_voti (id_frame, id_utente, upvote, downvote)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                upvote = VALUES(upvote),
                downvote = VALUES(downvote)',
            [$idFrame, $userId, $upvote, $downvote]
        );

        $counts             = vote_counts($pdo, 'frame_voti', 'id_frame', $idFrame);
        $counts['id_frame'] = $idFrame;

        send_json(true, 'Voto registrato.', 200, $counts);
    }

    // stessa cosa per i tag
    if ($targetType === 'tag') {
        $idTag = positive_post_id('id_tag');

        if (!db_query($pdo, 'SELECT id_tag FROM tags WHERE id_tag = ? LIMIT 1', [$idTag])->fetch()) {
            send_json(false, 'Tag non trovato.', 404);
        }

        db_query($pdo,
            'INSERT INTO tag_voti (id_tag, id_utente, upvote, downvote)
             VALUES (?, ?, ?, ?)
             ON DUPLICATE KEY UPDATE
                upvote = VALUES(upvote),
                downvote = VALUES(downvote)',
            [$idTag, $userId, $upvote, $downvote]
        );

        $counts          = vote_counts($pdo, 'tag_voti', 'id_tag', $idTag);
        $counts['id_tag'] = $idTag;

        send_json(true, 'Voto registrato.', 200, $counts);
    }

    send_json(false, 'Tipo di contenuto non valido.', 400);
} catch (PDOException $e) {
    error_log($e->getMessage());
    send_json(false, 'Errore durante il salvataggio del voto.', 500);
}
