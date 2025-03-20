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

    // Проверяем, есть ли запись о пользователе на мероприятии
    $stmtCheck = pg_prepare(
        $conn,
        "check_registration",
        "SELECT is_signed FROM user_events WHERE user_id = $1 AND event_id = $2;"
    );
    $resultCheck = pg_execute($conn, "check_registration", [$userId, $eventId]);
    $row = pg_fetch_assoc($resultCheck);

    if ($row) {
        // Если пользователь уже есть в БД
        if ($row['is_signed'] === 't') {
            // Если он уже записан, делаем посещение false (отписка)
            $stmtUpdate = pg_prepare(
                $conn,
                "unsubscribe_user",
                "UPDATE user_events SET is_signed = FALSE WHERE user_id = $1 AND event_id = $2;"
            );
            $resultUpdate = pg_execute($conn, "unsubscribe_user", [$userId, $eventId]);

            if ($resultUpdate) {
                echo json_encode(['success' => true, 'message' => 'Вы отписались от мероприятия.', 'action' => 'unregistered']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при отмене регистрации.']);
            }
        } else {
            // Если он есть в БД, но не записан, делаем is_signed = TRUE (восстанавливаем регистрацию)
            $stmtRestore = pg_prepare(
                $conn,
                "restore_registration",
                "UPDATE user_events SET is_signed = TRUE WHERE user_id = $1 AND event_id = $2;"
            );
            $resultRestore = pg_execute($conn, "restore_registration", [$userId, $eventId]);

            if ($resultRestore) {
                echo json_encode(['success' => true, 'message' => 'Вы снова зарегистрированы на мероприятие.', 'action' => 'registered']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Ошибка при восстановлении регистрации.']);
            }
        }
    } else {
        // Если записи нет — создаём новую
        $stmtInsert = pg_prepare(
            $conn,
            "register_user",
            "INSERT INTO user_events(user_id, event_id, is_signed) VALUES($1, $2, TRUE);"
        );
        $resultInsert = pg_execute($conn, "register_user", [$userId, $eventId]);

        if ($resultInsert) {
            echo json_encode(['success' => true, 'message' => 'Вы успешно зарегистрированы на мероприятие.', 'action' => 'registered']);
        } else {
            echo json_encode(['success' => false, 'message' => 'Ошибка при регистрации на мероприятие.']);
        }
    }
}

pg_close($conn);
