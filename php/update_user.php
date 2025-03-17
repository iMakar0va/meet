<?php
session_start();
require 'conn.php';

require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Получаем user_id
$userId = $_POST['user_id'] ?? null;

if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: пользователь не найден.']);
    exit();
}

// Получаем данные из формы
$lastName = $_POST['last_name'] ?? '';
$firstName = $_POST['first_name'] ?? '';
$birthDate = $_POST['birth_date'] ?? '';
$gender = $_POST['gender'] ?? '';

$oldPassword = $_POST['old_password'] ?? '';
$newPassword = $_POST['new_password'] ?? '';


// Проверка обязательных полей
if (empty($lastName) || empty($firstName) || empty($birthDate) || empty($gender)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: все поля должны быть заполнены.']);
    exit();
}

// Обновление данных мероприятия
$query = "UPDATE users SET last_name = $1, first_name = $2, birth_date = $3, gender = $4 WHERE user_id = $5";
$result = pg_query_params($conn, $query, [
    $lastName,
    $firstName,
    $birthDate,
    $gender,
    $userId
]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении данных.']);
    exit();
}
// Проверка и обновление пароля
if (!empty($oldPassword) && !empty($newPassword)) {
    // Получаем текущий хэш пароля из базы
    $passwordQuery = "SELECT password FROM users WHERE user_id = $1";
    $passwordResult = pg_query_params($conn, $passwordQuery, [$userId]);

    if ($passwordResult && $row = pg_fetch_assoc($passwordResult)) {
        $hashedPassword = $row['password'];

        // Проверяем соответствие старого пароля
        if (password_verify($oldPassword, $hashedPassword)) {
            // Хэшируем новый пароль
            $newHashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Обновляем пароль в базе данных
            $updatePasswordQuery = "UPDATE users SET password = $1 WHERE user_id = $2";
            $updatePasswordResult = pg_query_params($conn, $updatePasswordQuery, [$newHashedPassword, $userId]);

            if (!$updatePasswordResult) {
                echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении пароля.']);
                exit();
            }
        } else {
            echo json_encode(['success' => false, 'message' => 'Cтарый пароль введён неверно.']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при получении пароля из базы.']);
        exit();
    }
}

// Обработка изображения
if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
    // Восстановление дефолтного изображения
    $defaultImage = file_get_contents('../img/profile.jpg');
    if ($defaultImage === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении дефолтного изображения.']);
        exit();
    }
    $escapedData = pg_escape_bytea($conn, $defaultImage);
    $query = "UPDATE users SET image = $1 WHERE user_id = $2";
    $result = pg_query_params($conn, $query, [$escapedData, $userId]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при восстановлении изображения.']);
        exit();
    }
} elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Проверка и загрузка нового изображения
    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxSize = 10 * 1024 * 1024; // 10 МБ

    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Формат изображения должен быть JPEG, JPG или PNG.']);
        exit();
    }

    if ($file['size'] > $maxSize) {
        echo json_encode(['success' => false, 'message' => 'Размер изображения не должен превышать 10 МБ.']);
        exit();
    }

    // Чтение и сохранение изображения
    $imageData = file_get_contents($file['tmp_name']);
    if ($imageData === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении содержимого изображения.']);
        exit();
    }

    $escapedData = pg_escape_bytea($conn, $imageData);
    $query = "UPDATE users SET image = $1 WHERE user_id = $2";
    $result = pg_query_params($conn, $query, [$escapedData, $userId]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении изображения.']);
        exit();
    }
}

echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены.']);
pg_close($conn);
