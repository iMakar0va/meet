<?php
require 'conn.php';
require '../php-jwt-main/src/JWT.php'; // Подключаем библиотеку JWT
require '../php-jwt-main/src/JWK.php'; // Подключаем библиотеку JWT
require '../php-jwt-main/src/Key.php'; // Подключаем библиотеку JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

header('Content-Type: application/json');
$secret_key = "1a2s3d4f5g"; // Задай свой секретный ключ

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email и пароль обязательны для входа.']);
        exit;
    }

    $getUserQuery = "SELECT user_id, password FROM users WHERE email = $1";
    $resultGetUser = pg_query_params($conn, $getUserQuery, [$email]);

    if ($resultGetUser && pg_num_rows($resultGetUser) > 0) {
        $row = pg_fetch_assoc($resultGetUser);
        $hashedPasswordFromDb = $row['password'];

        if (password_verify($password, $hashedPasswordFromDb)) {
            $userId = $row['user_id'];

            // Генерируем JWT
            $payload = [
                'user_id' => $userId,
                'email' => $email,
                'exp' => time() + (60 * 60 * 24 * 7) // Токен на 7 дней
            ];
            $jwt = JWT::encode($payload, $secret_key, 'HS256');
            setcookie("token", $jwt, time() + (60 * 60 * 24 * 7), "/", "", false, true);


            echo json_encode(['success' => true, 'token' => $jwt]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Неверный пароль.']);
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email не найден.']);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса.']);
}
