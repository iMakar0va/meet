<?php
$client_id = "9736370e-6438-4d5c-bbe8-b2e9252fd0d5";
$redirect_uri = urlencode("https://meet-production-3c0b.up.railway.app/leaderid_callback.php");

$auth_url = "https://leader-id.ru/api/oauth/authorize/?response_type=code&client_id=$client_id&redirect_uri=$redirect_uri";

header("Location: $auth_url");
exit();
