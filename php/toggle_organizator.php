<?php
require 'conn.php';
require 'autoload.php';

$variables = require 'variables.php';

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
        $mail->Username   = $variables['smtp_username'];
        $mail->Password   = $variables['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->setFrom($variables['smtp_username'], 'MEET');
        $mail->addAddress($userEmail, 'Пользователь');
        $mail->CharSet = 'UTF-8';
        $mail->isHTML(true);

        if ($action === 'approve') {
            // Назначение организатором
            $query = "UPDATE organizators SET is_organizator = TRUE WHERE organizator_id = $1";
            error_log("SQL: $query, ID: $organizatorId");
            $result = pg_query_params($conn, $query, [$organizatorId]);

            if ($result) {
                $mail->Subject = 'Вы стали организатором';
                $mail->Body    = '<b>Поздравляем! Вам предоставлены права организатора.</b>';
                $mail->AltBody = 'Поздравляем! Вам предоставлены права организатора.';
                $response['message'] = 'Организатор одобрен, уведомление отправлено';
            } else {
                $response['message'] = 'Ошибка при обновлении БД';
            }

        } elseif ($action === 'cancel') {
            // Отмена статуса организатора
            $query = "UPDATE organizators SET is_organizator = FALSE WHERE organizator_id = $1";
            error_log("SQL: $query, ID: $organizatorId");
            $result = pg_query_params($conn, $query, [$organizatorId]);

            if ($result) {
                $mail->Subject = 'Ваш статус организатора снят';
                $mail->Body    = "<b>Ваш статус организатора был снят.</b><br>Причина: $reason";
                $mail->AltBody = "Ваш статус организатора был снят. Причина: $reason";
                $response['message'] = 'Статус снят, уведомление отправлено';
            } else {
                $response['message'] = 'Ошибка при обновлении БД';
            }
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
