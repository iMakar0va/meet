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
    $reason = trim($_POST['reason'] ?? NULL); // Получаем причину отмены

    if (!$eventId) {
        $response['message'] = 'Мероприятие не найдено или уже обработано.';
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
                                    END,
                        is_repeated = CASE
                                        WHEN is_active = false and is_approved = true THEN true
                                        ELSE false
                                    END,
                        reason_for_refusal = CASE
                                                WHEN $1 <> '' THEN $1
                                                ELSE reason_for_refusal
                                            END
                        WHERE event_id = $2
                        RETURNING is_active, is_approved, is_repeated, title, event_date;";

    $result = pg_query_params($conn, $toggleQuery, [$reason, $eventId]);

    if ($result && $row = pg_fetch_assoc($result)) {
        $isActive = $row['is_active'] === 't';
        $isApproved = $row['is_approved'] === 't';
        $isRepeated = $row['is_repeated'] === 't';
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

            if (!$isActive && !$isApproved && $isRepeated) {
                // Уведомление об одобрении мероприятия
                if ($organizerEmail) {
                    $mail->clearAddresses();
                    $mail->addAddress($organizerEmail, 'Организатор');
                    $mail->Subject = "Уведомление о повторной отправке мероприятия";

                    // HTML-тело письма
                    $mail->Body = "
                            <h1>Ваше мероприятие повторно отправлено</h1>
                            <p>Уважаемый организатор,</p>
                            <p>Мы рады сообщить, что ваше мероприятие <strong>\"{$eventName}\"</strong> повторно отправлено на модерацию.</p>
                            <p>Благодарим вас за ваш вклад и желаем успешного проведения мероприятия!</p>
                            <p>С уважением,</p>
                            <p>Команда MEET</p>
                        ";
                    $mail->AltBody = "Уважаемый организатор,\n\n"
                        . "Мы рады сообщить, что ваше мероприятие \"{$eventName}\" повторно отправлено на модерацию.\n\n"
                        . "Благодарим вас за ваш вклад и желаем успешного проведения мероприятия!.\n\n"
                        . "С уважением,\nКоманда MEET";
                    $mail->send();
                }
            } elseif (!$isActive && $isApproved && !$isRepeated) {
                // Уведомление об отмене мероприятия
                if ($organizerEmail) {
                    $mail->clearAddresses();
                    $mail->addAddress($organizerEmail, 'Организатор');
                    $mail->Subject = "Уведомление об отмене мероприятия";

                    // HTML-тело письма
                    $mail->Body = "
                            <h1>Ваше мероприятие отменено</h1>
                            <p>Уважаемый организатор,</p>
                            <p>К сожалению, ваше мероприятие <strong>\"{$eventName}\"</strong> было отменено.</p>
                            <p><strong>Причина отмены:</strong> <em>{$reason}</em></p>
                            <p><strong>Дата мероприятия:</strong> {$eventDate}</p>
                            <p>Если у вас есть вопросы, пожалуйста, свяжитесь с администрацией.</p>
                            <p>С уважением,</p>
                            <p>Команда MEET</p>
                        ";

                    // Альтернативное текстовое тело для почтовых клиентов без поддержки HTML
                    $mail->AltBody = "Уважаемый организатор,\n\n"
                        . "К сожалению, ваше мероприятие \"{$eventName}\" было отменено.\n\n"
                        . "Причина отмены: {$reason}\n"
                        . "Дата мероприятия: {$eventDate}\n\n"
                        . "Если у вас есть вопросы, пожалуйста, свяжитесь с администрацией.\n\n"
                        . "С уважением,\nКоманда MEET";
                    $mail->send();
                }

                // Уведомление участникам без указания причины
                foreach ($participantsEmails as $participantEmail) {
                    $mail->clearAddresses();
                    $mail->addAddress($participantEmail);
                    $mail->Subject = 'Уведомление об отмене мероприятия';
                    $mail->Body = "
                        <h1>Уведомление об отмене мероприятия</h1>
                        <p>Уважаемый участник,</p>
                        <p>С сожалением сообщаем, что мероприятие <strong>\"{$eventName}\"</strong> было отменено.</p>
                        <p><strong>Дата мероприятия:</strong> {$eventDate}</p>
                        <p>Приносим извинения за возможные неудобства и благодарим вас за понимание.</p>
                        <p>Если у вас возникнут вопросы, пожалуйста, не стесняйтесь связаться с нашей командой.</p>
                        <p>С уважением,</p>
                        <p>Команда MEET</p>
                    ";
                    $mail->send();
                }
            }

            $response['success'] = true;
            $response['newStatus'] = $isActive ? 'true' : 'false';
            if (!$isActive && !$isApproved) {
                $response['message'] = 'Мероприятие повторно отправлено, уведомления отправлены';
            } elseif (!$isActive && $isApproved) {
                $response['message'] = 'Мероприятие отменено, уведомления отправлены';
            } else {
                $response['message'] = 'Мероприятие отклонено или не активировано';
            }
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
