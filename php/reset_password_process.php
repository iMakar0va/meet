<?php
require 'conn.php';
require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Метод запроса не поддерживается.']);
    exit;
}

$inputData = json_decode(file_get_contents('php://input'), true);
$userEmail = filter_var($inputData['email'] ?? '', FILTER_VALIDATE_EMAIL);

if (!$userEmail) {
    echo json_encode(['success' => false, 'message' => 'Некорректный email.']);
    exit;
}

try {
    // Проверяем, существует ли пользователь с таким email
    $checkUserQuery = "SELECT user_id FROM users WHERE email = $1";
    $checkUserResult = pg_query_params($conn, $checkUserQuery, [$userEmail]);

    if (!$checkUserResult) {
        throw new Exception('Ошибка при проверке email.');
    }

    $user = pg_fetch_assoc($checkUserResult);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Пользователь с таким email не найден.']);
        exit;
    }

    // Генерируем новый пароль и хешируем его
    $newPassword = bin2hex(random_bytes(4)); // 8 символов (4 байта * 2)
    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    // Обновляем пароль в базе данных
    $updatePasswordQuery = "UPDATE users SET password = $1 WHERE email = $2";
    $updatePasswordResult = pg_query_params($conn, $updatePasswordQuery, [$hashedPassword, $userEmail]);

    if (!$updatePasswordResult) {
        throw new Exception('Ошибка при обновлении пароля.');
    }

    // Отправляем письмо с новым паролем
    if (sendNewPasswordEmail($userEmail, $newPassword, $variables)) {
        echo json_encode(['success' => true, 'message' => 'Новый пароль отправлен на вашу почту.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка при отправке письма. Попробуйте позже.']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

/**
 * Отправляет новый пароль пользователю по email
 */
function sendNewPasswordEmail($email, $password, $variables)
{
    $mail = new PHPMailer(true);

    try {
        // Настройки SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.yandex.ru';
        $mail->SMTPAuth   = true;
        $mail->Username   = $variables['smtp_username'];
        $mail->Password   = $variables['smtp_password'];
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';

        // Отправитель и получатель
        $mail->setFrom($variables['smtp_username'], 'MEET'); // Замените на свой email
        $mail->addAddress($email);

        // Содержание письма
        $mail->isHTML(true);
        $mail->Subject = 'Восстановление пароля';
        $mail->Body    = "Ваш новый пароль: <b>$password</b>";
        $mail->AltBody = "Ваш новый пароль: $password";

        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
