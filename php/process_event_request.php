<?php
session_start();
require 'conn.php';
require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if (!isset($_POST['event_id']) || !isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Некорректные данные.']);
    exit();
}

$eventId = $_POST['event_id'];
$action = $_POST['action'];
$reason = $_POST['reason'] ?? '';

// Получаем данные мероприятия
$query = "SELECT title, email FROM events WHERE event_id = $1 AND is_approved = false;";
$result = pg_query_params($conn, $query, [$eventId]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено или уже обработано.']);
    exit();
}

$event = pg_fetch_assoc($result);
$eventTitle = $event['title'];
$organizerEmail = $event['email'];

$mail = new PHPMailer(true);
try {
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = $variables['smtp_username'];
    $mail->Password = $variables['smtp_password'];
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
    $mail->Port = 465;
    $mail->setFrom($variables['smtp_username'], 'MEET');
    $mail->addAddress($organizerEmail);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);

    if ($action === 'approve') {
        pg_query_params($conn, "UPDATE events SET is_approved = true WHERE event_id = $1;", [$eventId]);
        $mail->Subject = "Мероприятие одобрено";
        $mail->Body = "Ваше мероприятие <b>\"$eventTitle\"</b> одобрено и теперь доступно на платформе.";
        echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" одобрено."]);
    } elseif ($action === 'reject') {
        pg_query_params($conn, "UPDATE events SET is_approved = false, is_active = true WHERE event_id = $1;", [$eventId]);
        // pg_query_params($conn, "DELETE FROM organizators_events WHERE event_id = $1;", [$eventId]);
        // pg_query_params($conn, "DELETE FROM events WHERE event_id = $1;", [$eventId]);

        $mail->Subject = "Мероприятие отклонено";
        $mail->Body = "Ваше мероприятие <b>\"$eventTitle\"</b> было отклонено. Причина: <b>$reason</b>.";
        echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" отклонено."]);
    }

    $mail->send();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}

pg_close($conn);
