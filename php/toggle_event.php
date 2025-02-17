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

    // Получаем данные о мероприятии: название, дата
    $eventQuery = "SELECT title, event_date FROM events WHERE event_id = $1";
    $eventResult = pg_query_params($conn, $eventQuery, [$eventId]);
    $eventData = pg_fetch_assoc($eventResult);

    if (!$eventData) {
        $response['message'] = 'Мероприятие не найдено.';
        echo json_encode($response);
        exit;
    }

    $eventTitle = htmlspecialchars($eventData['title']);
    $eventDate = new DateTime($eventData['event_date']);
    $formattedDate = $eventDate->format('d.m.Y');

    // Переключаем статус мероприятия
    $toggleQuery = "UPDATE events
                    SET is_active = NOT is_active
                    WHERE event_id = $1
                    RETURNING is_active, organizer_id";
    $result = pg_query_params($conn, $toggleQuery, [$eventId]);

    if ($result && $row = pg_fetch_assoc($result)) {
        $newStatus = $row['is_active'] === 't';
        $organizerId = $row['organizer_id'];

        // Получаем email организатора
        $emailQuery = "SELECT email FROM users WHERE user_id = $1";
        $emailResult = pg_query_params($conn, $emailQuery, [$organizerId]);
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
                $mail->Password   = 'clzyppxymjxvnmbt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;
                $mail->setFrom('eno7i@yandex.ru', 'MEET');
                $mail->addAddress($userEmail, 'Организатор');
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);

                if ($newStatus) {
                    // Одобрение мероприятия
                    $mail->Subject = 'Ваше мероприятие одобрено';
                    $mail->Body    = "<b>Поздравляем! Ваше мероприятие одобрено!</b><br><br>
                                      <strong>Название:</strong> {$eventTitle}<br>
                                      <strong>Дата:</strong> {$formattedDate}<br><br>
                                      Теперь оно доступно для участников на нашем сайте.";
                    $mail->AltBody = "Поздравляем! Ваше мероприятие одобрено!\n
                                      Название: {$eventTitle}\n
                                      Дата: {$formattedDate}\n
                                      Теперь оно доступно для участников на нашем сайте.";
                } else {
                    // Отклонение мероприятия
                    $mail->Subject = 'Ваше мероприятие отклонено';
                    $mail->Body    = "<b>К сожалению, ваше мероприятие было отклонено.</b><br><br>
                                      <strong>Название:</strong> {$eventTitle}<br>
                                      <strong>Дата:</strong> {$formattedDate}<br><br>
                                      Если у вас есть вопросы, свяжитесь с нашей поддержкой.";
                    $mail->AltBody = "К сожалению, ваше мероприятие было отклонено.\n
                                      Название: {$eventTitle}\n
                                      Дата: {$formattedDate}\n
                                      Если у вас есть вопросы, свяжитесь с нашей поддержкой.";
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
        $response['message'] = 'Ошибка при изменении статуса: ' . pg_last_error($conn);
    }
} else {
    $response['message'] = 'Недопустимый метод запроса.';
}

pg_close($conn);
echo json_encode($response);
