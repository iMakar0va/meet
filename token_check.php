<?php
require 'php-jwt-main/src/JWT.php'; // Подключаем библиотеку JWT
require 'php-jwt-main/src/JWK.php'; // Подключаем библиотеку JWT
require 'php-jwt-main/src/Key.php'; // Подключаем библиотеку JWT

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

$secret_key = "1a2s3d4f5g"; // Тот же ключ, что и при генерации

// header('Content-Type: application/json');
header('Content-Type: text/html; charset=UTF-8');

if (!isset($_COOKIE['token'])) {
    echo json_encode(['success' => false, 'message' => 'Нет токена']);
    header("Location: auth.php");
    exit();
}

try {
    $jwt = $_COOKIE['token'];
    $decoded = JWT::decode($jwt, new Key($secret_key, 'HS256'));

    $userId = $decoded->user_id;
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка валидации токена']);
    header("Location: auth.php");
    exit();
}
?>