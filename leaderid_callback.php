<?php
session_start();
require './php/conn.php';

$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$client_secret = "tqGScc3gssZ4W3lGOTqi2cvF1mHCSKTO";
$redirect_uri = "https://meet-production-3c0b.up.railway.app/leaderid_callback.php";

if (!isset($_GET['code'])) {
    die("Ошибка авторизации!");
}

$code = $_GET['code'];

// Шаг 1: Получаем access_token
$token_url = "https://leader-id.ru/api/oauth/token/";
$data = [
    'grant_type' => 'authorization_code',
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'code' => $code
];

$options = [
    'http' => [
        'header'  => "Content-type: application/x-www-form-urlencoded",
        'method'  => 'POST',
        'content' => http_build_query($data)
    ]
];

$context  = stream_context_create($options);
$response = file_get_contents($token_url, false, $context);

if ($response === FALSE) {
    die("Ошибка при запросе на получение токена.");
}

$token_data = json_decode($response, true);

// Отладка: Выводим информацию о токене
echo "<pre>";
echo "Ответ API на получение токена: ";
print_r($token_data);
echo "</pre>";

if (!isset($token_data['access_token'])) {
    // Выводим возможную ошибку из ответа
    echo "Ошибка получения токена: " . json_encode($token_data);
    die("Ошибка получения токена!");
}

$access_token = $token_data['access_token'];

// Шаг 2: Получаем данные пользователя
$user_url = "https://leader-id.ru/api/users/me/";
$options = [
    'http' => [
        'header'  => "Authorization: Bearer $access_token",
        'method'  => 'GET'
    ]
];

$context  = stream_context_create($options);
$user_response = file_get_contents($user_url, false, $context);

if ($user_response === FALSE) {
    die("Ошибка при запросе данных пользователя.");
}

$user_data = json_decode($user_response, true);

// Отладка: Выводим информацию о пользователе
echo "<pre>";
echo "Ответ API на запрос данных пользователя: ";
print_r($user_data);
echo "</pre>";

if (!$user_data || !isset($user_data['email'])) {
    // Выводим возможную ошибку из ответа
    echo "Ошибка получения данных пользователя: " . json_encode($user_data);
    die("Ошибка получения данных пользователя!");
}

$email = $user_data['email'];

// Шаг 3: Проверяем пользователя в базе
$getUserQuery = "SELECT user_id FROM users WHERE email = $1";
$result = pg_query_params($conn, $getUserQuery, [$email]);

if ($result && pg_num_rows($result) > 0) {
    // Пользователь найден, авторизуем
    $row = pg_fetch_assoc($result);
    $_SESSION['user_id'] = $row['user_id'];
    setcookie("user_id", $row['user_id'], time() + 3600 * 24 * 30, "/");

    header("Location: lk.php"); // Перенаправляем в личный кабинет
    exit();
} else {
    // Пользователь не найден → предложить регистрацию
    $_SESSION['leader_email'] = $email; // Сохраняем email для быстрой регистрации
    header("Location: register.php");
    exit();
}
?>
