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
            h.id_hardware,
            h.nome_modello,
            h.produttore,
            h.anno_rilascio,
            h.descrizione,
            h.curiosita
         FROM TAGS t
         INNER JOIN HARDWARE h ON t.id_hardware = h.id_hardware
         WHERE t.id_frame = ?
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
