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

try {
    $sql = "SELECT * FROM users WHERE user_id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([':user_id' => $userId]);

    if ($userData = $stmt->fetch(PDO::FETCH_ASSOC)) {
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
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}

exit();
