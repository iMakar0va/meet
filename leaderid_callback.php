<?php
session_start();
require './php/conn.php';

$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$client_secret = "tqGScc3gssZ4W3lGOTqi2cvF1mHCSKTO";
$redirect_uri = "https://localhost/wow2/meet/leaderid_callback.php";

// Проверяем наличие авторизационного кода
if (!isset($_GET['code'])) {
    error_log("Ошибка: отсутствует параметр code.");
    die("Ошибка авторизации: код отсутствует.");
}
$code = $_GET['code'];
error_log("Получен код авторизации: $code");

// 1️⃣ Получаем access_token
$token_url = "https://apps.leader-id.ru/api/v1/oauth/token";
$token_options = [
    'http' => [
        'header'  => "Content-type: application/json",
        'method'  => 'POST',
        'content' => json_encode([
            'client_id'     => $client_id,
            'client_secret' => $client_secret,
            'grant_type'    => 'authorization_code',
            'code'          => $code
        ])
    ]
];

$token_response = @file_get_contents($token_url, false, stream_context_create($token_options));
if ($token_response === FALSE) {
    error_log("Ошибка запроса токена: " . json_encode(error_get_last()));
    die("Ошибка получения токена.");
}

$token_data = json_decode($token_response, true);
error_log("Ответ токена: " . json_encode($token_data));

if (!isset($token_data['access_token'])) {
    die("Ошибка: отсутствует access_token.");
}
$access_token = $token_data['access_token'];

// 2️⃣ Получаем список профилей пользователя через /users/link-app
$link_url = "https://apps.leader-id.ru/api/v1/users/link-app";
$link_response = @file_get_contents($link_url, false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
]));

if ($link_response === FALSE) {
    error_log("Ошибка запроса профилей: " . json_encode(error_get_last()));
    die("Ошибка получения профилей.");
}

$link_data = json_decode($link_response, true);
error_log("Список профилей: " . json_encode($link_data));

// Проверяем, есть ли профили
if (empty($link_data) || !isset($link_data[0]['userId'])) {
    die("Ошибка: профиль пользователя не найден.");
}
$user_id = $link_data[0]['userId']; // Берём первый профиль

// 3️⃣ Получаем данные пользователя по userId через /users/{userId}
$user_url = "https://apps.leader-id.ru/api/v1/users/{$user_id}";
$user_response = @file_get_contents($user_url, false, stream_context_create([
    'http' => [
        'header' => "Authorization: Bearer $access_token",
        'method' => 'GET'
    ]
]));

if ($user_response === FALSE) {
    error_log("Ошибка запроса данных пользователя: " . json_encode(error_get_last()));
    die("Ошибка получения данных пользователя.");
}

$user_data = json_decode($user_response, true);
error_log("Данные пользователя: " . json_encode($user_data));

// 4️⃣ Проверяем наличие email
if (!isset($user_data['email'])) {
    die("Ошибка: email отсутствует.");
}
$email = $user_data['email'];

// 5️⃣ Ищем пользователя в базе
$result = pg_query_params($conn, "SELECT user_id FROM users WHERE email = $1", [$email]);

if ($result && pg_num_rows($result) > 0) {
    // Пользователь найден — логиним
    $_SESSION['user_id'] = pg_fetch_result($result, 0, 'user_id');
    setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
} else {
    // Пользователь не найден — перенаправляем на регистрацию
    $_SESSION['leader_email'] = $email;
    header("Location: reg.php");
}
exit();
