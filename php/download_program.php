<?php
require 'conn.php';
session_start();

$eventId = intval($_GET['event_id'] ?? 0);

$res = pg_query_params($conn,
    "SELECT program_file
       FROM events
      WHERE event_id = $1",
    [$eventId]);

$row = pg_fetch_assoc($res);

if (!$row || $row['program_file'] === null) {
    http_response_code(404);
    echo 'Файл программы не найден';
    exit;
}

/* ----- определяем тип ----- */
$bin = pg_unescape_bytea($row['program_file']);
$signature = substr($bin, 0, 4);

if ($signature === '%PDF') {
    $mime = 'application/pdf';
    $ext  = '.pdf';
} elseif ($signature === "PK\x03\x04") {   // DOCX — это ZIP‑контейнер
    $mime = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    $ext  = '.docx';
} else {                                    // запасной вариант
    $mime = 'application/pdf';
    $ext  = '.pdf';
}

/* ----- отдаём ----- */
header('Content-Type: '        . $mime);
header('Content-Disposition: attachment; filename="program'.$ext.'"');
echo $bin;
