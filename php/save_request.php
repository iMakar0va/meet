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
$organizerData = [
    'name' => $_POST["name_organizer"] ?? '',
    'phone' => $_POST["phone"] ?? '',
    'email' => $_POST["email"] ?? '',
    'date_start_work' => $_POST["date_start_work"] ?? '',
    'description' => $_POST["description"] ?? ''
];

// Валидация данных
if (empty($organizerData['name']) || empty($organizerData['phone']) || empty($organizerData['email'])) {
    $response['message'] = 'Все поля должны быть заполнены.';
    echo json_encode($response);
    exit();
}

if (!filter_var($organizerData['email'], FILTER_VALIDATE_EMAIL)) {
    $response['message'] = 'Некорректный формат email.';
    echo json_encode($response);
    exit();
}

try {
    // Вставка организатора
    $insertOrganizerQuery = "
        INSERT INTO organizators(organizator_id, name, phone_number, email, date_start_work, description)
        VALUES($1, $2, $3, $4, $5, $6);
    ";

    $organizerStmt = pg_prepare($conn, "insert_organizer", $insertOrganizerQuery);
    $organizerResult = pg_execute($conn, "insert_organizer", [
        $_SESSION['user_id'],  // организатор из сессии
        $organizerData['name'],
        $organizerData['phone'],
        $organizerData['email'],
        $organizerData['date_start_work'],
        $organizerData['description'],
    ]);

    // Проверка успешности выполнения запроса
    if ($organizerResult) {
        $response['success'] = true;
        $response['message'] = 'Организатор успешно добавлен.';
    } else {
        throw new Exception('Ошибка при сохранении данных. Попробуйте позже.');
    }

} catch (Exception $e) {
    $response['message'] = 'Ошибка: ' . $e->getMessage();
} finally {
    echo json_encode($response);
    pg_close($conn);  // Закрываем соединение с базой данных
}
