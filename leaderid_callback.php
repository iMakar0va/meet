<?php
// Включаем сессию и подключаем необходимые файлы
session_start();
require './php/conn.php';

// Конфигурация
$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$client_secret = "tqGScc3gssZ4W3lGOTqi2cvF1mHCSKTO";
$redirect_uri = "https://localhost/wow2/meet/leaderid_callback.php"; // Используйте ваш правильный URI

// Получение кода авторизации
if (!isset($_GET['code'])) {
    error_log("Ошибка: отсутствует параметр code.");
    die("Ошибка авторизации: код отсутствует.");
}
$code = $_GET['code'];
error_log("Получен код авторизации: $code");

// Получаем access_token с помощью кода авторизации
$token_url = "https://apps.leader-id.ru/api/v1/oauth/token";
$data = json_encode([
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'grant_type' => 'authorization_code',
    'code' => $code
]);

$options = [
    'http' => [
        'header' => "Content-type: application/json",
        'method' => 'POST',
        'content' => $data
    ]
];

$response = @file_get_contents($token_url, false, stream_context_create($options));

if ($response === FALSE) {
    error_log("Ошибка запроса токена: " . json_encode(error_get_last()));
    die("Ошибка запроса токена. Проверьте client_id, client_secret, redirect_uri.");
}

$token_data = json_decode($response, true);
error_log("Ответ токена: " . json_encode($token_data));

if (!isset($token_data['access_token'])) {
    die("Ошибка токена: " . json_encode($token_data));
}

$access_token = $token_data['access_token'];

// Запрос данных о пользователе через /users/link-app
$user_url = "https://apps.leader-id.ru/api/v1/users/link-app";
$link_data_response = @file_get_contents($user_url, false, stream_context_create([
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

// Логируем ответ для дальнейшего анализа
error_log("Ответ от /users/link-app: " . json_encode($link_data));

if (empty($link_data['items'])) {
    error_log("Ошибка: нет профилей, которые дали согласие на доступ.");
    die("Ошибка: нет профилей, которые дали согласие.");
}

// Предполагаем, что данные профиля находятся в массиве 'items'
$user_id = $link_data['items'][0]['id'];
$user_email = $link_data['items'][0]['email'];
error_log("Найден профиль пользователя с id: $user_id, email: $user_email");

// Проверяем, есть ли такой пользователь в нашей базе данных
$result = pg_query_params($conn, "SELECT user_id FROM users WHERE email = $1", [$user_email]);
if ($result && pg_num_rows($result) > 0) {
    // Пользователь найден в базе данных
    $_SESSION['user_id'] = pg_fetch_result($result, 0, 'user_id');
    setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
} else {
    // Новый пользователь, перенаправляем на страницу регистрации
    $_SESSION['leader_email'] = $user_email;
    header("Location: reg.php");
}
exit();
