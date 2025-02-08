<?php
require 'conn.php';
session_start();

$userId = $_SESSION['user_id'] ?? null;

if (!$userId) {
    echo json_encode([
        'success' => false,
        'message' => 'Пользователь не авторизован.'
    ]);
    exit();
}

$sql = "SELECT * FROM users WHERE user_id = $1";
$queryResult = pg_query_params($conn, $sql, [$userId]);

if ($userData = pg_fetch_assoc($queryResult)) {
    echo json_encode([
        'success' => true,
        'data' => $userData
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Не удалось получить данные пользователя.'
    ]);
}

exit();
