<?php
require 'conn.php';
require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $organizerId = $_POST['organizator_id'];

    // Подготовка запроса на одобрение организатора
    $approveOrganizerQuery = pg_prepare($conn, "approve_organizator", "UPDATE organizators SET isOrganizator = true WHERE organizator_id = $1");
    $approveOrganizerResult = pg_execute($conn, "approve_organizator", [$organizerId]);

    if ($approveOrganizerResult) {
        // Получение email пользователя
        $getUserEmailQuery = pg_prepare($conn, "get_user_email", "SELECT email FROM users WHERE user_id = $1");
        $userEmailResult = pg_execute($conn, "get_user_email", [$organizerId]);

        $userData = pg_fetch_assoc($userEmailResult);
        $userEmail = $userData['email'];

        // Настройка и отправка email
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
            $mail->Subject = 'Одобрение заявки';
            $mail->Body    = '<b>Ваша заявка стать организатором одобрена</b>';
            $mail->AltBody = 'Одобрение заявки';

            $mail->send();
            $response['success'] = true;
            $response['message'] = 'Заявка одобрена и уведомление отправлено';
        } catch (Exception $e) {
            $response['success'] = false;
            $response['message'] = 'Ошибка при отправке email: ' . $e->getMessage();
        }
    } else {
        $response['message'] = 'Ошибка при одобрении заявки: ' . pg_last_error($conn);
    }
} else {
    $response['message'] = 'Недопустимый метод запроса.';
}

pg_close($conn);
echo json_encode($response);
