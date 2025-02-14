<?php
session_start();
require 'conn.php';

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован.']);
    exit();
}

$userId = $_SESSION['user_id'];
$isUpdated = false;

// Получаем данные из формы
$firstName = $_POST['first_name'] ?? null;
$lastName = $_POST['last_name'] ?? null;
$password = $_POST['password'] ?? null;
$gender = $_POST['gender'] ?? null;
$birthDate = $_POST['birth_date'] ?? null;

// ✅ Преобразуем дату в формат `YYYY-MM-DD`
if (!empty($birthDate)) {
    $dateParts = explode('/', $birthDate);
    if (count($dateParts) === 3) {
        $birthDate = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
    }
}

// Обновляем только изменённые данные
$updateFields = [];
$params = [];
$paramIndex = 1;

if (!empty($firstName)) {
    $updateFields[] = "first_name = $" . $paramIndex++;
    $params[] = $firstName;
}

if (!empty($lastName)) {
    $updateFields[] = "last_name = $" . $paramIndex++;
    $params[] = $lastName;
}

if (!empty($password)) {
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $updateFields[] = "password = $" . $paramIndex++;
    $params[] = $hashedPassword;
}

if (!empty($gender)) {
    $updateFields[] = "gender = $" . $paramIndex++;
    $params[] = $gender;
}

if (!empty($birthDate)) {
    $updateFields[] = "birth_date = $" . $paramIndex++;
    $params[] = $birthDate;
}

// Выполняем обновление только если есть изменения
if (!empty($updateFields)) {
    $params[] = $userId;
    $query = "UPDATE users SET " . implode(', ', $updateFields) . " WHERE user_id = $" . $paramIndex;
    $result = pg_query_params($conn, $query, $params);

    if ($result) {
        $isUpdated = true;
    }
}

// ✅ Обработка удаления изображения
if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
    $defaultImagePath = '../img/profile.jpg';
    $defaultImageData = file_get_contents($defaultImagePath);

    if ($defaultImageData !== false) {
        $escapedImageData = pg_escape_bytea($conn, $defaultImageData);
        $query = "UPDATE users SET image = $1 WHERE user_id = $2";
        $result = pg_query_params($conn, $query, [$escapedImageData, $userId]);

        if ($result) {
            $_SESSION['user_image'] = $defaultImageData;
            $isUpdated = true;
        }
    }
}

// ✅ Обработка загрузки изображения
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['file'];
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxFileSize = 10 * 1024 * 1024; // 10 МБ

    if (in_array($file['type'], $allowedTypes) && $file['size'] <= $maxFileSize) {
        $imageData = file_get_contents($file['tmp_name']);
        if ($imageData !== false) {
            $escapedImageData = pg_escape_bytea($conn, $imageData);
            $query = "UPDATE users SET image = $1 WHERE user_id = $2";
            $result = pg_query_params($conn, $query, [$escapedImageData, $userId]);

            if ($result) {
                $_SESSION['user_image'] = $imageData;
                $isUpdated = true;
            }
        }
    }
}

// ✅ Отправка ответа
$response = $isUpdated
    ? ['success' => true, 'message' => 'Данные успешно обновлены.']
    : ['success' => false, 'message' => 'Нет изменений для сохранения.'];

echo json_encode($response);

// Закрываем соединение
pg_close($conn);
