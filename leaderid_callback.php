<?php
session_start();
require './php/conn.php';
require './PHPMailer/src/Exception.php';
require './PHPMailer/src/PHPMailer.php';
require './PHPMailer/src/SMTP.php';

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

$leader_id = null;

if (!empty($link_data['items']) && isset($link_data['items'][0]['id'])) {
    // Если есть профили, берем первый ID
    $leader_id = $link_data['items'][0]['id'];
    error_log("Получен leader_id из профилей: $leader_id");
} elseif (isset($link_data['id'])) {
    // Если нет профилей, но есть просто id
    $leader_id = $link_data['id'];
    error_log("Получен leader_id напрямую: $leader_id");
} elseif (preg_match('/\[(\d+)\]/', json_encode($link_data), $matches)) {
    // Если ID пришел как [6749818] в ответе
    $leader_id = $matches[1];
    error_log("Получен leader_id из текста ошибки: $leader_id");
} else {
    error_log("Ошибка: нет профилей и leader_id.");
    die("Ошибка: нет профилей и отсутствует leader_id.");
}

if (!$leader_id) {
    die("Ошибка: не удалось получить leader_id.");
}

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
    setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
} else {
    $first_name = $user_data['firstName'] ?? '';
    $last_name = $user_data['lastName'] ?? '';
    $birth_date = substr($user_data['birthday'], 0, 10);
    $gender = $user_data['gender'] == 'male' ? 'мужской' : 'женский';

    $generated_password = rand(100000, 999999); // Генерация 8-значного пароля
    $hashed_password = password_hash($generated_password, PASSWORD_DEFAULT);

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
    $mail->Username = 'eno7i@yandex.ru';
    $mail->Password = 'clzyppxymjxvnmbt';
    $mail->SMTPSecure = 'ssl';
    $mail->Port = 465;
    $mail->CharSet = 'UTF-8';
    $mail->setFrom('eno7i@yandex.ru', 'MEET');
    $mail->addAddress($user_email);
    $mail->Subject = 'Ваш новый пароль';
    $mail->Body = "Здравствуйте, $first_name!\n\nВаш пароль для входа: $generated_password\n\nПожалуйста, измените его после входа.";
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
    setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
}

exit();
