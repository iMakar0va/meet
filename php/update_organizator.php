<?php
session_start();
require 'conn.php';

// Получаем organizator_id
$organizatorId = $_POST['organizator_id'] ?? $_SESSION['organizator_id'] ?? null;

if (!$organizatorId) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: организатор не найден.']);
    exit();
}

// Получаем данные из формы
$name = $_POST['name'] ?? '';
$phone_number = $_POST['phone_number'] ?? '';
$date_start_work = $_POST['date_start_work'] ?? '';
$description = $_POST['description'] ?? '';

// Проверка обязательных полей
if (empty($name) || empty($phone_number) || empty($date_start_work) || empty($description)) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: все поля должны быть заполнены.']);
    exit();
}

// Обновление данных мероприятия
$query = "UPDATE organizators SET name = $1, phone_number = $2, date_start_work = $3, description = $4 WHERE organizator_id = $5";
$result = pg_query_params($conn, $query, [
    $name,
    $phone_number,
    $date_start_work,
    $description,
    $organizatorId
]);

if (!$result) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при обновлении данных.']);
    exit();
}

echo json_encode(['success' => true, 'message' => 'Данные успешно обновлены.']);
pg_close($conn);  // Закрытие соединения с базой данных
