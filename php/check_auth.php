<?php

require 'conn.php';
session_start();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    // Проверка наличия данных
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email и пароль обязательны для входа.']);
        exit;
    }

    // Подготовленные выражения для безопасных запросов
    $getUserQuery = "SELECT user_id, password FROM users WHERE email = $1";
    $resultGetUser = pg_query_params($conn, $getUserQuery, [$email]);

    if ($resultGetUser && pg_num_rows($resultGetUser) > 0) {
        // Получаем хеш пароля из базы данных
        $row = pg_fetch_assoc($resultGetUser);
        $hashedPasswordFromDb = $row['password'];

        if (password_verify($password, $hashedPasswordFromDb)) {
            // Получаем ID пользователя
            $userId = $row['user_id'];

            // Создаем новую сессию и устанавливаем cookie
            session_regenerate_id(true); // Обновляем ID сессии для защиты
            $_SESSION['user_id'] = $userId;

            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверный пароль.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email не найден.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса.']);
}
