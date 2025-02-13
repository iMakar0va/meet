<?php
require 'conn.php';  // Подключение через PDO
session_start();

// Проверяем, что данные пришли через POST и в формате JSON
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем email и пароль из JSON
    $email = $input['email'] ?? '';
    $password = $input['password'] ?? '';

    // Проверка наличия данных
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Email и пароль обязательны для входа.']);
        exit;
    }

    try {
        // Подготовленное выражение для безопасного запроса
        $sql = "SELECT user_id, password FROM users WHERE email = :email";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':email', $email, PDO::PARAM_STR);
        $stmt->execute();

        // Проверка, если пользователь найден в базе
        if ($stmt->rowCount() > 0) {
            // Получаем данные из результата запроса
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $hashedPasswordFromDb = $row['password'];

            // Проверка пароля
            if (password_verify($password, $hashedPasswordFromDb)) {
                // Получаем ID пользователя
                $userId = $row['user_id'];

                // Создаем новую сессию и устанавливаем cookie
                session_regenerate_id(true); // Обновляем ID сессии для защиты
                $_SESSION['user_id'] = $userId;
                setcookie("user_id", $userId, time() + 3600 * 24 * 30, "/"); // cookie на 30 дней

                // Возвращаем успешный ответ
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Неверный пароль.']);
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Пользователь с таким email не найден.']);
        }
    } catch (PDOException $e) {
        // Обработка ошибок
        echo json_encode(['success' => false, 'message' => 'Ошибка при подключении к базе данных: ' . $e->getMessage()]);
    }
} else {
    // Если не POST запрос
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса.']);
}
?>
