<?php
session_start();
require './php/conn.php';
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

$variables = require './php/variables.php';

// Конфигурация
$leader_id_client_id = "1e35caef-6ec7-4995-b5ef-6d2d054790c2";
$leader_id_client_secret = "JCj2acm68cANw5aHOZoOJGQeaLXvLPGu";
$redirect_uri = "https://localhost/wow2/meet/leaderid_callback.php";

// Получение кода авторизации
if (!isset($_GET['code'])) {
    error_log("Ошибка: отсутствует параметр code.");
    die("Ошибка авторизации: код отсутствует.");
}
$authorization_code = $_GET['code'];
error_log("Получен код авторизации: $authorization_code");

// Получаем access_token
$token_url = "https://apps.leader-id.ru/api/v1/oauth/token";
$token_request_data = json_encode([
    'client_id' => $leader_id_client_id,
    'client_secret' => $leader_id_client_secret,
    'grant_type' => 'authorization_code',
    'code' => $authorization_code,
    'redirect_uri' => $redirect_uri
]);

$options = [
    'http' => [
        'header' => "Content-type: application/json",
        'method' => 'POST',
        'content' => $token_request_data
    ]
];

$response = @file_get_contents($token_url, false, stream_context_create($options));

if ($response === FALSE) {
    error_log("Ошибка запроса токена: " . json_encode(error_get_last()));
    die("Ошибка запроса токена.");
}

$token_data = json_decode($response, true);
error_log("Ответ токена: " . json_encode($token_data));

if (!isset($token_data['access_token'])) {
    die("Ошибка: токен отсутствует.");
}

$access_token = $token_data['access_token'];

// --- Запрос данных через /users/link-app ---
$user_data_url = "https://apps.leader-id.ru/api/v1/users/link-app";
$link_data_response = @file_get_contents($user_data_url, false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
]));

if ($link_data_response === FALSE) {
    error_log("Ошибка запроса: " . json_encode(error_get_last()));
    die("Ошибка получения данных профилей.");
}

$link_data = json_decode($link_data_response, true);
error_log("Ответ от /users/link-app: " . json_encode($link_data));

$leader_id = $token_data['user_id'] ?? null;

if (!$leader_id) {
    die("Ошибка: не удалось получить leader_id.");
}

// Проверяем, есть ли $leader_id в списке items
$allowed = false;

// Если API вернул просто массив ID без ключа "items"
if (is_array($link_data) && in_array($leader_id, $link_data)) {
    $allowed = true;
}

// // Если API вернул массив с ключом "items"
// if (isset($link_data['items']) && is_array($link_data['items']) && in_array($leader_id, $link_data['items'])) {
//     $allowed = true;
// }

if (!$allowed) {
    error_log("Ошибка: leader_id $leader_id отсутствует в списке разрешенных.");
    die("Ошибка: у вас нет доступа! " . $leader_id . " - " . json_encode($link_data));
}

error_log("Успешная проверка: leader_id $leader_id найден в списке разрешенных.");


// --- Получаем данные пользователя по leader_id ---
$user_data_url = "https://apps.leader-id.ru/api/v1/users/$leader_id";
$options_user = [
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
];

$user_data_response = @file_get_contents($user_data_url, false, stream_context_create($options_user));

if ($user_data_response === FALSE) {
    error_log("Ошибка запроса данных о пользователе: " . json_encode(error_get_last()));
    die("Ошибка получения данных пользователя.");
}

$user_data = json_decode($user_data_response, true);
error_log("Ответ от /users: " . json_encode($user_data));

if (empty($user_data)) {
    die("Ошибка: пустой ответ от API.");
}

$user_email = $user_data['email'] ?? null;
if (!$user_email) {
    die("Ошибка: email отсутствует в данных пользователя.");
}
error_log("Найден профиль пользователя с email: $user_email");

// --- Проверяем наличие пользователя в базе ---
$user_check_result = pg_query_params($conn, "SELECT user_id FROM users WHERE email = $1", [$user_email]);
if ($user_check_result && pg_num_rows($user_check_result) > 0) {
    // Пользователь найден
    $_SESSION['user_id'] = pg_fetch_result($user_check_result, 0, 'user_id');
    // setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
} else {
    $first_name = $user_data['firstName'] ?? '';
    $last_name = $user_data['lastName'] ?? '';
    $birth_date = substr($user_data['birthday'], 0, 10);
    $gender = $user_data['gender'] == 'male' ? 'мужской' : 'женский';

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

    $hashed_password = password_hash($newPassword, PASSWORD_DEFAULT);

    // Вставка данных о пользователе в базу данных
    $insert_user_query = pg_query_params(
        $conn,
        "INSERT INTO users (last_name, first_name, email, password, birth_date, gender) VALUES ($1, $2, $3, $4, $5, $6)",
        [$last_name, $first_name, $user_email, $hashed_password, $birth_date, $gender]
    );

    // Отправка письма с новым паролем
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = 'smtp.yandex.ru';
    $mail->SMTPAuth = true;
    $mail->Username = $variables['smtp_username'];
    $mail->Password = $variables['smtp_password'];
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom($variables['smtp_username'], 'MEET');
    // $mail->addAddress($variables['smtp_username']);
    $mail->addAddress($user_email);
    $mail->Subject = 'Данные для входа в систему MEET';

    $mail->Body = "
        <h1>Добро пожаловать в MEET!</h1>
        <p>Уважаемый(ая) {$first_name},</p>
        <p>Ваш аккаунт успешно создан, и для входа в систему вам назначен временный пароль:</p>
        <p><strong>Ваш пароль:</strong> {$newPassword}</p>
        <p>Пожалуйста, войдите в систему и измените его в личном кабинете для обеспечения безопасности вашего аккаунта.</p>
        <p>Если у вас возникнут вопросы, наша команда всегда готова помочь.</p>
        <p>С уважением,</p>
        <p>Команда MEET</p>
    ";

    $mail->AltBody = "Уважаемый(ая) {$first_name},\n\n"
        . "Ваш аккаунт успешно создан, и для входа в систему вам назначен временный пароль:\n\n"
        . "Ваш пароль: {$newPassword}\n\n"
        . "Пожалуйста, войдите в систему и измените его в личном кабинете для обеспечения безопасности вашего аккаунта.\n\n"
        . "Если у вас возникнут вопросы, наша команда всегда готова помочь.\n\n"
        . "С уважением,\nКоманда MEET";

    $mail->send();
    if (!$mail->send()) {
        error_log("Ошибка отправки письма: " . $mail->ErrorInfo);
        die("Ошибка отправки письма.");
    } else {
        error_log("Письмо успешно отправлено.");
    }

    // Получаем ID пользователя и создаем сессию
    $user_id_query = pg_query_params($conn, "SELECT user_id FROM users WHERE email = $1", [$user_email]);
    $_SESSION['user_id'] = pg_fetch_result($user_id_query, 0, 'user_id');
    // setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
}

exit();
