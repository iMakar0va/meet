<?php
require 'conn.php';  // Подключение к базе данных через PDO
session_start();

// Проверка на POST запрос
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Получаем данные из формы
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Проверка наличия данных
    if (empty($email) || empty($password)) {
        echo "Email и пароль обязательны для входа.";
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

                // Перенаправляем на главную страницу или страницу профиля
                header("Location: /dashboard.php"); // Убедитесь, что файл существует
                exit;
            } else {
                echo "Неверный пароль.";
            }
        } else {
            echo "Пользователь с таким email не найден.";
        }
    } catch (PDOException $e) {
        // Обработка ошибок
        echo "Ошибка при подключении к базе данных: " . $e->getMessage();
    }
} else {
    // Если не POST запрос
    echo "Неверный метод запроса.";
}
?>
