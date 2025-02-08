<?php
require 'conn.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = $_SESSION['user_id'] ?? null;
    $eventId = $_POST['event_id'] ?? null;

    if (!$userId || !$eventId) {
        echo json_encode(['success' => false, 'message' => 'Ошибка: ID пользователя или мероприятия отсутствует.']);
        exit();
    }

    $stmt = pg_prepare($conn, "check_registration", "SELECT EXISTS(SELECT 1 FROM user_events WHERE user_id = $1 AND event_id = $2);");
    $resultCheckRegistration = pg_execute($conn, "check_registration", [$userId, $eventId]);
    $isRegistered = pg_fetch_result($resultCheckRegistration, 0) === 't';

    if ($isRegistered) {
        $stmt = pg_prepare($conn, "remove_registration", "DELETE FROM user_events WHERE user_id = $1 AND event_id = $2;");
        $resultRemoveRegistration = pg_execute($conn, "remove_registration", [$userId, $eventId]);

        if ($resultRemoveRegistration) {
            echo json_encode(['success' => true, 'message' => 'Вы отписались от мероприятия.', 'action' => 'unregistered']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Произошла ошибка при отмене регистрации.']);
        }
    } else {
        $stmt = pg_prepare($conn, "add_registration", "INSERT INTO user_events(user_id, event_id) VALUES($1, $2);");
        $resultAddRegistration = pg_execute($conn, "add_registration", [$userId, $eventId]);

        if ($resultAddRegistration) {
            echo json_encode(['success' => true, 'message' => 'Вы успешно зарегистрированы на мероприятие.', 'action' => 'registered']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Произошла ошибка при регистрации на мероприятие.']);
        }
    }
}

pg_close($conn);
