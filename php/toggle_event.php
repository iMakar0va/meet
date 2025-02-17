<?php
require 'conn.php';
require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $eventId = $_POST['event_id'] ?? null;

    if (!$eventId) {
        $response['message'] = 'Отсутствует ID мероприятия';
        echo json_encode($response);
        exit;
    }

    // Переключаем статус мероприятия
    $toggleQuery = "UPDATE events
                    SET is_active = NOT is_active
                    WHERE event_id = $1
                    RETURNING is_active, title, event_date";
    $result = pg_query_params($conn, $toggleQuery, [$eventId]);

    if ($result && $row = pg_fetch_assoc($result)) {
        $newStatus = $row['is_active'] === 't';
        $eventName = $row['title']; // Название мероприятия
        $eventDate = $row['event_date']; // Дата мероприятия

        // Получаем ID организатора из таблицы organizators_events
        $organizerQuery = "SELECT organizator_id FROM organizators_events WHERE event_id = $1";
        $organizerResult = pg_query_params($conn, $organizerQuery, [$eventId]);

        if ($organizerResult && $organizerData = pg_fetch_assoc($organizerResult)) {
            $organizerId = $organizerData['organizator_id'];

            // Получаем email организатора
            $emailQuery = "SELECT email FROM users WHERE user_id = $1";
            $emailResult = pg_query_params($conn, $emailQuery, [$organizerId]);

            if (!$emailResult) {
                $response['message'] = 'Ошибка при получении email организатора: ' . pg_last_error($conn);
                echo json_encode($response);
                exit;
            }

            $userData = pg_fetch_assoc($emailResult);
            $userEmail = $userData['email'] ?? null;

            if ($userEmail) {
                // Отправляем уведомление по email
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host       = 'smtp.yandex.ru';
                    $mail->SMTPAuth   = true;
                    $mail->Username   = 'eno7i@yandex.ru';
                    $mail->Password   = 'clzyppxymjxvnmbt'; // Лучше хранить эти данные в переменных окружения для безопасности
                    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                    $mail->Port       = 465;
                    $mail->setFrom('eno7i@yandex.ru', 'MEET');
                    $mail->addAddress($userEmail, 'Организатор');
                    $mail->CharSet = 'UTF-8';
                    $mail->isHTML(true);

                    if ($newStatus) {
                        // Одобрение мероприятия
                        $mail->Subject = 'Ваше мероприятие одобрено';
                        $mail->Body    = '<b>Поздравляем! Ваше мероприятие "' . htmlspecialchars($eventName) . '" одобрено и теперь доступно для участников.</b><br><br>Дата мероприятия: ' . htmlspecialchars($eventDate);
                        $mail->AltBody = 'Поздравляем! Ваше мероприятие "' . $eventName . '" одобрено. Дата мероприятия: ' . $eventDate;
                    } else {
                        // Отклонение мероприятия
                        $mail->Subject = 'Ваше мероприятие отклонено';
                        $mail->Body    = '<b>К сожалению, ваше мероприятие "' . htmlspecialchars($eventName) . '" было отклонено.</b><br><br>Дата мероприятия: ' . htmlspecialchars($eventDate);
                        $mail->AltBody = 'К сожалению, ваше мероприятие "' . $eventName . '" было отклонено. Дата мероприятия: ' . $eventDate;
                    }

                    $mail->send();
                    $response['success'] = true;
                    $response['newStatus'] = $newStatus ? 'true' : 'false';
                    $response['message'] = $newStatus
                        ? 'Мероприятие одобрено, уведомление отправлено'
                        : 'Мероприятие отклонено, уведомление отправлено';

                } catch (Exception $e) {
                    $response['success'] = false;
                    $response['message'] = 'Ошибка отправки email: ' . $e->getMessage();
                }
            } else {
                $response['success'] = true;
                $response['message'] = 'Статус мероприятия изменен, но email организатора не найден.';
            }
        } else {
            $response['message'] = 'Ошибка при получении ID организатора из таблицы organizators_events';
        }
    } else {
        $response['message'] = 'Ошибка при изменении статуса: ' . pg_last_error($conn);
    }
} else {
    $response['message'] = 'Недопустимый метод запроса.';
}

pg_close($conn);
echo json_encode($response);
?>
