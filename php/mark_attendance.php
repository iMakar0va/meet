<?php
require 'conn.php'; // Подключаем БД

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_POST['user_id'] ?? null;
    $eventId = $_POST['event_id'] ?? null;

    if (!$userId || !$eventId) {
        echo "❌ Ошибка: не хватает данных.";
        exit;
    }

    // Проверяем, записан ли пользователь на мероприятие
    $checkQuery = "SELECT * FROM user_events WHERE user_id = $1 AND event_id = $2";
    $checkResult = pg_query_params($conn, $checkQuery, [$userId, $eventId]);

    if (pg_num_rows($checkResult) === 0) {
        echo "⚠ Пользователь не зарегистрирован на это мероприятие!";
        exit;
    }

    // Отмечаем присутствие пользователя
    $updateQuery = "UPDATE user_events SET presense = TRUE WHERE user_id = $1 AND event_id = $2";
    $updateResult = pg_query_params($conn, $updateQuery, [$userId, $eventId]);

    if ($updateResult) {
        echo "✅ Успешно! Пользователь " . htmlspecialchars($userId) . " отмечен на мероприятии.";
    } else {
        echo "❌ Ошибка обновления: " . pg_last_error($conn);
    }
}
