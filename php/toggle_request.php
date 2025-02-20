<?php
require 'conn.php';
require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizatorId = $_POST['organizator_id'] ?? null;
    $action = $_POST['action'] ?? null;
    $reason = $_POST['reason'] ?? '';

    if (!$organizatorId || !$action) {
        $response['message'] = 'Некорректные данные';
        echo json_encode($response);
        exit;
    }

    // Получаем email организатора
    $emailQuery = "SELECT email FROM organizators WHERE organizator_id = $1";
    $emailResult = pg_query_params($conn, $emailQuery, [$organizatorId]);
    $userData = pg_fetch_assoc($emailResult);
    $userEmail = $userData['email'] ?? null;

    if (!$userEmail) {
        $response['message'] = 'Email организатора не найден.';
        echo json_encode($response);
        exit;
    }

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

        if ($action === 'approve') {
            // Одобрение заявки
            $query = "UPDATE organizators SET is_approved = true WHERE organizator_id = $1";
            pg_query_params($conn, $query, [$organizatorId]);

            $mail->Subject = 'Ваша заявка одобрена';
            $mail->Body    = '<b>Поздравляем! Ваша заявка на статус организатора одобрена.</b>';
            $mail->AltBody = 'Поздравляем! Ваша заявка на статус организатора одобрена.';

            $response['message'] = 'Заявка одобрена, уведомление отправлено';
        } elseif ($action === 'delete') {
            // Удаление заявки
            $query = "DELETE FROM organizators WHERE organizator_id = $1";
            pg_query_params($conn, $query, [$organizatorId]);

            $mail->Subject = 'Ваша заявка отклонена';
            $mail->Body    = "<b>Ваша заявка на статус организатора отклонена.</b><br>Причина: $reason";
            $mail->AltBody = "Ваша заявка на статус организатора отклонена. Причина: $reason";

            $response['message'] = 'Заявка удалена, уведомление отправлено';
        }

        $mail->send();
        $response['success'] = true;
    } catch (Exception $e) {
        $response['success'] = false;
        $response['message'] = 'Ошибка отправки email: ' . $e->getMessage();
    }
}

pg_close($conn);
echo json_encode($response);
