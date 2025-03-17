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
$query = "SELECT * FROM events WHERE event_id = $1 AND is_approved = false;";
$result = pg_query_params($conn, $query, [$eventId]);

if (!$result || pg_num_rows($result) === 0) {
    echo json_encode(['success' => false, 'message' => 'Мероприятие не найдено или уже обработано.']);
    exit();
}

$event = pg_fetch_assoc($result);
$eventTitle = $event['title'];
$organizerEmail = $event['email'];
$eventDate = $event['event_date'];

// Получаем email всех зарегистрированных участников
$participantsQuery = "SELECT u.email FROM users u
       JOIN user_events ue ON u.user_id = ue.user_id
       WHERE ue.event_id = $1";
$participantsResult = pg_query_params($conn, $participantsQuery, [$eventId]);
$participantsEmails = [];
while ($participant = pg_fetch_assoc($participantsResult)) {
    $participantsEmails[] = $participant['email'];
}
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
        $mail->clearAddresses();
        $mail->addAddress($organizerEmail);
        $mail->Subject = 'Уведомление об одобрении мероприятия';

        // HTML-тело письма
        $mail->Body = "
            <h1>Подтверждение одобрения мероприятия</h1>
            <p>Уважаемый организатор,</p>
            <p>Ваше мероприятие <strong>\"{$eventTitle}\"</strong> успешно одобрено.</p>
            <p><strong>Дата проведения:</strong> {$eventDate}</p>
            <p>Благодарим вас за ваш вклад в развитие сообщества и желаем успешного проведения мероприятия!</p>
            <p>С уважением,</p>
            <p>Команда MEET</p>
        ";

        // Альтернативное текстовое тело для почтовых клиентов без поддержки HTML
        $mail->AltBody = "Уважаемый организатор,\n\n"
            . "Ваше мероприятие \"{$eventTitle}\" успешно одобрено.\n"
            . "Дата проведения: {$eventDate}\n\n"
            . "Благодарим вас за ваш вклад в развитие сообщества и желаем успешного проведения мероприятия!\n\n"
            . "С уважением,\nКоманда MEET";

        $mail->send();
        echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" одобрено."]);

        // Уведомление участникам без указания причины
        foreach ($participantsEmails as $participantEmail) {
            $mail->clearAddresses();
            $mail->addAddress($participantEmail);
            $mail->Subject = 'Уведомление о доступном мероприятии';

            // HTML-тело письма
            $mail->Body = "
                <h1>Уведомление о доступном мероприятии</h1>
                <p>Уважаемый участник,</p>
                <p>С радостью сообщаем вам, что мероприятие <strong>\"{$eventTitle}\"</strong>, которое ранее было отменено, снова доступно. Вы по-прежнему записаны на него. В случае необходимости отмены записи, вы можете сделать это в личном кабинете.</p>
                <p><strong>Дата мероприятия:</strong> {$eventDate}</p>
                <p>Если у вас возникнут вопросы, пожалуйста, не стесняйтесь связаться с нашей командой.</p>
                <p>С уважением,</p>
                <p>Команда MEET</p>
            ";

            // Альтернативное текстовое тело для почтовых клиентов без поддержки HTML
            $mail->AltBody = "Уважаемый участник,\n\n"
                . "С радостью сообщаем вам, что мероприятие \"{$eventTitle}\", которое ранее было отменено, снова доступно. Вы по-прежнему записаны на него. В случае необходимости отмены записи, вы можете сделать это в личном кабинете.\n\n"
                . "Дата мероприятия: {$eventDate}\n\n"
                . "Если у вас возникнут вопросы, пожалуйста, не стесняйтесь связаться с нашей командой.\n\n"
                . "С уважением,\nКоманда MEET";
            $mail->send();
        }
    } elseif ($action === 'reject') {
        pg_query_params($conn, "UPDATE events SET is_active = false, is_approved = true, reason_for_refusal = $1, is_repeated = false  WHERE event_id = $2;", [$reason, $eventId]);

        $mail->Subject = "Уведомление об отклонении мероприятия";

        // HTML-тело письма
        $mail->Body = "
            <h1>Ваше мероприятие отклонено</h1>
            <p>Уважаемый организатор,</p>
            <p>К сожалению, ваше мероприятие <strong>\"{$eventTitle}\"</strong> было отклонено.</p>
            <p><strong>Причина:</strong> {$reason}</p>
            <p>Если у вас есть вопросы, пожалуйста, свяжитесь с администрацией.</p>
            <p>С уважением,</p>
            <p>Команда MEET</p>
        ";

        // Альтернативное текстовое тело для почтовых клиентов без поддержки HTML
        $mail->AltBody = "Уважаемый организатор,\n\n"
            . "К сожалению, ваше мероприятие \"{$eventTitle}\" было отклонено.\n"
            . "Причина: {$reason}\n\n"
            . "Если у вас есть вопросы, пожалуйста, свяжитесь с администрацией.\n\n"
            . "С уважением,\nКоманда MEET";
        $mail->send();
        echo json_encode(['success' => true, 'message' => "Мероприятие \"$eventTitle\" отклонено."]);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}

pg_close($conn);
