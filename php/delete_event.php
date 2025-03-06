<?php
session_start();
require 'conn.php'; // Подключение к базе данных
require 'autoload.php'; // Автозагрузка классов
require 'variables.php'; // Настройки SMTP

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

// Проверка наличия event_id в запросе
if (!isset($_GET['event_id'])) {
    echo json_encode(['success' => false, 'message' => 'Ошибка: ID мероприятия не указан.']);
    exit();
}

$eventId = intval($_GET['event_id']); // Приведение к целому числу

// Получаем данные мероприятия перед удалением
$query = "SELECT title, email FROM events WHERE event_id = $1;";
$result = pg_query_params($conn, $query, [$eventId]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено.']);
    exit();
}

$eventData = pg_fetch_assoc($result);
$eventTitle = $eventData['title'];
$organizerEmail = $eventData['email'];

// Удаляем мероприятие из таблицы organizators_events
$deleteOrganizersQuery = "DELETE FROM organizators_events WHERE event_id = $1;";
$deleteOrganizersResult = pg_query_params($conn, $deleteOrganizersQuery, [$eventId]);

if (!$deleteOrganizersResult) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении данных организатора.']);
    exit();
}

// Удаляем мероприятие из таблицы events
$deleteEventQuery = "DELETE FROM events WHERE event_id = $1;";
$deleteEventResult = pg_query_params($conn, $deleteEventQuery, [$eventId]);

if (!$deleteEventResult) {
    echo json_encode(['success' => false, 'message' => 'Ошибка при удалении мероприятия.']);
    exit();
}

// Отправляем уведомление организатору на email
$mail = new PHPMailer(true);

try {
    // Настройки SMTP
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = $variables['smtp_username'];
    $mail->Password = $variables['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;

    // Отправитель и получатель
    $mail->setFrom($variables['smtp_username'], 'MEET');
    $mail->addAddress($organizerEmail);

    // Содержание письма
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = "Мероприятие удалено";
    $mail->Body = "Ваше мероприятие <b>\"$eventTitle\"</b> удалено.";

    // Отправка письма
    $mail->send();

    // Успешный ответ
    echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" удалено."]);
} catch (Exception $e) {
    // Ошибка при отправке письма
    echo json_encode(['success' => false, 'message' => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}

// Закрытие соединения с базой данных
pg_close($conn);