<?php
$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$redirect_uri = urlencode("https://localhost/wow2/meet/leaderid_callback.php"); // Ваш правильный URI

// Добавляем параметр prompt=consent для принудительного запроса согласия
$auth_url = "https://leader-id.ru/apps/authorize?client_id=$client_id&redirect_uri=$redirect_uri&response_type=code&prompt=consent";

header("Location: $auth_url");
exit();
?>
