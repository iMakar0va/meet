<?php
require 'conn.php';
require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;
    $reason = trim($_POST['reason'] ?? ''); // Получаем причину отмены

    if (!$eventId) {
        $response['message'] = 'Отсутствует ID мероприятия';
        echo json_encode($response);
        exit;
    }

    $toggleQuery = "UPDATE events
                    SET
                        is_active = CASE
                                        WHEN is_active = true THEN false
                                        ELSE is_active
                                    END,
                        is_approved = CASE
                                        WHEN is_active = false THEN NOT is_approved
                                        ELSE is_approved
                                    END
                        WHERE event_id = $1
                        RETURNING is_active, title, event_date;";

    $result = pg_query_params($conn, $toggleQuery, [$eventId]);

    if ($result && $row = pg_fetch_assoc($result)) {
        $newStatus = $row['is_active'] === 't';
        $eventName = $row['title'];
        $eventDate = $row['event_date'];

        // Получаем email организатора
        $emailQuery = "SELECT u.email FROM users u
                       JOIN organizators_events oe ON u.user_id = oe.organizator_id
                       WHERE oe.event_id = $1";
        $emailResult = pg_query_params($conn, $emailQuery, [$eventId]);
        $organizerEmail = ($emailResult && $userData = pg_fetch_assoc($emailResult)) ? $userData['email'] : null;

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
            $mail->Host       = 'smtp.yandex.ru';
            $mail->SMTPAuth   = true;
            $mail->Username   = $variables['smtp_username'];
            $mail->Password   = $variables['smtp_password'];
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;
            $mail->setFrom($variables['smtp_username'], 'MEET');
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);

            if ($newStatus) {
                // Уведомление об одобрении мероприятия
                if ($organizerEmail) {
                    $mail->clearAddresses();
                    $mail->addAddress($organizerEmail, 'Организатор');
                    $mail->Subject = 'Ваше мероприятие одобрено';
                    $mail->Body = "<b>Ваше мероприятие \"{$eventName}\" одобрено.</b><br>Дата: {$eventDate}";
                    $mail->send();
                }
            } else {
                // Уведомление об отмене мероприятия
                if ($organizerEmail) {
                    $mail->clearAddresses();
                    $mail->addAddress($organizerEmail, 'Организатор');
                    $mail->Subject = 'Ваше мероприятие отменено';
                    $mail->Body = "<b>Ваше мероприятие \"{$eventName}\" отменено по решению администратора платформы.</b><br>Причина: <i>{$reason}</i><br>Дата: {$eventDate}";
                    $mail->send();
                }

                // Уведомление участникам без указания причины
                foreach ($participantsEmails as $participantEmail) {
                    $mail->clearAddresses();
                    $mail->addAddress($participantEmail);
                    $mail->Subject = 'Мероприятие отменено';
                    $mail->Body = "<b>Мероприятие \"{$eventName}\" было отменено по решению администратора платформы.</b><br>Дата: {$eventDate}";
                    $mail->send();
                }
            }

            $response['success'] = true;
            $response['newStatus'] = $newStatus ? 'true' : 'false';
            $response['message'] = $newStatus
                ? 'Мероприятие одобрено, уведомления отправлены'
                : 'Мероприятие отменено, уведомления отправлены';
        } catch (Exception $e) {
            $response['message'] = 'Ошибка отправки email: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Ошибка при изменении статуса';
    }
} else {
    $response['message'] = 'Недопустимый метод запроса.';
}

pg_close($conn);
echo json_encode($response);
