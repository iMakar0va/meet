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

try {
    // Обработка данных пользователя из формы
    if (isset($_POST['first_name'], $_POST['last_name'], $_POST['gender'], $_POST['birth_date'])) {
        $firstName = $_POST['first_name'];
        $lastName = $_POST['last_name'];
        $gender = $_POST['gender'];
        $birthDate = $_POST['birth_date'];

        // Проверка на обязательные поля
        if (empty($firstName) || empty($lastName) || empty($birthDate)) {
            echo json_encode(['success' => false, 'message' => 'Пожалуйста, заполните все обязательные поля.']);
            exit();
        }

        // Хеширование пароля, если он был передан
        $password = isset($_POST['password']) && !empty($_POST['password']) ? password_hash($_POST['password'], PASSWORD_DEFAULT) : null;

        // Обновление данных пользователя
        $query = "UPDATE users SET first_name = :first_name, last_name = :last_name, gender = :gender, birth_date = :birth_date WHERE user_id = :user_id";
        $params = [
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':gender' => $gender,
            ':birth_date' => $birthDate,
            ':user_id' => $userId
        ];

        if ($password !== null) {
            $query .= ", password = :password";
            $params[':password'] = $password;
        }

        $stmt = $pdo->prepare($query);
        $stmt->execute($params);

        if ($stmt->rowCount()) {
            $isUpdated = true;
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

        // Обновляем изображение на дефолтное
        $query = "UPDATE users SET image = :image WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':image' => $defaultImageData,
            ':user_id' => $userId
        ]);

        if ($stmt->rowCount()) {
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

        // Обновление изображения пользователя
        $query = "UPDATE users SET image = :image WHERE user_id = :user_id";
        $stmt = $pdo->prepare($query);
        $stmt->execute([
            ':image' => $imageData,
            ':user_id' => $userId
        ]);

        if ($stmt->rowCount()) {
            $_SESSION['user_image'] = $imageData;
            $isUpdated = true;
        }
    }

    // Ответ на запрос
    $response = $isUpdated
        ? ['success' => true, 'message' => 'Данные успешно обновлены.']
        : ['success' => false, 'message' => 'Нет изменений для сохранения.'];

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ' . $e->getMessage()]);
}

exit();
