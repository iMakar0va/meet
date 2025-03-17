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
    function generateSecurePassword($length = 6)
    {
        // Убедимся, что длина пароля не меньше 6
        if ($length < 6) {
            $length = 6;
        }

        // Множество символов для пароля
        $lowercase = 'abcdefghijklmnopqrstuvwxyz';
        $uppercase = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $numbers = '0123456789';
        $specialChars = '!@#$%^&*()-_=+[]{}|;:,.<>?';

        // Генерация случайных символов из каждой категории
        $password = '';
        $password .= $lowercase[random_int(0, strlen($lowercase) - 1)];
        $password .= $uppercase[random_int(0, strlen($uppercase) - 1)];
        $password .= $numbers[random_int(0, strlen($numbers) - 1)];
        $password .= $specialChars[random_int(0, strlen($specialChars) - 1)];

        // Заполнение оставшихся символов случайными символами из всех категорий
        $allChars = $lowercase . $uppercase . $numbers . $specialChars;
        for ($i = 4; $i < $length; $i++) {
            $password .= $allChars[random_int(0, strlen($allChars) - 1)];
        }

        // Перемешиваем пароль для случайного порядка символов
        $password = str_shuffle($password);

        return $password;
    }

    // Пример использования:
    $newPassword = generateSecurePassword(8); // Генерация пароля длиной 8 символов

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
        $mail->Subject = 'Восстановление доступа к аккаунту MEET';

        $mail->Body = "
            <h1>Восстановление пароля</h1>
            <p>Уважаемый(ая) пользователь,</p>
            <p>Вы запросили восстановление пароля. Ваш новый временный пароль:</p>
            <p><strong>{$password}</strong></p>
            <p>Рекомендуем как можно скорее изменить его в личном кабинете для обеспечения безопасности вашего аккаунта.</p>
            <p>Если вы не запрашивали смену пароля, пожалуйста, свяжитесь с нашей поддержкой.</p>
            <p>С уважением,</p>
            <p>Команда MEET</p>
        ";

        $mail->AltBody = "Уважаемый(ая) пользователь,\n\n"
            . "Вы запросили восстановление пароля. Ваш новый временный пароль:\n\n"
            . "{$password}\n\n"
            . "Рекомендуем как можно скорее изменить его в личном кабинете для обеспечения безопасности вашего аккаунта.\n\n"
            . "Если вы не запрашивали смену пароля, пожалуйста, свяжитесь с нашей поддержкой.\n\n"
            . "С уважением,\nКоманда MEET";


        return $mail->send();
    } catch (Exception $e) {
        return false;
    }
}
