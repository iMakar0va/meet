<?php
session_start();
require './php/conn.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['user_id'], $_POST['event_id'], $_POST['presense'])) {
    http_response_code(400);
    exit("Ошибка: неверные данные");
}

$userId = $_POST['user_id'];
$eventId = $_POST['event_id'];
$presense = $_POST['presense'] === 'true' ? 'true' : 'false';

$query = "UPDATE user_events SET presense = $1 WHERE user_id = $2 AND event_id = $3";
$result = pg_query_params($conn, $query, [$presense, $userId, $eventId]);

if ($result) {
    echo "Статус успешно обновлён";
} else {
    http_response_code(500);
    echo "Ошибка обновления";
}

pg_close($conn);
?>
