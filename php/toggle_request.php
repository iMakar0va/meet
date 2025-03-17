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
            // Одобрение заявки
            $query = "UPDATE organizators SET is_approved = true, is_organizator = true WHERE organizator_id = $1";
            pg_query_params($conn, $query, [$organizatorId]);

            $mail->Subject = 'Уведомление о статусе';
            $mail->Body = "
                <h1>Поздравляем!</h1>
                <p>Уважаемый организатор,</p>
                <p>Мы рады сообщить вам, что <strong>\"{$userData['name']}\"</strong> были предоставлены права организатора на платформе <strong>MEET</strong>.</p>
                <p>Теперь у вас есть возможность создавать и управлять мероприятиями.</p>
                <p>Если у вас возникнут вопросы, пожалуйста, свяжитесь с нашей командой.</p>
                <p>С уважением,</p>
                <p>Команда MEET</p>
            ";

            $mail->AltBody = "Уважаемый организатор,\n\n"
                . "Мы рады сообщить вам, что вам были предоставлены права организатора на платформе MEET.\n"
                . "Теперь у вас есть возможность создавать и управлять мероприятиями.\n\n"
                . "Если у вас возникнут вопросы, пожалуйста, свяжитесь с нашей командой.\n\n"
                . "С уважением,\nКоманда MEET.";

            $response['message'] = 'Заявка одобрена, уведомление отправлено';
        } elseif ($action === 'delete') {
            // Удаление заявки
            $query = "DELETE FROM organizators WHERE organizator_id = $1";
            pg_query_params($conn, $query, [$organizatorId]);

            $mail->Subject = 'Ваша заявка отклонена';
            $mail->Subject = 'Уведомление об отказе';
                $mail->Body = "
                    <h1>Уведомление об отказе</h1>
                    <p>Уважаемый организатор,</p>
                    <p>Мы вынуждены сообщить вам, что ваша заявка на становление организатором <strong>\"{$userData['name']}\"</strong> на платформе <strong>MEET</strong> была отклонена.</p>
                    <p><strong>Причина:</strong> {$reason}</p>
                    <p>Если у вас возникли вопросы или вы хотите уточнить детали, пожалуйста, свяжитесь с нашей командой.</p>
                    <p>С уважением,</p>
                    <p>Команда MEET</p>
                ";

                $mail->AltBody = "Уважаемый организатор,\n\n"
                    . "Мы вынуждены сообщить вам, что ваша заявка на становление организатором на платформе MEET была отклонена.\n\n"
                    . "Причина: {$reason}\n\n"
                    . "Если у вас возникли вопросы или вы хотите уточнить детали, пожалуйста, свяжитесь с нашей командой.\n\n"
                    . "С уважением,\nКоманда MEET";

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
