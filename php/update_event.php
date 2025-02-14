<?php
session_start();
require 'conn.php';

// Получаем event_id
$eventId = $_POST['event_id'] ?? $_SESSION['event_id'] ?? null;

if (!$eventId) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: мероприятие не найдено.']);
    exit();
}

// Получаем данные из формы
$title = $_POST['title_event'] ?? '';
$type = $_POST['type_event'] ?? '';
$topic = $_POST['topic_event'] ?? '';
$description = $_POST['desc_event'] ?? '';
$eventDate = $_POST['date_event'] ?? '';
$startTime = $_POST['start_time'] ?? '';
$endTime = $_POST['end_time'] ?? '';
$city = $_POST['city_event'] ?? '';
$place = $_POST['place_event'] ?? '';
$address = $_POST['address_event'] ?? '';
$phone = $_POST['phone'] ?? '';
$email = $_POST['email'] ?? '';

// Проверка обязательных полей
if (empty($title) || empty($type) || empty($description) || empty($eventDate) || empty($city)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: все поля должны быть заполнены.']);
    exit();
}

// Обновление данных мероприятия
$query = "UPDATE events SET title = $1, type = $2, topic = $3, description = $4, event_date = $5, start_time = $6, end_time = $7, city = $8, place = $9, address = $10, phone = $11, email = $12 WHERE event_id = $13";
$result = pg_query_params($conn, $query, [
    $title,
    $type,
    $topic,
    $description,
    $eventDate,
    $startTime,
    $endTime,
    $city,
    $place,
    $address,
    $phone,
    $email,
    $eventId
]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении данных.']);
    exit();
}

// Обработка изображения
if (isset($_POST['remove_image']) && $_POST['remove_image'] === '1') {
    // Восстановление дефолтного изображения
    $defaultImage = file_get_contents('../img/event_fon.jpg');
    if ($defaultImage === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при чтении дефолтного изображения.']);
        exit();
    }
    $escapedData = pg_escape_bytea($conn, $defaultImage);
    $query = "UPDATE events SET image = $1 WHERE event_id = $2";
    $result = pg_query_params($conn, $query, [$escapedData, $eventId]);

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
    $query = "UPDATE events SET image = $1 WHERE event_id = $2";
    $result = pg_query_params($conn, $query, [$escapedData, $eventId]);

    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении изображения.']);
        exit();
    }
}

echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены.']);
pg_close($conn);  // Закрытие соединения с базой данных
