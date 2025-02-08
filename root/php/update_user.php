<?php

require 'conn.php';
session_start();

// Проверка авторизации пользователя
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Пользователь не авторизован.']);
    exit();
}

$userId = $_SESSION['user_id'];
$isUpdated = false;

// Обработка данных пользователя из формы
if (isset($_POST['first_name'], $_POST['last_name'], $_POST['password'], $_POST['gender'], $_POST['birth_date'])) {
    $firstName = $_POST['first_name'];
    $lastName = $_POST['last_name'];
    $password = $_POST['password'];
    $gender = $_POST['gender'];
    $birthDate = $_POST['birth_date'];

    // Хеширование пароля
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Проверка на обязательные поля
    if (empty($firstName) || empty($lastName) || empty($birthDate)) {
        $isUpdated = false;
    } else {
        // Обновление данных пользователя
        if (empty($password)) {
            // Если пароль не изменяется, обновляем только другие данные
            $query = "UPDATE users SET first_name = $1, last_name = $2, gender = $3, birth_date = $4 WHERE user_id = $5";
            $result = pg_query_params($conn, $query, [$firstName, $lastName, $gender, $birthDate, $userId]);
        } else {
            // Если пароль изменяется, обновляем все поля
            $query = "UPDATE users SET first_name = $1, last_name = $2, password = $3, gender = $4, birth_date = $5 WHERE user_id = $6";
            $result = pg_query_params($conn, $query, [$firstName, $lastName, $hashedPassword, $gender, $birthDate, $userId]);
        }

        // Если запрос выполнен успешно
        if ($result) {
            $isUpdated = true;
        }
    }
}

// Обработка изображения
if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
    // Удаление изображения и восстановление дефолтного
    $defaultImagePath = '../img/profile.jpg';
    $defaultImageData = file_get_contents($defaultImagePath);

    if ($defaultImageData === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении дефолтного изображения.']);
        exit();
    }

    $escapedImageData = pg_escape_bytea($conn, $defaultImageData);

    // Обновляем изображение на дефолтное
    $query = "UPDATE users SET image = $1 WHERE user_id = $2";
    $result = pg_query_params($conn, $query, [$escapedImageData, $userId]);

    if ($result) {
        $_SESSION['user_image'] = $defaultImageData;
        $isUpdated = true;
    }
} elseif (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // Обработка загрузки изображения
    $file = $_FILES['file'];

    // Проверка типа и размера изображения
    $allowedTypes = ['image/jpeg', 'image/png', 'image/jpg'];
    $maxFileSize = 10 * 1024 * 1024; // 10 МБ

    if (!in_array($file['type'], $allowedTypes)) {
        echo json_encode(['success' => false, 'message' => 'Формат изображения должен быть JPEG, JPG или PNG.']);
        exit();
    }

    if ($file['size'] > $maxFileSize) {
        echo json_encode(['success' => false, 'message' => 'Размер изображения не должен превышать 10 МБ.']);
        exit();
    }

    // Чтение содержимого изображения
    $imageData = file_get_contents($file['tmp_name']);
    if ($imageData === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении содержимого изображения.']);
        exit();
    }

    // Экранирование изображения перед записью в базу данных
    $escapedImageData = pg_escape_bytea($conn, $imageData);

    // Обновление изображения пользователя
    $query = "UPDATE users SET image = $1 WHERE user_id = $2";
    $result = pg_query_params($conn, $query, [$escapedImageData, $userId]);

    if ($result) {
        $_SESSION['user_image'] = $imageData;
        $isUpdated = true;
    }
}

// Ответ на запрос
$response = $isUpdated
    ? ['success' => true, 'message' => 'Данные успешно обновлены.']
    : ['success' => false, 'message' => 'Нет изменений для сохранения.'];

echo json_encode($response);

// Закрытие соединения с базой данных
pg_close($conn);
