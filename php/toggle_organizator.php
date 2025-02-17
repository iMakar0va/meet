<?php
require 'conn.php';
require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizatorId = $_POST['organizator_id'] ?? null;

    if (!$organizatorId) {
        $response['message'] = 'Отсутствует ID организатора';
        echo json_encode($response);
        exit;
    }

    // Переключаем статус на противоположный
    $toggleQuery = "UPDATE organizators
                    SET isOrganizator = NOT isOrganizator
                    WHERE organizator_id = $1
                    RETURNING isOrganizator";
    $result = pg_query_params($conn, $toggleQuery, [$organizatorId]);

    if ($result && $row = pg_fetch_assoc($result)) {
        $newStatus = $row['isorganizator'] === 't';

        // Получаем email пользователя
        $emailQuery = "SELECT email FROM users WHERE user_id = $1";
        $emailResult = pg_query_params($conn, $emailQuery, [$organizatorId]);
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
                $mail->addAddress($userEmail, 'Пользователь');
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);

                if ($newStatus) {
                    // Одобрение заявки
                    $mail->Subject = 'Ваша заявка одобрена';
                    $mail->Body    = '<b>Поздравляем! Теперь вы организатор.</b>';
                    $mail->AltBody = 'Поздравляем! Теперь вы организатор.';
                } else {
                    // Отмена статуса
                    $mail->Subject = 'Ваш статус организатора снят';
                    $mail->Body    = '<b>Ваш статус организатора был аннулирован.</b>';
                    $mail->AltBody = 'Ваш статус организатора был аннулирован.';
                }

                $mail->send();
                $response['success'] = true;
                $response['newStatus'] = $newStatus ? 'true' : 'false';
                $response['message'] = $newStatus
                    ? 'Заявка одобрена, уведомление отправлено'
                    : 'Статус снят, уведомление отправлено';

            } catch (Exception $e) {
                $response['success'] = false;
                $response['message'] = 'Ошибка отправки email: ' . $e->getMessage();
            }
        } else {
            $response['success'] = true;
            $response['message'] = 'Статус изменен, но email пользователя не найден.';
        }
    } else {
        $response['message'] = 'Ошибка при изменении статуса: ' . pg_last_error($conn);
    }
} else {
    $response['message'] = 'Недопустимый метод запроса.';
}

pg_close($conn);
echo json_encode($response);
