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
$reason = $_POST['reason'] ?? NULL;

// Получаем данные мероприятия
$query = "SELECT title, email, event_date FROM events WHERE event_id = $1 AND is_approved = false;";
$result = pg_query_params($conn, $query, [$eventId]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено или уже обработано.']);
    exit();
}

$event = pg_fetch_assoc($result);
$eventTitle = $event['title'];
$organizerEmail = $event['email'];
$eventDate = $event['event_date'];

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
        pg_query_params($conn, "UPDATE events SET is_active = true, is_approved = true, reason_for_refusal = NULL, is_repeated = false WHERE event_id = $1;", [$eventId]);
        $mail->Subject = 'Ваше мероприятие одобрено';

        // HTML-тело письма
        $mail->Body = "
    <html>
    <head>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                color: #333;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 10px;
                background-color: #f9f9f9;
            }
            h1 {
                color: #4CAF50;
                font-size: 24px;
                margin-bottom: 20px;
            }
            p {
                margin: 10px 0;
            }
            .footer {
                margin-top: 20px;
                font-size: 12px;
                color: #777;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <h1>Ваше мероприятие одобрено</h1>
            <p>Уважаемый организатор,</p>
            <p>Мы рады сообщить, что ваше мероприятие <b>\"{$eventTitle}\"</b> было успешно одобрено.</p>
            <p><b>Дата мероприятия:</b> {$eventDate}</p>
            <p>Благодарим вас за ваш вклад и желаем успешного проведения мероприятия!</p>
            <div class='footer'>
                <p>С уважением,<br>Команда MEET</p>
            </div>
        </div>
    </body>
    </html>
";

        // Альтернативное текстовое тело для клиентов, которые не поддерживают HTML
        $mail->AltBody = "Уважаемый организатор,\n\n"
            . "Мы рады сообщить, что ваше мероприятие \"{$eventTitle}\" было успешно одобрено.\n"
            . "Дата мероприятия: {$eventDate}\n\n"
            . "Благодарим вас за ваш вклад и желаем успешного проведения мероприятия!\n\n"
            . "С уважением,\nКоманда MEET";

        echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" одобрено."]);
    } elseif ($action === 'reject') {
        pg_query_params($conn, "UPDATE events SET is_active = false, is_approved = true, reason_for_refusal = $1, is_repeated = false  WHERE event_id = $2;", [$reason, $eventId]);

        $mail->Subject = "Мероприятие отклонено";
        $mail->Body = "Ваше мероприятие <b>\"$eventTitle\"</b> было отклонено. Причина: <b>$reason</b>.";
        echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" отклонено."]);
    }

    $mail->send();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}

pg_close($conn);
