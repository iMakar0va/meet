<?php
session_start();
require 'conn.php'; // Подключение к базе данных

header('Content-Type: application/json');

// Проверка наличия event_id в запросе
if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ID мероприятия не указан.']);
    exit();
}

$eventId = intval($_GET['event_id']); // Приведение к целому числу

// Получаем данные мероприятия и комментарий
$query = "SELECT title, reason_for_refusal FROM events WHERE event_id = $1;";
$result = pg_query_params($conn, $query, [$eventId]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено.']);
    exit();
}

$eventData = pg_fetch_assoc($result);
echo json_encode([
    'success' => true,
    'title' => $eventData['title'], // Название мероприятия
    'comment' => $eventData['reason_for_refusal'] // Комментарий (причина отказа)
]);

pg_close($conn);
