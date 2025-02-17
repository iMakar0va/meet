<?php
session_start();
require './php/conn.php';

$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$client_secret = "tqGScc3gssZ4W3lGOTqi2cvF1mHCSKTO";
$redirect_uri = "localhost/wow2/meet/leaderid_callback.php";

if (!isset($_GET['code'])) {
    error_log("Ошибка: отсутствует параметр code.");
    die("Ошибка авторизации: код отсутствует.");
}
$code = $_GET['code'];
error_log("Получен код авторизации: $code");

$token_url = "https://leader-id.ru/api/oauth/access_token";
$data = http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => $client_id,
    'client_secret' => $client_secret,
    'redirect_uri' => $redirect_uri,
    'code' => $code
]);

$options = [
    'http' => [
        'header' => "Content-type: application/x-www-form-urlencoded",
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

$user_url = "https://leader-id.ru/api/users/me/";
$user_response = @file_get_contents($user_url, false, stream_context_create([
    'http' => ['header' => "Authorization: Bearer $access_token", 'method' => 'GET']
]));

if ($user_response === FALSE) {
    error_log("Ошибка запроса пользователя: " . json_encode(error_get_last()));
    die("Ошибка получения данных пользователя.");
}
$user_data = json_decode($user_response, true);
error_log("Данные пользователя: " . json_encode($user_data));

if (!isset($user_data['email'])) {
    die("Ошибка: email отсутствует.");
}
$email = $user_data['email'];

$result = pg_query_params($conn, "SELECT user_id FROM users WHERE email = $1", [$email]);
if ($result && pg_num_rows($result) > 0) {
    $_SESSION['user_id'] = pg_fetch_result($result, 0, 'user_id');
    setcookie("user_id", $_SESSION['user_id'], time() + 3600 * 24 * 30, "/");
    header("Location: lk.php");
} else {
    $_SESSION['leader_email'] = $email;
    header("Location: reg.php");
}
exit();
