<?php
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode([
        'success' => false,
        'message' => 'Metodo non consentito. Usa una richiesta GET.',
        'data' => [],
    ]);
    exit;
}

$idFrame = filter_input(INPUT_GET, 'id_frame', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($idFrame === null) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Parametro id_frame mancante.',
        'data' => [],
    ]);
    exit;
}

if ($idFrame === false) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Parametro id_frame non valido.',
        'data' => [],
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare(
        'SELECT
            t.id_tag,
            t.coord_x,
            t.coord_y,
            t.creato_il,
            u.id_utente,
            u.username AS autore_tag,
            COALESCE(SUM(tv.upvote), 0) AS upvotes,
            COALESCE(SUM(tv.downvote), 0) AS downvotes,
            h.id_hardware,
            h.nome_modello,
            h.produttore,
            h.anno_rilascio,
            h.descrizione,
            h.curiosita
         FROM TAGS t
         INNER JOIN HARDWARE h ON t.id_hardware = h.id_hardware
         INNER JOIN UTENTI u ON t.id_utente = u.id_utente
         LEFT JOIN TAG_VOTI tv ON tv.id_tag = t.id_tag
         WHERE t.id_frame = ?
         GROUP BY
            t.id_tag,
            t.coord_x,
            t.coord_y,
            t.creato_il,
            u.id_utente,
            u.username,
            h.id_hardware,
            h.nome_modello,
            h.produttore,
            h.anno_rilascio,
            h.descrizione,
            h.curiosita
         ORDER BY t.id_tag ASC'
    );
    $stmt->execute([$idFrame]);
    $rows = $stmt->fetchAll();

    $tags = [];

    foreach ($rows as $row) {
        $tags[] = [
            'id_tag' => (int) $row['id_tag'],
            'coord_x' => (float) $row['coord_x'],
            'coord_y' => (float) $row['coord_y'],
            'creato_il' => $row['creato_il'],
            'upvotes' => (int) $row['upvotes'],
            'downvotes' => (int) $row['downvotes'],
            'autore' => [
                'id' => (int) $row['id_utente'],
                'username' => $row['autore_tag'],
            ],
            'hardware' => [
                'id' => (int) $row['id_hardware'],
                'nome_modello' => $row['nome_modello'],
                'produttore' => $row['produttore'],
                'anno_rilascio' => $row['anno_rilascio'] !== null ? (int) $row['anno_rilascio'] : null,
                'descrizione' => $row['descrizione'],
                'curiosita' => $row['curiosita'],
            ],
        ];
    }

    echo json_encode([
        'success' => true,
        'data' => $tags,
    ]);
} catch (PDOException $e) {
    error_log($e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Errore durante il recupero dei tag.',
        'data' => [],
    ]);
}
