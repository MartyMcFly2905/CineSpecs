<?php
// Ritorna i tag di un frame
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    send_json(false, 'Metodo non consentito. Usa una richiesta GET.', 405);
}

$idFrame = filter_input(INPUT_GET, 'id_frame', FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1],
]);

if ($idFrame === null) {
    send_json(false, 'Parametro id_frame mancante.', 400);
}

if ($idFrame === false) {
    send_json(false, 'Parametro id_frame non valido.', 400);
}

try {
    // Prende tag, hardware collegato, autore e voti
    $rows = db_query($pdo,
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
            h.curiosita,
            h.prop_fittizio
         FROM tags t
         INNER JOIN hardware h ON t.id_hardware = h.id_hardware
         INNER JOIN utenti u ON t.id_utente = u.id_utente
         LEFT JOIN tag_voti tv ON tv.id_tag = t.id_tag
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
            h.curiosita,
            h.prop_fittizio
         ORDER BY t.id_tag ASC',
        [$idFrame]
    )->fetchAll();

    $tags = [];

    // Prepara i dati da inviare
    foreach ($rows as $row) {
        $tags[] = [
            'id_tag'    => (int)   $row['id_tag'],
            'coord_x'   => (float) $row['coord_x'],
            'coord_y'   => (float) $row['coord_y'],
            'creato_il' =>         $row['creato_il'],
            'upvotes'   => (int)   $row['upvotes'],
            'downvotes' => (int)   $row['downvotes'],
            'autore' => [
                'id'       => (int) $row['id_utente'],
                'username' =>       $row['autore_tag'],
            ],
            'hardware' => [
                'id'            => (int)  $row['id_hardware'],
                'nome_modello'  =>        $row['nome_modello'],
                'produttore'    =>        $row['produttore'],
                'anno_rilascio' => $row['anno_rilascio'] !== null ? (int) $row['anno_rilascio'] : null,
                'descrizione'   =>        $row['descrizione'],
                'curiosita'     =>        $row['curiosita'],
                'prop_fittizio' => (bool) $row['prop_fittizio'],
            ],
        ];
    }

    send_json(true, '', 200, $tags);
} catch (PDOException $e) {
    error_log($e->getMessage());
    send_json(false, 'Errore durante il recupero dei tag.', 500);
}
