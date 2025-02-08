<?php
require 'conn.php';
require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['organizator_id'])) {
    $organizatorId = $_POST['organizator_id'];

    // Удаление организатора
    $stmt = pg_prepare($conn, "delete_organizator", "DELETE FROM organizators WHERE organizator_id = $1 RETURNING organizator_id");
    $result = pg_execute($conn, "delete_organizator", [$organizatorId]);

    if ($result && pg_affected_rows($result) > 0) {
        // Получение email пользователя
        $stmtEmail = pg_prepare($conn, "get_email", "SELECT email FROM users WHERE user_id = $1");
        $resultEmail = pg_execute($conn, "get_email", [$organizatorId]);

        if ($row = pg_fetch_assoc($resultEmail)) {
            $email = $row['email'];
            $mail = new PHPMailer(true);

            try {
                // Настройки SMTP
                $mail->isSMTP();
                $mail->Host       = 'smtp.yandex.ru';
                $mail->SMTPAuth   = true;
                $mail->Username   = 'eno7i@yandex.ru';
                $mail->Password   = 'clzyppxymjxvnmbt';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
                $mail->Port       = 465;

                // Отправитель и получатель
                $mail->setFrom('eno7i@yandex.ru', 'MEET');
                $mail->addAddress($email, 'Пользователь');

                // Кодировка и формат письма
                $mail->CharSet = 'UTF-8';
                $mail->isHTML(true);
                $mail->Subject = 'Отклонение заявки';
                $mail->Body    = '<b>Ваша заявка стать организатором отклонена</b>';
                $mail->AltBody = 'Ваша заявка стать организатором отклонена';

                $mail->send();
                $response['success'] = true;
                $response['message'] = "Заявка отклонена, письмо отправлено.";
            } catch (Exception $e) {
                $response['message'] = "Ошибка при отправке письма: " . $mail->ErrorInfo;
            }
        } else {
            $response['message'] = "Ошибка: email не найден для данного пользователя.";
        }
    } else {
        $response['message'] = 'Ошибка при отмене заявки: ' . pg_last_error($conn);
    }
} else {
    $response['message'] = 'Недопустимый метод запроса или отсутствует ID организатора.';
}

// Закрытие соединения с БД
pg_close($conn);
echo json_encode($response);
