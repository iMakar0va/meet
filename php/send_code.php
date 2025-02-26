<?php
header('Content-Type: application/json');

session_start();
require 'conn.php';

require 'autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
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

// Обработка изображения
$fileContentEscaped = null;
if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $allowed_types = ['image/jpeg', 'image/jpg', 'image/png'];
    $max_size = 10 * 1024 * 1024; // 10 МБ

    if (!in_array($_FILES['file']['type'], $allowed_types)) {
        echo json_encode(['success' => false, 'message' => 'Формат изображения должен быть JPEG, JPG или PNG.']);
        exit();
    }

    if ($_FILES['file']['size'] > $max_size) {
        echo json_encode(['success' => false, 'message' => 'Размер изображения не должен превышать 10 МБ.']);
        exit();
    }

    $fileContent = file_get_contents($_FILES['file']['tmp_name']);
    if ($fileContent === false) {
        echo json_encode(['success' => false, 'message' => 'Ошибка чтения содержимого файла.']);
        exit();
    }

    $fileContentEscaped = pg_escape_bytea($conn, $fileContent);
} else {
    // Используем дефолтное изображение
    $defaultImagePath = '../img/profile.jpg';
    if (file_exists($defaultImagePath)) {
        $defaultImageContent = file_get_contents($defaultImagePath);
        if ($defaultImageContent === false) {
            echo json_encode(['success' => false, 'message' => 'Ошибка чтения дефолтного изображения.']);
            exit();
        }
        $fileContentEscaped = pg_escape_bytea($conn, $defaultImageContent);
    } else {
        echo json_encode(['success' => false, 'message' => 'Ошибка: дефолтное изображение не найдено.']);
        exit();
    }
}

// Отправка письма с кодом подтверждения
$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host       = 'smtp.yandex.ru';
    $mail->SMTPAuth   = true;
    $mail->Username   = 'eno7i@yandex.ru';
    $mail->Password   = 'clzyppxymjxvnmbt';
    $mail->SMTPSecure = 'ssl';
    $mail->Port       = 465;

    $mail->setFrom('eno7i@yandex.ru', 'MEET');
    $mail->addAddress($userData['email'], 'Пользователь');

    $code = rand(100000, 999999);
    $_SESSION['verification_code'] = $code;
    $_SESSION['reg_data'] = [
        'email' => $userData['email'],
        'last_name' => $userData['last_name'],
        'first_name' => $userData['first_name'],
        'hashedPassword' => $hashedPassword,
        'birth_date' => $userData['birth_date'],
        'gender' => $userData['gender'],
        'image' => $fileContentEscaped,
    ];

    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Код подтверждения';
    $mail->Body    = 'Код для подтверждения почты: <b>' . $code . '</b>';
    $mail->AltBody = 'Код подтверждения';

    $mail->send();
    echo json_encode(['success' => true, 'message' => 'Письмо с кодом подтверждения отправлено.']);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => "Ошибка при отправке письма: {$mail->ErrorInfo}"]);
}
pg_close($conn);  // Закрытие соединения с базой данных
