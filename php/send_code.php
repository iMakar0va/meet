<?php
header('Content-Type: application/json');

session_start();
require 'conn.php';

require 'autoload.php';

$variables = require 'variables.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$response = ['success' => false];

// Проверка метода запроса
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Неверный метод запроса.']);
    exit();
}

// Получаем данные из формы
$userData = [
    'last_name' => $_POST["last_name"] ?? '',
    'first_name' => $_POST["first_name"] ?? '',
    'email' => $_POST["email"] ?? '',
    'password' => $_POST["password"] ?? '',
    'repeat_password' => $_POST["repeat_password"] ?? '',
    'birth_date' => $_POST["birth_date"] ?? '',
    'gender' => $_POST["gender"] ?? ''
];
// Валидация данных
if (empty($userData['last_name']) || empty($userData['first_name']) || empty($userData['email']) || empty($userData['password']) || empty($userData['repeat_password']) || empty($userData['birth_date']) || empty($userData['gender'])) {
    echo json_encode(['success' => false, 'message' => 'Все поля должны быть заполнены.']);
    exit();
}

if (!filter_var($userData['email'], FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'message' => 'Некорректный формат email.']);
    exit();
}

// Проверка на существование пользователя
$stmt = pg_prepare($conn, "check_user_exists", "SELECT email FROM users WHERE email = $1;");
$resultGetIsUser = pg_execute($conn, "check_user_exists", [$userData['email']]);

if (!$resultGetIsUser) {
    echo json_encode(['success' => false, 'message' => 'Ошибка проверки пользователя.']);
    exit();
}

if (pg_num_rows($resultGetIsUser) > 0) {
    echo json_encode(['success' => false, 'message' => 'Пользователь с такой почтой уже зарегистрирован.']);
    exit();
}

// Хешируем пароль перед сохранением
$hashedPassword = password_hash($userData['password'], PASSWORD_DEFAULT);

// Отправка письма с кодом подтверждения
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.yandex.ru';
    $mail->SMTPAuth   = true;
    $mail->Username   = $variables['smtp_username'];
    $mail->Password   = $variables['smtp_password'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom($variables['smtp_username'], 'MEET');
    $mail->addAddress($userData['email'], 'Пользователь');

    $code = rand(100000, 999999);
    $_SESSION['verification_code'] = $code;
    $_SESSION['reg_data'] = [
        'email' => $userData['email'],
        'last_name' => $userData['last_name'],
        'first_name' => $userData['first_name'],
        'hashedPassword' => $hashedPassword,
        'birth_date' => $userData['birth_date'],
        'gender' => $userData['gender']
    ];

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Код подтверждения регистрации в MEET';

    $mail->Body = "
        <h1>Подтверждение регистрации</h1>
        <p>Уважаемый(ая) пользователь,</p>
        <p>Благодарим вас за регистрацию на платформе <strong>MEET</strong>. Для завершения процесса подтвердите вашу почту, введя следующий код:</p>
        <p><strong>{$code}</strong></p>
        <p>Если вы не запрашивали регистрацию, просто проигнорируйте это сообщение.</p>
        <p>С уважением,</p>
        <p>Команда MEET</p>
    ";

    $mail->AltBody = "Уважаемый(ая) пользователь,\n\n"
        . "Благодарим вас за регистрацию на платформе MEET. Для завершения процесса подтвердите вашу почту, введя следующий код:\n\n"
        . "{$code}\n\n"
        . "Если вы не запрашивали регистрацию, просто проигнорируйте это сообщение.\n\n"
        . "С уважением,\nКоманда MEET";


    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Письмо с кодом подтверждения отправлено.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}
pg_close($conn);  // Закрытие соединения с базой данных
