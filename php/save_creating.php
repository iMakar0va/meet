<?php

require 'conn.php';
session_start();

header('Content-Type: application/json');

$response = ['success' => false];

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $response['message'] = 'Неверный метод запроса.';
    echo json_encode($response);
    exit();
}

// Получаем данные из формы
$eventData = [
    'title' => $_POST["title_event"] ?? '',
    'type' => $_POST["type_event"] ?? '',
    'topic' => $_POST["topic_event"] ?? '',
    'description' => $_POST["desc_event"] ?? '',
    'start_time' => $_POST["start_time"] ?? '',
    'end_time' => $_POST["end_time"] ?? '',
    'event_date' => $_POST["date_event"] ?? '',
    'city' => $_POST["city_event"] ?? '',
    'address' => $_POST["address_event"] ?? '',
    'place' => $_POST["place_event"] ?? '',
    'phone' => $_POST["phone"] ?? '',
];

// Получаем данные организатора из базы данных на основе user_id из сессии
$userId = $_SESSION['user_id'] ?? null;

if ($userId) {
    $getOrganizerQuery = "
        SELECT name, email
        FROM organizators
        WHERE organizator_id = $1
    ";

    $organizerStmt = pg_prepare($conn, "get_organizer", $getOrganizerQuery);
    $organizerResult = pg_execute($conn, "get_organizer", [$userId]);

    if ($organizerResult) {
        $organizerData = pg_fetch_assoc($organizerResult);

        // Если данные организатора найдены, присваиваем их переменным
        if ($organizerData) {
            $organizer = $organizerData['name'];
            $organizerEmail = $organizerData['email'];
        } else {
            $response['message'] = 'Организатор не найден.';
            echo json_encode($response);
            exit();
        }
    } else {
        $response['message'] = 'Ошибка при получении данных организатора.';
        echo json_encode($response);
        exit();
    }
} else {
    $response['message'] = 'Пользователь не авторизован.';
    echo json_encode($response);
    exit();
}

// Заглушка для email, если данные организатора не найдены
// $email = $organizerEmail ?? $_POST["email"] ?? '';

// Проверка изображения
$imageContent = handleImageUpload();

if ($imageContent === false) {
    echo json_encode($response);
    exit();
}

try {
    // Вставка мероприятия
    $insertEventQuery = "
        INSERT INTO events(image, title, type, topic, description, start_time, end_time, event_date, city, address, organizer, place, phone, email)
        VALUES($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, $13, $14)
        RETURNING event_id;
    ";

    $eventStmt = pg_prepare($conn, "insert_event", $insertEventQuery);
    $eventResult = pg_execute($conn, "insert_event", [
        $imageContent,
        $eventData['title'],
        $eventData['type'],
        $eventData['topic'],
        $eventData['description'],
        $eventData['start_time'],
        $eventData['end_time'],
        $eventData['event_date'],
        $eventData['city'],
        $eventData['address'],
        $organizer,
        $eventData['place'],
        $eventData['phone'],
        $email
    ]);

    // Получаем ID вставленного события
    $eventRow = pg_fetch_assoc($eventResult);
    $eventId = $eventRow['event_id'];

    // Вставка связи между мероприятием и организатором
    $insertOrganizerEventQuery = "
        INSERT INTO organizators_events(event_id, organizator_id)
        VALUES ($1, $2);
    ";

    $organizerStmt = pg_prepare($conn, "insert_organizator_event", $insertOrganizerEventQuery);
    $organizerResult = pg_execute($conn, "insert_organizator_event", [
        $eventId,
        $userId // организатор из сессии
    ]);

    if ($eventResult && $organizerResult) {
        $response['success'] = true;
        $response['message'] = 'Мероприятие успешно создано.';
    } else {
        throw new Exception('Ошибка при сохранении данных в базу.');
    }

} catch (Exception $e) {
    $response['message'] = 'Ошибка: ' . $e->getMessage();
} finally {
    echo json_encode($response);
    pg_close($conn); // Закрываем соединение с базой данных
}

/**
 * Обрабатывает загрузку изображения
 */
function handleImageUpload()
{
    global $conn, $response;

    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        // Разрешенные типы файлов
        $allowedTypes = ['image/jpeg', 'image/jpg', 'image/png'];
        $maxSize = 10 * 1024 * 1024; // 10 MB

        // Проверка формата файла
        if (!in_array($_FILES['file']['type'], $allowedTypes)) {
            $response['message'] = 'Формат изображения должен быть JPEG, JPG или PNG.';
            return false;
        }

        // Проверка размера файла
        if ($_FILES['file']['size'] > $maxSize) {
            $response['message'] = 'Размер изображения не должен превышать 10 МБ.';
            return false;
        }

        // Чтение содержимого файла
        $fileContent = file_get_contents($_FILES['file']['tmp_name']);
        if ($fileContent === false) {
            $response['message'] = 'Ошибка чтения содержимого файла.';
            return false;
        }

        return pg_escape_bytea($conn, $fileContent);
    } else {
        // Загрузка дефолтного изображения, если файл не был выбран
        $defaultImagePath = '../img/event_fon.jpg';
        if (file_exists($defaultImagePath)) {
            $defaultImageContent = file_get_contents($defaultImagePath);
            if ($defaultImageContent === false) {
                $response['message'] = 'Ошибка чтения дефолтного изображения.';
                return false;
            }
            return pg_escape_bytea($conn, $defaultImageContent);
        } else {
            $response['message'] = 'Дефолтное изображение не найдено.';
            return false;
        }
    }
}
